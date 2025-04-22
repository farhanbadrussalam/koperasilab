<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;
use App\Models\Kontrak;
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
        $menu = $request->has('menu') ? $request->menu : '';
        $filter = $request->has('filter') ? $request->filter : [];

        switch ($menu) {
            case 'pengajuan':
                $status = [1];
                break;
            case 'pembayaran':
                $status = [3];
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
            case 'faktur':
                $status = [2,7];
                break;
            default:
                $status = false;
                break;
        }

        DB::beginTransaction();
        try {
            // Menampilkan data keuangan berdasarkan created_by jika rolenya pelanggan
            $createBy = false;
            if(Auth::user()->hasRole('Pelanggan')){
                $createBy = Auth::user()->id;
            }
            
            $query = Keuangan::with(
                            'permohonan',
                            'diskon',
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
                        ->when($createBy, function($q, $createBy) {
                            return $q->whereHas('permohonan', function($q) use ($createBy) {
                                $q->where('created_by', $createBy);
                            })->whereNotIn('status', [1, 2, 91]);
                        })
                        ->when($filter, function($q, $filter) {
                            return $q->whereHas('permohonan', function($p) use ($filter, $q) {
                                foreach ($filter as $key => $value) {
                                    if($key == 'status') {
                                        $q->where($key, decryptor($value));
                                    }else{
                                        $p->where($key, decryptor($value));
                                    }
                                }
                            });
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

    public function countList(Request $request){
        DB::beginTransaction();
        try {
            $arrStatus = [1,2,3,4,5,6,7];
            $_status = Keuangan::selectRaw('count(*) as total, status')
                ->groupBy('status')
                ->get()
                ->toArray();
            foreach ($arrStatus as $value) {
                $exist = array_filter($_status, function($item) use ($value) {
                    return $item['status'] == $value;
                });
                if (count($exist) == 0) {
                    $_status[] = [
                        'status' => $value,
                        'total' => 0
                    ];
                }
            }

            $query = array_map(function($item) {
                switch ($item['status']) {
                    case 1:
                        $item['name'] = 'Pengajuan';
                        break;
                    case 3:
                        $item['name'] = 'Pembayaran';
                        break;
                    case 4:
                        $item['name'] = 'Verifikasi';
                        break;
                    case 5:
                        $item['name'] = 'Diterima';
                        break;
                    case 6:
                        $item['name'] = 'Ditolak';
                        break;
                    case 2:
                    case 7:
                        $item['name'] = 'Faktur';
                        break;
                }
                return $item;
            }, $_status);
            
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

            // get bukti bayar
            if(isset($query->bukti_bayar)){
                $buktiBayar = $query->bukti_bayar;
                $arrBukti = array();
                foreach ($buktiBayar as $key => $idMedia) {
                    array_push($arrBukti, $this->media->get($idMedia));
                }
                $query->media_bukti_bayar = $arrBukti;
            }else{
                $query->media_bukti_bayar = array();
            }

            // get bukti bayar pph
            if(isset($query->bukti_bayar_pph)){
                $buktiBayarPph = $query->bukti_bayar_pph;
                $arrBuktiPph = array();
                foreach ($buktiBayarPph as $key => $idMedia) {
                    array_push($arrBuktiPph, $this->media->get($idMedia));
                }
                $query->media_bukti_bayar_pph = $arrBuktiPph;
            }else{
                $query->media_bukti_bayar_pph = array();
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
            $textNote = $request->note ?? '';
            $plt = $request->has('plt') ? $request->plt : false;

            $result = array();
            $data = [];
            
            $totalHarga && $data['total_harga'] = $totalHarga;
            $ppn && $data['ppn'] = $ppn;
            $pph && $data['pph'] = $pph;
            $idPermohonan && $data['id_permohonan'] = $idPermohonan;
            $ttd && $data['ttd'] = $ttd;
            $ttd_by && $data['ttd_by'] = $ttd_by;
            $plt && $data['plt'] = $plt;
            
            $data['status'] = $status;
            
            $invoice = Keuangan::where('id_keuangan', $idKeuangan)->first();
            if($invoice){
                !$invoice->no_invoice && $data['no_invoice'] = $this->generateNoInvoice($idPermohonan);
                !$invoice->created_by && $data['created_by'] = Auth::user()->id;
            }else{
                $data['no_invoice'] = $this->generateNoInvoice($idPermohonan);
                $data['created_by'] = Auth::user()->id;
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

            if($status == 7){
                // Simpan dokumen Invoice
                $document = Permohonan_dokumen::create(array(
                    'id_permohonan' => $keuangan->id_permohonan,
                    'created_by' => Auth::user()->id,
                    'nama' => 'Invoice',
                    'jenis' => 'invoice',
                    'status' => 1,
                    'nomer' => $keuangan->no_invoice
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

                // menambahkan id keuangan ke kontrak
                $idKontrak = Permohonan::find($idPermohonan)->id_kontrak;
                if($idKontrak){
                    $kontrak = Kontrak::find($idKontrak);
                    $kontrak->update(array('id_keuangan' => $keuangan->id_keuangan));
                }

            } elseif ($keuangan->wasChanged()) {
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

    public function uploadBuktiBayar(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);
            
            if(isset($dataKeuangan)){
                $buktiBayar = is_array($dataKeuangan->bukti_bayar) ? $dataKeuangan->bukti_bayar : [];
                
                array_push($buktiBayar, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('bukti_bayar' => $buktiBayar));
    
                DB::commit();
    
                if($update){
                    $fileUpload->store();
                    // ambil media bukti bayar
                    $mediaBuktiBayar = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Bukti bayar berhasil diupload', 'data' => $mediaBuktiBayar));
                }
    
                return $this->output(array('msg' => 'Bukti bayar gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadBuktiBayarPph(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idHash);
            $file = $request->file('file');
            
            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);
            
            if(isset($dataKeuangan)){
                $buktiBayarPph = is_array($dataKeuangan->bukti_bayar_pph) ? $dataKeuangan->bukti_bayar_pph : [];
                
                array_push($buktiBayarPph, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('bukti_bayar_pph' => $buktiBayarPph));
    
                DB::commit();
    
                if($update){
                    $fileUpload->store();
                    // ambil media bukti bayar pph
                    $mediaBuktiBayarPph = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Bukti bayar PPH berhasil diupload', 'data' => $mediaBuktiBayarPph));
                }
    
                return $this->output(array('msg' => 'Bukti bayar PPH gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadDocumentFaktur(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);
            
            if(isset($dataKeuangan)){
                $documentFaktur = is_array($dataKeuangan->document_faktur) ? $dataKeuangan->document_faktur : [];
                
                array_push($documentFaktur, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('document_faktur' => $documentFaktur));
    
                DB::commit();
    
                if($update){
                    $fileUpload->store();
                    // ambil media faktur
                    $mediaFaktur = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Faktur berhasil diupload', 'data' => $mediaFaktur));
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

    public function uploadFaktur(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);
            
            if(isset($dataKeuangan)){
                $documentFaktur = is_array($dataKeuangan->document_faktur) ? $dataKeuangan->document_faktur : [];
                
                array_push($documentFaktur, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('document_faktur' => $documentFaktur));
    
                DB::commit();
    
                if($update){
                    $fileUpload->store();
                    // ambil media faktur
                    $mediaFaktur = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Faktur berhasil diupload', 'data' => $mediaFaktur));
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

    public function destroyBuktiBayar($idKeuangan, $idMedia){
        $idMedia = decryptor($idMedia);
        $idKeuangan = decryptor($idKeuangan);

        DB::beginTransaction();
        try {
            $dataKeuangan = Keuangan::find($idKeuangan);
            $buktiBayar = is_array($dataKeuangan->bukti_bayar) ? $dataKeuangan->bukti_bayar : [];
            
            if(($key = array_search($idMedia, $buktiBayar)) !== false) {
                unset($buktiBayar[$key]);
            }
            // atur menjadi array biasa jangan array object
            $buktiBayar = array_values($buktiBayar);

            $update = $dataKeuangan->update(array('bukti_bayar' => $buktiBayar));
            $this->media->destroy($idMedia);

            DB::commit();

            if($update){
                return $this->output(array('msg' => 'Bukti bayar berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Bukti bayar gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyBuktiBayarPph($idKeuangan, $idMedia){
        $idMedia = decryptor($idMedia);
        $idKeuangan = decryptor($idKeuangan);

        DB::beginTransaction();
        try {
            $dataKeuangan = Keuangan::find($idKeuangan);
            $buktiBayarPph = is_array($dataKeuangan->bukti_bayar_pph) ? $dataKeuangan->bukti_bayar_pph : [];
            
            if(($key = array_search($idMedia, $buktiBayarPph)) !== false) {
                unset($buktiBayarPph[$key]);
            }
            // atur menjadi array biasa jangan array object
            $buktiBayarPph = array_values($buktiBayarPph);

            $update = $dataKeuangan->update(array('bukti_bayar_pph' => $buktiBayarPph));
            $this->media->destroy($idMedia);

            DB::commit();

            if($update){
                return $this->output(array('msg' => 'Bukti bayar PPH berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Bukti bayar PPH gagal dihapus'), 'Fail', 400);
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
            $documentFaktur = array_values($documentFaktur);

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
