<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Traits\RestApi;

use App\Models\Pengiriman;
use App\Models\Permohonan;
use App\Models\User;

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
    }

    public function listPengiriman(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $menu = $request->has('menu') ? $request->menu : '';
        $idPelanggan = $request->has('idPelanggan') ? decryptor($request->idPelanggan) : '';

        switch ($menu) {
            case 'list':
                $status = [1];
                break;
            
            case 'selesai':
                $status = [2];
                break;

            default:
                $status = false;
                break;
        }

        DB::beginTransaction();
        try {
            $query = Pengiriman::with(
                        'permohonan:id_permohonan,periode_pemakaian,created_by',
                        'permohonan.pelanggan',
                        'permohonan.pelanggan.perusahaan'
                    )->orderBy('created_at', 'DESC')
                    ->offset(($page - 1) * $limit)
                    ->when($status, function($q, $status) {
                        return $q->whereIn('status', $status);
                    })
                    ->when($idPelanggan, function($q, $idPelanggan) {
                        return $q->where('tujuan', $idPelanggan);
                    })
                    ->limit($limit)
                    ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');

            DB::commit();

            return response()->json($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }
    }

    public function getPengirimanById(string $idPengiriman)
    {
        $id = decryptor($idPengiriman);

        DB::beginTransaction();
        try {
            $query = Pengiriman::with(
                'permohonan:id_permohonan,periode_pemakaian,created_by',
                'permohonan.pelanggan',
                'permohonan.invoice',
                'permohonan.lhu',
                'permohonan.lhu.media',
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
                    'lhu',
                    'lhu.media',
                    'lhu.log'
                )->whereHas('lhu.log', function ($q) {
                    $q->whereColumn('log_penyelia.status', 'penyelia.status');
                })
                ->where('id_permohonan', decryptor($idPermohonan))->first();
            }else{
                $query = Permohonan::with(
                    'layanan_jasa:id_layanan,nama_layanan',
                    'pelanggan',
                    'jenis_layanan_parent'
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
            $idPengiriman = $request->idPengiriman ? decryptor($request->idPengiriman) : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $noResi = $request->noResi ? $request->noResi : false;
            $jenisPengiriman = $request->jenisPengiriman ? $request->jenisPengiriman : false;
            $noKontrak = $request->noKontrak ? $request->noKontrak : false;
            $alamat = $request->alamat ? $request->alamat : false;
            $tujuan = $request->tujuan ? $request->tujuan : false;
            $periode = $request->periode ? $request->periode : false;
            $status = $request->status ? $request->status : false;
            $recivedAt = $request->dateRecived ? $request->dateRecived : false;
            $buktiPengiriman = $request->file('buktiPengiriman') ? $request->file('buktiPengiriman') : array();
            $buktiPenerima = $request->file('buktiPenerima') ? $request->file('buktiPenerima') : array();
            
            $params = array();
            $idPermohonan && $params['id_permohonan'] = $idPermohonan;
            $noResi && $params['no_resi'] = $noResi;
            $jenisPengiriman && $params['jenis_pengiriman'] = $jenisPengiriman;
            $noKontrak && $params['no_kontrak'] = $noKontrak;
            $alamat && $params['alamat'] = $alamat;
            $tujuan && $params['tujuan'] = $tujuan;
            $periode && $params['periode'] = $periode;
            $status && $params['status'] = $status;
            $recivedAt && $params['recived_at'] = $recivedAt;

            // upload file
            $bukti = array();
            $tmpFileBukti = array();
            if(count($buktiPengiriman) != 0){
                foreach ($buktiPengiriman as $key => $file) {
                    $fileBukti = $this->media->upload($file, 'pengiriman');
                    array_push($bukti, $fileBukti->getIdMedia());
                    array_push($tmpFileBukti, $fileBukti);
                }

                $params['bukti_pengiriman'] = json_encode($bukti);
            }

            $tmpBuktiPenerima = array();
            $tmpFilePenerima = array();
            if(count($buktiPenerima) != 0){
                foreach ($buktiPenerima as $key => $file) {
                    $fileBukti = $this->media->upload($file, 'pengiriman');
                    array_push($tmpBuktiPenerima, $fileBukti->getIdMedia());
                    array_push($tmpFilePenerima, $fileBukti);
                }

                $params['bukti_penerima'] = json_encode($tmpBuktiPenerima);
            }

            $pengiriman = Pengiriman::where('id_pengiriman', $idPengiriman)->first();
            if($pengiriman){

            }else{
                $params['send_at'] = Carbon::now();
                $params['created_by'] = Auth::user()->id;
            }

            $query = Pengiriman::updateOrCreate(
                ["id_pengiriman" => $idPengiriman],
                $params
            );

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
        $id = decryptor($idPengiriman);

        DB::beginTransaction();
        try {
            $fileBukti = Pengiriman::select('bukti_pengiriman','bukti_penerima')->where('id_pengiriman', $id)->first();
            $delete = Pengiriman::where('id_pengiriman', $id)->delete();
            DB::commit();

            if($fileBukti && $delete){
                $buktiPengiriman = json_decode($fileBukti->bukti_pengiriman);
                $buktiPenerima = json_decode($fileBukti->bukti_penerima);

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
