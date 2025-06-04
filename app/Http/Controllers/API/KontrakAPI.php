<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Kontrak;
use App\Models\Kontrak_pengguna;
use App\Models\Kontrak_periode;
use App\Models\Master_tld;

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
            $query = Kontrak::with([
                        'pengguna',
                        'periode' => function($q) use ($filter) {
                            if(isset($filter['date_range']))
                                $q->whereBetween('start_date', [$filter['date_range'][0], $filter['date_range'][1]])->whereNull('id_permohonan');
                        },
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
                    ])
                    ->withCount('periode')
                    ->when($idPelanggan, function($q, $idPelanggan){
                        return $q->where('id_pelanggan', $idPelanggan);
                    })
                    ->when($filter, function($q, $filter) {
                        foreach ($filter as $key => $value) {
                            if($key == 'date_range') {
                                $q->whereHas('periode', function($p) use ($value) {
                                    $p->whereBetween('start_date', [$value[0], $value[1]])->whereNull('id_permohonan');
                                });
                            }else{
                                $q->where($key, decryptor($value));
                            }
                        }
                    });

            $query = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->paginate($limit);

            // Filter range periode start_date - end_date
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

    // private function filter_by_periode($data, $filter){
    //     $dataNew = [];
    //     foreach ($data as $key => $value) {
    //         $arrFilter = array_filter($value['periode'], function($p) use ($filter) {
    //             return $p['permohonan'] == null;
    //         });
    //         $value['periode'] = array_values($arrFilter);
    //         array_push($dataNew, $value);
    //     }

    //     return $dataNew;
    // }

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

    public function getKontrakById($id){
        $id = decryptor($id);

        DB::beginTransaction();
        try {
            $query = Kontrak::with(
                        'pengguna',
                        'pengguna.tld_pengguna',
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
                        'pelanggan.perusahaan','pengguna.media',
                        'pengiriman:id_pengiriman,id_kontrak,no_resi,status',
                        'pengiriman.detail',
                        'pengiriman.permohonan:id_permohonan,periode',
                    )
                    ->where('id_kontrak', $id)
                    ->first();

            DB::commit();

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function searchKontrak(Request $request){
        DB::beginTransaction();
        try {
            $no_kontrak = $request->has('no_kontrak') ? $request->no_kontrak : false;
            $data = array();

            if(!empty($no_kontrak)){
                $idPelanggan = Auth::user()->hasRole('Pelanggan') ? Auth::user()->id : false;
                $data = Kontrak::when($idPelanggan, fn($q) => $q->where('id_pelanggan', $idPelanggan))
                        ->where('no_kontrak', 'like', '%'.$no_kontrak.'%')
                        ->get();
            }

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
