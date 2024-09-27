<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Keuangan;
use App\Models\Keuangan_diskon;

use Auth;
use DB;

class KeuanganAPI extends Controller
{
    use RestApi;

    public function listKeuangan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $menu = $request->has('menu') ? $request->menu : '';

        switch ($menu) {
            case 'pengajuan':
                $status = 1;
                break;
            case 'pembayaran':
                $status = 3;
                break;
            case 'verifikas':
                $status = 4;
                break;
            case 'diterima':
                $status = 5;
                break;
            default:
                $status = 90;
                break;
        }

        DB::beginTransaction();
        try {
            $query = Keuangan::with(
                            'permohonan',
                            'permohonan.layanan_jasa:id_layanan,nama_layanan',
                            'permohonan.jenisTld:id_jenisTld,name', 
                            'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                            'permohonan.jenis_layanan_parent',
                            'permohonan.pelanggan:id,name'
                        )
                        ->orderBy('created_at','DESC')
                        ->offset(($page - 1) * $limit)
                        ->where('status', $status)
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

    public function keuanganAction(Request $request)
    {
        DB::beginTransaction();
        try {
            $idKeuangan = $request->idKeuangan ? decryptor($request->idKeuangan) : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $diskon = $request->diskon ? json_decode($request->diskon) : array();
            $totalHarga = $request->totalHarga ?? false;
            $ppn = $request->ppn ?? false;

            $data = [];
            
            $totalHarga && $data['total_harga'] = $totalHarga;
            $ppn && $data['ppn'] = $ppn;
            $data['status'] = 2;
            $data['no_invoice'] = $this->generateNoInvoice($idPermohonan);
            $idPermohonan && $data['id_permohonan'] = $idPermohonan;
            
            $invoice = Keuangan::where('id_keuangan', $idKeuangan)->first();
            if($invoice){
                !$invoice->created_by && $data['created_by'] = Auth::user()->id;
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
            } elseif ($keuangan->wasChanged()) {
                $result['status'] = "updated";
                $result['msg'] = "Invoice berhasil diedit.";
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
