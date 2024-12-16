<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Keuangan;
use App\Models\Keuangan_diskon;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use Auth;
use DB;

class KeuanganAPI extends Controller
{
    use RestApi;

    public function __construct()
    {
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
    }

    public function listKeuangan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $menu = $request->has('menu') ? $request->menu : '';

        switch ($menu) {
            case 'pengajuan':
                $status = [1];
                break;
            case 'pembayaran':
                $status = [2,3];
                break;
            case 'verifikasi':
                $status = [4];
                break;
            case 'diterima':
                $status = [5];
                break;
            case 'ditolak':
                $status = [90];
                break;
            default:
                $status = false;
                break;
        }

        DB::beginTransaction();
        try {
            $query = Keuangan::with(
                            'permohonan',
                            'diskon',
                            'media_bayar',
                            'media_bayar_pph',
                            'usersig',
                            'permohonan.layanan_jasa:id_layanan,nama_layanan',
                            'permohonan.jenisTld:id_jenisTld,name', 
                            'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                            'permohonan.jenis_layanan_parent',
                            'permohonan.pelanggan',
                            'permohonan.pelanggan.perusahaan',
                            'permohonan.kontrak'
                        )
                        ->orderBy('created_at','DESC')
                        ->offset(($page - 1) * $limit)
                        ->when($status, function($q, $status) {
                            return $q->whereIn('status', $status);
                        })
                        ->limit($limit)
                        ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getKeuangan($idKeuangan)
    {
        $idKeuangan = $idKeuangan ? decryptor($idKeuangan) : false;
        DB::beginTransaction();
        try {
            $query = Keuangan::with(
                'permohonan',
                'diskon',
                'media_bayar',
                'media_bayar_pph',
                'usersig',
                'permohonan.layanan_jasa:id_layanan,nama_layanan',
                'permohonan.jenisTld:id_jenisTld,name', 
                'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                'permohonan.jenis_layanan_parent',
                'permohonan.pelanggan',
                'permohonan.pelanggan.perusahaan',
                'permohonan.kontrak'
            )->find($idKeuangan);

            // get Document faktur
            if(isset($query->document_faktur)){
                $documentFaktur = $query->document_faktur;
                $arrDoc = array();
                foreach ($documentFaktur as $key => $idMedia) {
                    array_push($arrDoc, $this->media->get($idMedia));
                }
                $query->media = $arrDoc;
            }else{
                $query->media = array();
            }
            
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    public function keuanganAction(Request $request)
    {
        DB::beginTransaction();
        try {
            $idKeuangan = $request->idKeuangan ? decryptor($request->idKeuangan) : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $diskon = $request->diskon ? json_decode($request->diskon) : array();
            $status = $request->status ? $request->status : false;
            $totalHarga = $request->totalHarga ?? false;
            $ppn = $request->ppn ?? false;
            $pph = $request->pph ?? false;
            $ttd = $request->ttd ?? false;
            $ttd_by = $request->ttd_by ? decryptor($request->ttd_by) : false;
            $buktiBayar = $request->file('buktiBayar') ?? false;
            $buktiBayarPph = $request->file('buktiPph') ?? false;
            $textNote = $request->note ?? '';

            $result = array();
            $data = [];
            
            $totalHarga && $data['total_harga'] = $totalHarga;
            $ppn && $data['ppn'] = $ppn;
            $pph && $data['pph'] = $pph;
            $idPermohonan && $data['id_permohonan'] = $idPermohonan;
            $ttd && $data['ttd'] = $ttd;
            $ttd_by && $data['ttd_by'] = $ttd_by;
            
            $data['status'] = $status;
            
            $invoice = Keuangan::where('id_keuangan', $idKeuangan)->first();
            if($invoice){
                !$invoice->no_invoice && $data['no_invoice'] = $this->generateNoInvoice($idPermohonan);
                !$invoice->created_by && $data['created_by'] = Auth::user()->id;
            }else{
                $data['no_invoice'] = $this->generateNoInvoice($idPermohonan);
                $data['created_by'] = Auth::user()->id;
            }

            // Upload bukti
            $file_buktiBayar = false;
            $file_buktiBayarPph = false;
            if($buktiBayar){
                $file_buktiBayar = $this->media->upload($buktiBayar, 'keuangan');
                $data['bukti_bayar'] = $file_buktiBayar->getIdMedia();
            }

            if($buktiBayarPph){
                $file_buktiBayarPph = $this->media->upload($buktiBayarPph, 'keuangan');
                $data['bukti_bayar_pph'] = $file_buktiBayarPph->getIdMedia();
            }

            $keuangan = Keuangan::updateOrCreate(
                ["id_keuangan" => $idKeuangan],
                $data
            );

            foreach ($diskon as $key => $value) {
                Keuangan_diskon::create(array(
                    'id_keuangan' => decryptor($keuangan->keuangan_hash),
                    'name' => $value->name,
                    'diskon' => $value->diskon
                ));
            }

            DB::commit();

            $result['id_keuangan'] = $keuangan->keuangan_hash;

            if ($keuangan->wasRecentlyCreated) {
                $result['status'] = "created";
                $result['msg'] = "Invoice berhasil dibuat.";

                // log keuangan
                $note = $this->log->noteLog('keuangan', $status);
                $this->log->addLog('keuangan', array(
                    'id_keuangan' => $keuangan->id_keuangan,
                    'status' => $status,
                    'note' => $note,
                    'created_by' => Auth::user()->id
                ));

            } elseif ($keuangan->wasChanged()) {
                $file_buktiBayar && $file_buktiBayar->store();
                $file_buktiBayarPph && $file_buktiBayarPph->store();

                $result['status'] = "updated";
                $result['msg'] = "Invoice berhasil diedit.";

                // log keuangan
                $note = $this->log->noteLog('keuangan', $status, $textNote);
                $this->log->addLog('keuangan', array(
                    'id_keuangan' => $keuangan->id_keuangan,
                    'status' => $status,
                    'note' => $note,
                    'created_by' => Auth::user()->id
                ));
            } else {
                $result['status'] = "none";
                $result['msg'] = "Nothing has changed.";
            }

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
        
    }

    public function uploadFaktur(Request $request)
    {
        $validate = $request->validate([
            'idKeuangan' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idKeuangan);
            $file = $request->file('faktur');

            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);
            
            if(isset($dataKeuangan)){
                $documentFaktur = is_array($dataKeuangan->document_faktur) ? $dataKeuangan->document_faktur : [];
                
                array_push($documentFaktur, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('document_faktur' => $documentFaktur));
    
                DB::commit();
    
                if($update){
                    $fileUpload->store();
                    return $this->output(array('msg' => 'Faktur berhasil diupload'));
                }
    
                return $this->output(array('msg' => 'Faktur gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }


    }

    public function destroyFaktur($idKeuangan, $idMedia){
        $idMedia = decryptor($idMedia);
        $idKeuangan = decryptor($idKeuangan);

        DB::beginTransaction();
        try {
            $dataKeuangan = Keuangan::find($idKeuangan);
            $documentFaktur = is_array($dataKeuangan->document_faktur) ? $dataKeuangan->document_faktur : [];
            
            if(($key = array_search($idMedia, $documentFaktur)) !== false) {
                unset($documentFaktur[$key]);
            }

            $update = $dataKeuangan->update(array('document_faktur' => $documentFaktur));
            $this->media->destroy($idMedia);

            DB::commit();

            if($update){
                return $this->output(array('msg' => 'Faktur berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Faktur gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    // PRIVATE FUNCTION
    private function generateNoInvoice($idPermohonan)
    {
        $permohonan = Permohonan::with('jenis_layanan')->where('id_permohonan', $idPermohonan)->first();
        // Menentukan tipe kontrak
        if($permohonan) {
            $jenisLayanan = substr($permohonan->jenis_layanan->name, 0, 1);
            $type = strtoupper($jenisLayanan);

            // Nama aplikasi
            $appName = 'JKRL';

            // Mengambil bulan sekarang dan mengubah ke dalam format Romawi
            $bulanSekarang = date('n'); // n = format angka bulan tanpa nol
            $romawiBulan = getRomawiBulan($bulanSekarang);

            // Tahun saat ini
            $tahunSekarang = date('Y');

            // Incremental number
            $lastInvoiceNumber = Keuangan::whereNotNull('no_invoice')
                                    ->whereMonth('created_at', $bulanSekarang)
                                    ->whereYear('created_at', $tahunSekarang)
                                    ->count(); // Ubah dengan pengambilan nomor terakhir dari database
            $increment = str_pad($lastInvoiceNumber + 1, 4, '0', STR_PAD_LEFT);

            // Format nomor kontrak
            $noInvoice = "{$increment}/INV-{$type}/{$appName}/{$romawiBulan}/{$tahunSekarang}";

            return $noInvoice;
        }
    }
}
