<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Kontrak;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;
use App\Models\Kontrak_detail;
use App\Models\Master_pengguna;
use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class KontrakAPI extends Controller
{
    use RestApi;
    protected $media, $log, $pagination;

    public function __construct(){
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
    }

    public function listKontrak(Request $request){
        $limit = $request->limit ?? 10;
        $page = $request->page ?? 1;
        $filter = $request->filter ?? [];

        // cek role
        $idPelanggan = false;
        if(Auth::user()->hasRole('Pelanggan')){
            $idPelanggan = Auth::user()->id;
        }

        DB::beginTransaction();
        try {
            $query = Kontrak::with([
                        'pengguna',
                        'periode' => function($q) use ($filter) {
                            if(isset($filter['date_range']))
                                $q->whereBetween('start_date', [$filter['date_range'][0], $filter['date_range'][1]])->whereNull('id_permohonan');

                            $q->whereIn('status', [1, 2]);
                        },
                        'periode.permohonan',
                        'periode.permohonan.jenis_layanan',
                        'periode.permohonan.jenis_layanan_parent',
                        'periode.permohonan.file_lhu',
                        'periode.permohonan.invoice',
                        'periode.penyelia',
                        'periode.penyelia.penyelia_map',
                        'periode.penyelia.penyelia_map.jobs',
                        'layanan_jasa:id_layanan,nama_layanan',
                        'jenisTld:id_jenisTld,name',
                        'jenis_layanan:id_jenisLayanan,name,parent',
                        'jenis_layanan_parent',
                        'pelanggan:id,id_perusahaan,name',
                        'pelanggan.perusahaan',
                        'pengiriman:id_pengiriman,id_kontrak,no_resi,status,id_permohonan',
                        'pengiriman.detail',
                        'pengiriman.permohonan:id_permohonan,periode,tipe_kontrak',
                        'tld_aktif:id_tld,digunakan,no_seri_tld,status',
                        'kontrak_detail',
                        'kontrak_detail.tld_1',
                        'kontrak_detail.tld_2',
                        'kontrak_detail.entitas' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([
                                Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                            ]);
                        },
                        'rincian_list_tld' => function($q) {
                            $q->whereIn('status', [5,6]);
                        }
                    ])
                    ->withCount('periode')
                    ->when($idPelanggan, function($q, $idPelanggan){
                        // mengambil id dari history_pic
                        $id_pic = array();
                        foreach (Auth::user()->perusahaan->history_pic as $key => $pic) {
                            array_push($id_pic, $pic->id);
                        }
                        return $q->where('id_pelanggan', $id_pic);
                    })
                    ->when($filter, function($q, $filter) {
                        foreach ($filter as $key => $value) {
                            if($key == 'date_range') {
                                $q->whereHas('periode', function($p) use ($value) {
                                    $p->whereBetween('start_date', [$value[0], $value[1]])->whereNull('id_permohonan');
                                });
                            } else if ($key == 'periode') {

                            } else {
                                $q->where($key, decryptor($value));
                            }
                        }
                    });

            if(Auth::user()->status == 2){
                $query = $query->where('status', '2');
            }

            $query = $query->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->paginate($limit);

            // Filter range periode start_date - end_date
            $arr = $query->toArray();

            $this->pagination = Arr::except($arr, 'data');

            // mencari adendum di setiap periode
            $kontrakIds = $query->pluck('id_kontrak');
            $adendums = Permohonan::whereIn('id_kontrak', $kontrakIds)
                ->where('tipe_kontrak', 'adendum')
                ->get()
                ->groupBy(['id_kontrak', 'periode']);

            foreach ($query->items() as $value) {
                foreach ($value->periode as $v) {
                    $v->adendum = $adendums->get($value->id_kontrak)?->get($v->periode) ?? collect();
                }
            }

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
                Kontrak::where('id_kontrak', $id)->get()->each->delete();
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
            $query = Kontrak::with([
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
                        'pengiriman.permohonan:id_permohonan,periode',
                        'tld_aktif',
                        'kontrak_detail',
                        'kontrak_detail.tld_1',
                        'kontrak_detail.tld_2',
                        'kontrak_detail.entitas' => function (MorphTo $morphTo) {
                            $morphTo->morphWith([
                                Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                            ]);
                        },
                    ])
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

    public function getKontrakTld(Request $request){
        $idKontrak = $request->has('id_kontrak') ? decryptor($request->id_kontrak) : false;

        DB::beginTransaction();
        try {
            $data = Kontrak_detail::with([
                'entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'tld_1',
                'tld_2'
            ])->where('id_kontrak', $idKontrak)->where('status', 1)->get();

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function signKontrak(Request $request){
        $id_kontrak = decryptor($request->id_kontrak);
        $ttdValue = decryptor($request->ttd);
        $ttdBy = decryptor($request->ttd_by);

        if(empty($ttdValue) || empty($ttdBy)){
            return $this->output(array('msg' => 'Tanda tangan dan nama penandatangan harus diisi'), "Fail", 400);
        }

         // cek apakah kontrak sudah memiliki ttd
         $kontrak_dokumen = Permohonan_dokumen::where('id_kontrak', $id_kontrak)
            ->whereIn('jenis', ['kontrak', 'KontrakPengujian'])
            ->first();

        if (!$kontrak_dokumen) {
            return $this->output(array('msg' => 'Dokumen kontrak tidak ditemukan'), "Fail", 404);
        }

         if($kontrak_dokumen->ttd != null){
             return $this->output(array('msg' => 'Kontrak sudah memiliki tanda tangan'), "Fail", 400);
         }

        DB::beginTransaction();
        try {
            $kontrak_dokumen->update(['ttd' => $ttdValue, 'ttd_by' => $ttdBy]);
            DB::commit();
            return $this->output(array('msg' => 'Tanda tangan berhasil disimpan'), "Success", 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
