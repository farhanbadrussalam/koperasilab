<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Kontrak;
use App\Models\Kontrak_pengguna;
use App\Models\Kontrak_periode;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use Auth;
use DB;

class KontrakAPI extends Controller
{
    use RestApi;

    public function __construct(){
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
    }

    public function listKontrak(Request $request){
        $limit = $request->limit ?? 10;
        $page = $request->page ?? 1;
        $filter = $request->filter ?? [];

        // cek role
        $role = Auth::user()->getRoleNames();
        $idPelanggan = false;
        if(in_array('Pelanggan', $role->toArray())){
            $idPelanggan = Auth::user()->id; 
        }
        
        DB::beginTransaction();
        try {
            $query = Kontrak::with(
                        'pengguna',
                        'periode',
                        'periode.permohonan',
                        'periode.permohonan.jenis_layanan',
                        'periode.permohonan.jenis_layanan_parent',
                        'periode.permohonan.file_lhu',
                        'invoice',
                        'layanan_jasa:id_layanan,nama_layanan',
                        'jenisTld:id_jenisTld,name', 
                        'jenis_layanan:id_jenisLayanan,name,parent',
                        'jenis_layanan_parent',
                        'pelanggan:id,id_perusahaan,name',
                        'pelanggan.perusahaan',
                        'pengiriman:id_pengiriman,id_kontrak,no_resi,status',
                        'pengiriman.detail',
                        'pengiriman.permohonan:id_permohonan,periode'
                    )
                    ->when($idPelanggan, function($q, $idPelanggan){
                        return $q->where('id_pelanggan', $idPelanggan);
                    })
                    ->orderBy('created_at', 'desc')
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->paginate($limit);
            
            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');

            DB::commit();
            
            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function actionKontrak(Request $request){
        $action = $request->action;
        $data = $request->data;
        $id = $request->id;

        DB::beginTransaction();
        try {
            if($action == "add"){
                $dataKontrak = Kontrak::create($data);
                $id = $dataKontrak->id_kontrak;
            } else if($action == "edit"){
                Kontrak::where('id_kontrak', $id)->update($data);
            } else if($action == "delete"){
                Kontrak::where('id_kontrak', $id)->delete();
            }

            DB::commit();
            return $this->output(array('id' => $id), 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
