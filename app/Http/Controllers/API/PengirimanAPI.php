<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Traits\RestApi;

use App\Models\Pengiriman;
use App\Models\Pengiriman_detail;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\Keuangan;
use App\Models\Penyelia;
use App\Models\Kontrak;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use Auth;
use DB;

class PengirimanAPI extends Controller
{
    use RestApi;

    public function __construct(){
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
        date_default_timezone_set('Asia/Jakarta');
    }

    public function listPermohonan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';

        DB::beginTransaction();
        try {
            $query = Permohonan::with(
                        'layanan_jasa:id_layanan,nama_layanan',
                        'jenisTld:id_jenisTld,name', 
                        'jenis_layanan:id_jenisLayanan,name,parent',
                        'jenis_layanan_parent',
                        'pelanggan:id,id_perusahaan,name',
                        'pelanggan.perusahaan',
                        'kontrak',
                        'kontrak.periode',
                        'pengiriman',
                        'invoice',
                        'invoice.pengiriman',
                        'lhu',
                        'lhu.media',
                        'lhu.pengiriman',
                        'file_lhu'
                    )->when($search, function($q, $search){
                        return $q->where('no_kontrak', 'like', "%$search%");
                    })
                    ->whereIn('status', [2, 3, 4, 5])
                    ->orderBy('verify_at','DESC')
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->paginate($limit);

            $arr = $query->toArray();
            DB::commit();
            $this->pagination = Arr::except($arr, 'data');

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }

    }

    public function listPengiriman(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $idPelanggan = $request->has('idPelanggan') ? decryptor($request->idPelanggan) : '';

        DB::beginTransaction();
        try {
            $query = Pengiriman::with(
                        'permohonan:id_permohonan,periode_pemakaian,created_by',
                        'permohonan.pelanggan',
                        'permohonan.pelanggan.perusahaan',
                        'kontrak',
                        'detail',
                        'alamat'
                    )->orderBy('created_at', 'DESC')
                    ->offset(($page - 1) * $limit)
                    // ->when($status, function($q, $status) {
                    //     return $q->whereIn('status', $status);
                    // })
                    ->when($idPelanggan, function($q, $idPelanggan) {
                        return $q->where('tujuan', $idPelanggan);
                    })
                    ->limit($limit)
                    ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');

            DB::commit();

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }
    }

    public function getPengirimanById(string $idPengiriman)
    {
        $id = $idPengiriman;

        DB::beginTransaction();
        try {
            $query = Pengiriman::with(
                'permohonan:id_permohonan,periode_pemakaian,jumlah_pengguna,jumlah_kontrol,created_by',
                'permohonan.pelanggan',
                'permohonan.invoice',
                'permohonan.lhu',
                'permohonan.lhu.media',
                'permohonan.pelanggan.perusahaan',
                'permohonan.kontrak',
                'detail',
            )->where('id_pengiriman', $id)->first();
            
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPermohonan(Request $request)
    {
        $tipe = $request->has('tipe') ? $request->tipe : null;
        $search = $request->has('search') ? $request->search : '';
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;

        $idPermohonan = $request->has('idPermohonan') ? $request->idPermohonan : false;

        DB::beginTransaction();
        try {
            if($idPermohonan){
                $query = Permohonan::with(
                    'pelanggan',
                    'pelanggan.perusahaan',
                    'pelanggan.perusahaan.alamat',
                    'invoice',
                    'invoice.usersig',
                    'invoice.diskon',
                    'lhu',
                    'lhu.media',
                    'lhu.log',
                    'kontrak',
                    'jenis_layanan',
                    'jenisTld',
                    'layanan_jasa'
                )->whereHas('lhu.log', function ($q) {
                    $q->whereColumn('log_penyelia.status', 'penyelia.status');
                })
                ->where('id_permohonan', decryptor($idPermohonan))->first();
            }else{
                $query = Permohonan::with(
                    'layanan_jasa:id_layanan,nama_layanan',
                    'pelanggan',
                    'pelanggan.perusahaan',
                    'jenis_layanan_parent',
                    'kontrak'
                )->when($search, function($q, $search){
                    return $q->where('no_kontrak', 'like', "%$search%");
                })
                ->whereNotIn('status', ['80','99'])
                ->orderBy('created_at','DESC')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->paginate($limit);
                
                $arr = $query->toArray();
                $this->pagination = Arr::except($arr, 'data');
            }
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function actionPengiriman(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPengiriman = $request->idPengiriman ? $request->idPengiriman : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $idEkspedisi = $request->has('idEkspedisi') ? decryptor($request->idEkspedisi) : false;
            $noResi = $request->has('noResi') ? $request->noResi : false;
            $jenisPengiriman = $request->jenisPengiriman ? $request->jenisPengiriman : false;
            $idKontrak = $request->idKontrak ? decryptor($request->idKontrak) : false;
            $alamat = $request->alamat ? decryptor($request->alamat) : false;
            $tujuan = $request->tujuan ? $request->tujuan : false;
            $periode = $request->periode ? $request->periode : false;
            $status = $request->status ? $request->status : false;
            $detail = $request->detail ? $request->detail : false;
            $sendAt = $request->sendAt ? $request->sendAt : false;
            $recivedAt = $request->dateRecived ? $request->dateRecived : false;
            $statusPermohonan = $request->statusPermohonan ? $request->statusPermohonan : false;
            $buktiPengiriman = $request->file('buktiPengiriman') ? $request->file('buktiPengiriman') : array();
            $buktiPenerima = $request->file('buktiPenerima') ? $request->file('buktiPenerima') : array();
            
            $params = array();
            $request->has('noResi') && $params['no_resi'] = $noResi;
            $request->has('idEkspedisi') && $params['id_ekspedisi'] = $idEkspedisi;
            $idPermohonan && $params['id_permohonan'] = $idPermohonan;
            $jenisPengiriman && $params['jenis_pengiriman'] = $jenisPengiriman;
            $idKontrak && $params['id_kontrak'] = $idKontrak;
            $alamat && $params['alamat'] = $alamat;
            $tujuan && $params['tujuan'] = $tujuan;
            $periode && $params['periode'] = $periode;
            $status && $params['status'] = $status;
            $recivedAt && $params['recived_at'] = $recivedAt;
            $sendAt && $params['send_at'] = Carbon::parse($sendAt)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

            // upload file
            $bukti = array();
            $tmpFileBukti = array();
            if(count($buktiPengiriman) != 0){
                foreach ($buktiPengiriman as $key => $file) {
                    $fileBukti = $this->media->upload($file, 'pengiriman');
                    array_push($bukti, $fileBukti->getIdMedia());
                    array_push($tmpFileBukti, $fileBukti);
                }

                $params['bukti_pengiriman'] = $bukti;
            }

            $tmpBuktiPenerima = array();
            $tmpFilePenerima = array();
            if(count($buktiPenerima) != 0){
                foreach ($buktiPenerima as $key => $file) {
                    $fileBukti = $this->media->upload($file, 'pengiriman');
                    array_push($tmpBuktiPenerima, $fileBukti->getIdMedia());
                    array_push($tmpFilePenerima, $fileBukti);
                }

                $params['bukti_penerima'] = $tmpBuktiPenerima;
            }

            $pengiriman = Pengiriman::with('detail')->where('id_pengiriman', $idPengiriman)->first();
            if(!$pengiriman){
                $params['created_by'] = Auth::user()->id;
            }

            $query = Pengiriman::updateOrCreate(
                ["id_pengiriman" => $idPengiriman],
                $params
            );

            // update status
            if($statusPermohonan){
                Permohonan::where('id_permohonan', $query->id_permohonan)
                            ->update(array('status' => $statusPermohonan));
            }

            // jika status 2 = pengiriman selesai maka mengganti list_tld di tabel kontrak
            if($status == 2){
                // cari jenis tld pada detail pengiriman
                $listTld = array();
                $pengiriman->detail->each(function($item, $key) use (&$listTld){
                    if($item->jenis == 'tld'){
                        $listTld = $item->list_tld;
                    }
                });

                // update list_tld di kontrak
                if(count($listTld) != 0){
                    $pengiriman->kontrak->update(['list_tld' => $listTld]);
                }
            }

            // Add to detail
            if($detail){
                // Remove all detail
                Pengiriman_detail::where('id_pengiriman', $idPengiriman)->delete();

                foreach (json_decode($detail) as $key => $value) {
                    Pengiriman_detail::create(array(
                        'id_pengiriman' => $idPengiriman,
                        'jenis' => $value->jenis,
                        'periode' => $value->periode ? $value->periode : null,
                        'list_tld' => $value->listTld ? $value->listTld : null,
                    ));
                    
                    // menambahkan id_pengiriman ke invoice
                    if($value->jenis == 'invoice'){
                        $invoice = Keuangan::where('id_keuangan', decryptor($value->id))->update(['id_pengiriman' => $idPengiriman]);
                    } else if($value->jenis == 'lhu'){
                        $lhu = Penyelia::where('id_penyelia', decryptor($value->id))->update(['id_pengiriman' => $idPengiriman]);
                    } else if($value->jenis == 'tld'){
                        $tld = Permohonan::where('id_permohonan', decryptor($value->id))->update(['id_pengiriman' => $idPengiriman]);
                    }
                }
            }

            $result['id_pengiriman'] = $query->pengiriman_hash;

            if ($query->wasRecentlyCreated) {
                $result['status'] = "created";
                $result['msg'] = "Pengiriman berhasil dibuat.";
            } elseif ($query->wasChanged()) {
                $result['status'] = "updated";
                $result['msg'] = "Pengiriman berhasil diedit.";
            } else {
                $result['status'] = "none";
                $result['msg'] = "Nothing has changed.";
            }

            if(count($tmpFileBukti) != 0){
                foreach ($tmpFileBukti as $key => $file) {
                    $file->store();
                }
            }

            if(count($tmpFilePenerima) != 0){
                foreach ($tmpFilePenerima as $key => $file) {
                    $file->store();
                }
            }

            DB::commit();
            
            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroy(string $idPengiriman)
    {
        $id = $idPengiriman;

        DB::beginTransaction();
        try {
            $fileBukti = Pengiriman::select('bukti_pengiriman','bukti_penerima')->where('id_pengiriman', $id)->first();
            $delete = Pengiriman::where('id_pengiriman', $id)->delete();
            $detail = Pengiriman_detail::where('id_pengiriman', $id)->delete();

            // update id_pengiriman di invoice, lhu, dan tld
            Keuangan::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);
            Penyelia::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);
            Permohonan::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);

            DB::commit();

            if($fileBukti && $delete){
                $buktiPengiriman = $fileBukti->bukti_pengiriman;
                $buktiPenerima = $fileBukti->bukti_penerima;

                if($buktiPengiriman){
                    foreach ($buktiPengiriman as $key => $value) {
                        $this->media->destroy($value);
                    }
                }

                if($buktiPenerima){
                    foreach ($buktiPenerima as $key => $value) {
                        $this->media->destroy($value);
                    }
                }

                return $this->output(array('msg' => 'Data berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Data gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
