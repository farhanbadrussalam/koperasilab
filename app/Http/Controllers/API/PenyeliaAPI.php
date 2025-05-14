<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Penyelia;
use App\Models\Penyelia_petugas;
use App\Models\Penyelia_map;
use App\Models\User;
use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;

use App\Models\Master_jobs;
use App\Models\Master_tld;

use App\Models\Kontrak_tld;
use App\Models\Kontrak_periode;

use App\Http\Controllers\LogController;
use App\Http\Controllers\MediaController;

use Auth;
use DB;

class PenyeliaAPI extends Controller
{
    use RestApi;

    public function __construct()
    {
        $this->log = resolve(LogController::class);
        $this->media = resolve(MediaController::class);
    }

    public function actionPenyelia(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPenyelia = $request->idPenyelia ? decryptor($request->idPenyelia) : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $startDate = $request->startDate ?? false;
            $endDate = $request->endDate ?? false;
            $status = $request->status ?? false;
            $ttd = $request->ttd ?? false;
            $ttd_by = $request->ttd_by ? decryptor($request->ttd_by) : false;
            $petugas = $request->petugas ? $request->petugas : false;
            $jobsMap = $request->jobsMap ? $request->jobsMap : false;
            $arrPetugas = array();
            $textNote = $request->note ? $request->note : '';
            $statusPermohonan = $request->statusPermohonan ? $request->statusPermohonan : '';
            $jenisLog = $request->jenisLog ? $request->jenisLog : '';

            $document = $request->file("document");
            $file_document = false;
            $flagSkipLog = false;

            if($document){
                $file_document = $this->media->upload($document, 'penyelia');
            }

            $params = array();
            $result = array();

            $idPermohonan && $params['id_permohonan'] = $idPermohonan;
            $startDate && $params['start_date'] = $startDate;
            $endDate && $params['end_date'] = $endDate;
            $ttd && $params['ttd'] = $ttd;
            $ttd_by && $params['ttd_by'] = $ttd_by;
            $file_document && $params['document'] = $file_document->getIdMedia();

            $status && $params['status'] = $status;

            $penyelia = Penyelia::where('id_penyelia', $idPenyelia)->first();
            if($penyelia){
                !$penyelia->created_by && $params['created_by'] = Auth::user()->id;
            }else{
                $params['created_by'] = Auth::user()->id;
            }

            // menambahkan periode
            $dataPemohonan = Permohonan::select('periode')->where('id_permohonan', $idPermohonan)->first();
            if($dataPemohonan){
                $params['periode'] = $dataPemohonan->periode ? $dataPemohonan->periode : 0;
            }

            $penyelia = Penyelia::updateOrCreate(
                ["id_penyelia" => $idPenyelia],
                $params
            );

            $result['id_penyelia'] = $penyelia->penyelia_hash;

            if ($penyelia->wasRecentlyCreated) {
                $result['status'] = "created";
                $result['msg'] = "Penyelia berhasil dibuat.";
            } elseif ($penyelia->wasChanged()) {
                $result['status'] = "updated";
                $result['msg'] = "Penyelia berhasil diedit.";

                // Menyimpan perubahan yang terjadi
                $result['changed_columns'] = $penyelia->getChanges();
                // remove updated_at
                unset($result['changed_columns']['updated_at']);

                if(empty($result['changed_columns'])){
                    $result['status'] = "none";
                    $result['msg'] = "Nothing has changed.";
                }
            } else {
                $result['status'] = "none";
                $result['msg'] = "Nothing has changed.";
            }

            // update status
            if($statusPermohonan){
                Permohonan::where('id_permohonan', $penyelia->id_permohonan)
                            ->update(array('status' => $statusPermohonan));

                if($statusPermohonan == 3){ // ketika proses pelaksana lab
                    $flagSkipLog = true;
                } else if($statusPermohonan == 4){ // ketika proses LHU selesai
                    $flagSkipLog = false;
                }
            }

            // log penyelia
            if($result['status'] != "none" && !$flagSkipLog){
                $message = $this->log->noteLog('penyelia', $status, $jenisLog);
                $this->log->addLog('penyelia', array(
                    'id_penyelia' => $penyelia->id_penyelia,
                    'status' => $status,
                    'message' => $message,
                    'note' => $textNote,
                    'document' => $file_document ? $file_document->getIdMedia() : null,
                    'created_by' => Auth::user()->id
                ));

                if($file_document){
                    $file_document->store();
                }
            }

            DB::commit();

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function actionSuratTugas(Request $request){
        DB::beginTransaction();
        try {
            $idPenyelia = $request->has('idPenyelia') ? decryptor($request->idPenyelia) : false;
            $status = $request->has('status') ? $request->status : false;
            $startDate = $request->has('startDate') ? $request->startDate : false;
            $endDate = $request->has('endDate') ? $request->endDate : false;
            $ttd = $request->has('ttd') ? $request->ttd : false;
            $ttd_by = $request->has('ttd_by') ? decryptor($request->ttd_by) : false;

            $petugas = $request->has('petugas') ? $request->petugas : false;
            $jobsMap = $request->has('jobsMap') ? $request->jobsMap : false;
            $jobsMapParalel = $request->has('jobsMapParalel') ? $request->jobsMapParalel : false;
            $jenisLog = $request->has('jenisLog') ? $request->jenisLog : false;

            $params = array();

            $startDate && $params['start_date'] = $startDate;
            $endDate && $params['end_date'] = $endDate;
            $status && $params['status'] = $status;
            $ttd_by && $params['ttd_by'] = $ttd_by;
            $ttd && $params['ttd'] = $ttd;

            $penyelia = Penyelia::with('permohonan', 'permohonan.jenis_layanan_parent')->find($idPenyelia);
            if($penyelia){
                $penyelia->update($params);

                // Menambahkan jobs ke penyelia
                if($jobsMap && $jobsMapParalel){
                    $arrJobsMap = json_decode($jobsMap);
                    $arrJobsMapParalel = json_decode($jobsMapParalel);

                    foreach($arrJobsMap as $value){
                        $data = array(
                            'order' => $value->order,
                            'created_by' => Auth::user()->id
                        );

                        Penyelia_map::updateOrCreate(
                            [
                                'id_jobs' => decryptor($value->jobs_hash),
                                'id_penyelia' => $idPenyelia
                            ],
                            $data
                        );
                    }

                    foreach($arrJobsMapParalel as $value){
                        $data = array(
                            'order' => $value->order,
                            'created_by' => Auth::user()->id,
                            'point_jobs' => $penyelia->permohonan->jenis_layanan_parent->jobs_paralel_point
                        );

                        Penyelia_map::updateOrCreate(
                            [
                                'id_jobs' => decryptor($value->jobs_hash),
                                'id_penyelia' => $idPenyelia
                            ],
                            $data
                        );
                    }
                }

                // Menambahkan petugas
                if($petugas){
                    $arr = json_decode($petugas);

                    // Menghapus data sebelumnya
                    Penyelia_petugas::where('id_penyelia', $idPenyelia)->delete();

                    foreach ($arr as $value) {
                        $findMap = Penyelia_map::where('id_jobs', decryptor($value->idJobs))->where('id_penyelia', $idPenyelia)->first();
                        if($findMap){
                            $data = array(
                                'status' => 1,
                                'created_by' => Auth::user()->id,
                                'id_map' => decryptor($findMap->map_hash),
                                'id_penyelia' => $idPenyelia,
                                'id_user' => decryptor($value->idPetugas),
                            );

                            Penyelia_petugas::create($data);
                        }
                    }

                }

                // Jika status = 10 akan mengganti status di permohonan menjadi 3 = Proses Pelaksana LAB
                if($status == 10){
                    $permohonan = Permohonan::find($penyelia->id_permohonan);
                    $permohonan->update(array('status' => 3));

                    // mengganti status penyelia_map
                    $subQuery = Penyelia_map::where('id_penyelia', $idPenyelia)->where('order', 1)->where('point_jobs', null)->first();
                    $subQuery->update(array('status' => 1));

                    Penyelia_map::where('point_jobs', $subQuery->id_jobs)->where('id_penyelia', $idPenyelia)->update(array('status' => 1));

                    // log surat tugas
                    $this->log->addLog('penyelia', array(
                        'id_penyelia' => $penyelia->id_penyelia,
                        'message' => 'Surat tugas ditandatangani',
                        'created_by' => Auth::user()->id
                    ));
                }

                // cek dokumen sudah ada atau belum
                $dokumen = Permohonan_dokumen::where('id_permohonan', $penyelia->id_permohonan)->where('jenis', 'surattugas')->first();

                if(!$dokumen){
                    // menambahkan dokumen perjanjian
                    $penyeliaData = Penyelia::select('id_permohonan','id_penyelia')->find($idPenyelia);
                    $dataParams = array(
                        'id_permohonan' => $penyeliaData->id_permohonan,
                        'created_by' => Auth::user()->id,
                        'nama' => 'Surat Tugas Uji',
                        'jenis' => 'surattugas',
                        'status' => 1,
                        'nomer' => generateNoDokumen('surattugas', $penyeliaData->id_penyelia)
                    );

                    $document = Permohonan_dokumen::create($dataParams);
                }
            }

            DB::commit();
            return $this->output(array('msg' => 'Berhasil mengupdate penyelia'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function actionJobProses(Request $request){
        DB::beginTransaction();
        try {
            $validate = $request->validate([
                'idPenyelia' => 'required',
                'note' => 'required',
                'sProgress' => 'required',
            ]);

            $idPenyelia = decryptor($request->idPenyelia);
            $sProgress = $request->sProgress;
            $note = $request->note;
            $nextJobs = $request->nextJobs ? decryptor($request->nextJobs) : false;
            $nowJobs = $request->nowJobs ? decryptor($request->nowJobs) : false;

            $penyelia = Penyelia::with(
                'permohonan',
                'permohonan.kontrak.rincian_list_tld'
            )->find($idPenyelia);
            $jobsNow = Penyelia_map::with('jobs')->where('id_map', $nowJobs)->first();

            if($jobsNow->jobs->status == 17){ // Penyimpanan TLD
                foreach($penyelia->permohonan->kontrak->rincian_list_tld as $key => $value){
                    if($value->status == 3) {
                        // jenis kontraknya bukan evaluasi berarti di update statusnya
                        if($penyelia->permohonan->kontrak->jenis_layanan_2 != '3') {
                            Master_tld::where('id_tld', $value->id_tld)->update(array('status' => 0));
                        }

                        // Masih opsional apakah Kontrak_tld di ganti menjadi status 0 atau masih tetap 3
                        Kontrak_tld::where('id_kontrak_tld', $value->id_kontrak_tld)->update(['status' => 0]);

                        // mengecek jika sudah di periode terakhir
                        // Mengambil last periode
                        $kontrakPeriode = Kontrak_periode::where('id_kontrak', $penyelia->permohonan->kontrak->id_kontrak)->orderBy('periode', 'desc')->first();
                        $isLast = $kontrakPeriode->periode == $penyelia->permohonan->periode ? true : false;

                        if($isLast) {
                            Master_tld::where('digunakan', $penyelia->permohonan->kontrak->no_kontrak)->update(array('status' => 0, 'digunakan' => null));
                        }
                    }
                }
            }

            $jobsNow->update(array(
                'status' => $sProgress == 'done' ? 2 : 0,
                'done_by' => $sProgress == 'done' ? Auth::user()->id : null,
                'done_at' => $sProgress == 'done' ? date('Y-m-d H:i:s') : null
            ));

            if($sProgress != 'done' && $penyelia->document) {
                // remove dokument LHU saat dikembalikan
                $this->destroyDokumenLhu($penyelia->penyelia_hash, encryptor($penyelia->document));
            }

            $jobsNext = Penyelia_map::with('jobs')->where('id_map', $nextJobs)->first();
            if($jobsNext){
                $jobsNext->update(array(
                    'status' => 1,
                    'done_by' => null,
                    'done_at' => null
                ));
            }

            if($sProgress == 'done') {
                // mencari jobs yang sifatnya paralel
                $jobsParalel = Penyelia_map::with('jobs')
                    ->where('order', 1)
                    ->where('id_penyelia', $idPenyelia)
                    ->where('point_jobs', $jobsNow->id_jobs)
                    ->first();

                if($jobsParalel){
                    $jobsParalel->update(array(
                        'status' => 1,
                    ));
                }
            }

            // menambahkan log penyelia
            $this->log->addLog('penyelia', array(
                'id_penyelia' => $penyelia->id_penyelia,
                'status' => $jobsNow->jobs->status,
                'message' => $this->log->noteLog('penyelia', $jobsNow->jobs->status),
                'note' => $note,
                'created_by' => Auth::user()->id
            ));

            // kondisi saat salah satu proses selesai
            if (!$nextJobs && !$jobsNow->point_jobs) {
                // $condition = $jobsNow->point_jobs ? 'whereNull' : 'whereNotNull';
                // $jobsParalel = Penyelia_map::where('status', 1)->$condition('point_jobs')->where('id_penyelia', $idPenyelia)->first();

                // if (!$jobsParalel) {
                    $permohonan = Permohonan::find($penyelia->id_permohonan);
                    $permohonan->update(['status' => 4]); // ketika proses lhu selesai

                    $penyelia->update(['status' => 3]);

                    // menambahkan log penyelia
                    $this->log->addLog('penyelia', [
                        'id_penyelia' => $penyelia->id_penyelia,
                        'status' => $penyelia->status,
                        'message' => $this->log->noteLog('penyelia', $penyelia->status),
                        'created_by' => Auth::user()->id
                    ]);
                // }
            }


            DB::commit();
            return $this->output(array('msg' => 'Berhasil mengupdate Progress'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function listPenyelia(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $menu = $request->has('menu') ? $request->menu : '';
        $filter = $request->has('filter') ? $request->filter : [];
        $userId = false;
        $status = false;
        $typePencarian = 'in';

        switch($menu) {
            case 'ttd-surat':
                $status = [1];
                $typePencarian = 'not';
                break;
            case 'penerbitanlhu':
                $status = [14];
                break;
            default:
                $status = false;
                break;
        }

        if(!$status){
            $paramStatus = $request->has('status') ? $request->status : false;
            if($paramStatus){
                $tmpArr = array();
                foreach ($paramStatus as $key => $value) {
                    array_push($tmpArr, decryptor($value));
                }
                $status = $tmpArr;
                $userId = Auth::user()->id;
            }
        }

        DB::beginTransaction();
        try {
            $query = Penyelia::with(
                'permohonan',
                'petugas',
                'petugas.jobs',
                'penyelia_map',
                'penyelia_map.jobs:id_jobs,status,name,upload_doc',
                'penyelia_map.jobs_paralel:id_jobs,status,name,upload_doc',
                'usersig:id,name',
                'permohonan.layanan_jasa:id_layanan,nama_layanan',
                'permohonan.jenisTld:id_jenisTld,name',
                'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                'permohonan.jenis_layanan_parent',
                'permohonan.pelanggan',
                'permohonan.pelanggan.perusahaan',
                'permohonan.kontrak',
                'permohonan.kontrak.periode',
                'permohonan.periodenow',
            )
            ->orderBy('id_penyelia','DESC')
            ->offset(($page - 1) * $limit)
            ->when($status, function($q, $status) use ($typePencarian) {
                if($typePencarian == 'not'){
                    return $q->whereNotIn('status', $status);
                }

                return $q->whereHas('penyelia_map', function ($query) use ($status) {
                    return $query->whereIn('id_jobs', $status)->where('status', 1)->whereHas('petugas', function ($q) {
                        return $q->where('id_user', Auth::user()->id);
                    });
                });
            })
            ->when($filter, function($q, $filter) {
                foreach ($filter as $key => $value) {
                    if ($key === 'id_perusahaan') {
                        $q->whereHas('permohonan.pelanggan.perusahaan', function ($v) use ($value) {
                            $v->where('id_perusahaan', decryptor($value));
                        });
                    } else if($key === 'status') {
                        $q->where($key, decryptor($value));
                    } else if ($key === 'date_range') {
                        $q->whereHas('permohonan.periodenow', function ($v) use ($value) {
                            $v->where(function($v) use ($value) {
                                $v->whereBetween('start_date', [$value[0], $value[1]])
                                    ->orWhereBetween('end_date', [$value[0], $value[1]])
                                    ->orWhere(function($v) use ($value) {
                                        $v->where('start_date', '<=', $value[0])
                                            ->where('end_date', '>=', $value[1]);
                                    });
                            });
                        });
                    } else {
                        $q->whereHas('permohonan', function ($p) use ($key, $value) {
                            $p->where($key, decryptor($value));
                        });
                    }
                }
            })
            ->when($userId, function($q, $userId) {
                return $q->whereHas('petugas', function ($query) use ($userId) {
                    return $query->where('id_user', $userId);
                });
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
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function getListPetugas(Request $request)
    {
        $search = $request->has('text') ? $request->text : '';
        $idUser = $request->has('idUser') ? decryptor($request->idUser) : false;

        DB::beginTransaction();
        try {
            $query = User::select("id","name", "jobs")
                    ->where('satuankerja_id', Auth::user()->satuankerja_id)
                    ->when($idUser, function($query, $idUser) {
                        return $query->where('id', $idUser);
                    })
                    ->when($search, function($query, $search) {
                        return $query->where('name', 'LIKE', '%'.$search.'%');
                    })
                    ->role('Staff');

            if($idUser){
                $query = $query->first();
            }else{
                $query = $query->get();
            }

            DB::commit();

            return response()->json($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function getPenyeliaById($idPenyelia)
    {
        DB::beginTransaction();
        try {
            $idPenyelia = decryptor($idPenyelia);

            $query = Penyelia::with(
                'permohonan',
                'petugas',
                'petugas.jobs',
                'penyelia_map',
                'penyelia_map.jobs:id_jobs,status,name,upload_doc',
                'usersig:id,name',
                'permohonan.layanan_jasa:id_layanan,nama_layanan',
                'permohonan.jenisTld:id_jenisTld,name',
                'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                'permohonan.jenis_layanan_parent',
                'permohonan.pelanggan',
                'permohonan.pelanggan.perusahaan',
                'permohonan.kontrak',
                'permohonan.kontrak.periode',
                'permohonan.dokumen',
                'permohonan.invoice',
                'permohonan.pengguna',
                'permohonan.pengguna.tld_pengguna',
                'permohonan.rincian_list_tld',
                'permohonan.rincian_list_tld.tld:id_tld,no_seri_tld',
                'permohonan.rincian_list_tld.pengguna_map',
                'permohonan.rincian_list_tld.pengguna_map.pengguna',
                'log',
                'log.user',
                'media'
            )->find($idPenyelia);
            DB::commit();

            if(isset($query->permohonan->list_tld) && count($query->permohonan->list_tld) > 0){
                $tldKontrol = Master_tld::whereIn('id_tld', $query->permohonan->list_tld)->get();
                $query->permohonan->tld_kontrol = $tldKontrol;
            }

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPenyeliaMapById($idPenyeliaMap)
    {
        DB::beginTransaction();
        try {
            $idPenyeliaMap = decryptor($idPenyeliaMap);

            $query = Penyelia_map::with(
                'jobs:id_jobs,status,name,upload_doc',
                'jobs_paralel:id_jobs,status,name,upload_doc',
                'petugas',
                'petugas.user',
                'doneBy:id,name',
                'penyelia:id_penyelia,status'
            )->find($idPenyeliaMap);
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadDokumenLhu(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idPenyelia = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'penyelia');
            $dataPenyelia = Penyelia::find($idPenyelia);

            if(isset($dataPenyelia)){
                $update = $dataPenyelia->update(array('document' => $fileUpload->getIdMedia()));

                DB::commit();

                if($update){
                    $fileUpload->store();
                    // ambil media dokumen lhu
                    $mediaDokumenLhu = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Dokumen lhu berhasil diupload', 'data' => $mediaDokumenLhu));
                }

                return $this->output(array('msg' => 'Dokumen lhu gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'Penyelia tidak ditemukan'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyDokumenLhu($idPenyelia, $idMedia)
    {
        $idPenyelia = decryptor($idPenyelia);
        $idMedia = decryptor($idMedia);

        DB::beginTransaction();
        try {
            $dataPenyelia = Penyelia::find($idPenyelia);

            if(isset($dataPenyelia)){
                $update = $dataPenyelia->update(array('document' => null));

                DB::commit();

                if($update){
                    $this->media->destroy($idMedia);
                    return $this->output(array('msg' => 'Dokumen lhu berhasil dihapus'));
                }

                return $this->output(array('msg' => 'Dokumen lhu gagal dihapus'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'Penyelia tidak ditemukan'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function removeSuratTugas($idPenyelia)
    {
        $idPenyelia = decryptor($idPenyelia);

        DB::beginTransaction();
        try {
            Penyelia_petugas::where('id_penyelia', $idPenyelia)->delete();
            Penyelia_map::where('id_penyelia', $idPenyelia)->delete();

            // update penyelia
            $penyelia = Penyelia::find($idPenyelia);
            $penyelia->update(array(
                'status' => 1,
                'start_date' => null,
                'end_date' => null
            ));

            // hapus dokumen surat tugas
            Permohonan_dokumen::where('id_permohonan', $penyelia->id_permohonan)->where('jenis', 'surattugas')->delete();

            // Log penyelia
            $this->log->addLog('penyelia', array(
                'id_penyelia' => $idPenyelia,
                'status' => 1,
                'message' => 'Surat tugas dihapus',
                'note' => '',
                'created_by' => Auth::user()->id
            ));

            DB::commit();

            return $this->output(array('msg' => 'Surat tugas berhasil dihapus!'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
