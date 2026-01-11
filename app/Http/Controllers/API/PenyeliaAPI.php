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
use App\Models\Documents;

use App\Models\Master_jobs;
use App\Models\Master_tld;
use App\Models\Master_pengguna;

use App\Models\Kontrak_tld;
use App\Models\Kontrak_periode;

use App\Services\Notifier;

use App\Http\Controllers\LogController;
use App\Http\Controllers\MediaController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenyeliaAPI extends Controller
{
    use RestApi;
    protected $log, $media, $global, $pagination;

    public function __construct()
    {
        $this->log = resolve(LogController::class);
        $this->media = resolve(MediaController::class);
        $this->global = config('customvariabel');
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
            $file_document && $params['document'] = $file_document->getIdMedia();

            $status && $params['status'] = $status;

            $penyelia = Penyelia::where('id_penyelia', $idPenyelia)->first();
            if($penyelia){
                !$penyelia->created_by && $params['created_by'] = Auth::user()->id;
            }else{
                $params['created_by'] = Auth::user()->id;
            }

            // menambahkan periode
            $dataPemohonan = Permohonan::select('periode', 'id_layanan', 'id_kontrak')
                ->with('layanan_jasa:id_layanan,satuankerja_id', 'kontrak:id_kontrak,no_kontrak')
                ->where('id_permohonan', $idPermohonan)->first();
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

                // send notification
                $userQuery = User::role("Staff Penyelia")->whereRaw('JSON_CONTAINS(satuankerja_id, ?)', [(String) $dataPemohonan->layanan_jasa->satuankerja_id]);
                $us = Auth::user();
                $dataNotif = array(
                    'pesan' => "Permohonan baru telah diajukan oleh <b>".$us->name."</b> no kontrak <b>".$dataPemohonan->kontrak->no_kontrak."</b>, silahkan cek penyelia.",
                    'url' => "/staff/penyelia",
                    "event_id" => $penyelia->penyelia_hash,
                    "event" => "Penyelia",
                );
                Notifier::send($userQuery, $dataNotif);
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

            $penyelia = Penyelia::with('permohonan', 'permohonan.jenis_layanan_parent', 'permohonan.layanan_jasa:id_layanan,satuankerja_id', 'permohonan.kontrak')->find($idPenyelia);
            if($penyelia){
                $penyelia->update($params);


                // simpan ttd di dokumen
                if($ttd){
                    Permohonan_dokumen::where('id_permohonan', $penyelia->id_permohonan)
                    ->where('jenis', 'surattugas')->where('status', 1)
                    ->update([
                        'ttd' => $ttd,
                        'ttd_by' => $ttd_by
                    ]);
                }

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
                    Penyelia_petugas::where('id_penyelia', $idPenyelia)->get()->each->delete();

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
                    $subQuery = Penyelia_map::with('jobs')->where('id_penyelia', $idPenyelia)->where('order', 1)->where('point_jobs', null)->first();
                    $subQuery->update(array('status' => 1));

                    // mengambil id user yang ada di jobs
                    $petugasUser = Penyelia_petugas::select('id_user')->where('id_map', $subQuery->id_map)->where('id_penyelia', $idPenyelia)->get();
                    // send notifikasi kepada petugas
                    $userQuery = array();
                    $us = Auth::user();
                    foreach($petugasUser as $value){
                        array_push($userQuery, $value->id_user);
                    }
                    $dataNotif = array(
                        'pesan' => "Proses <b>{$subQuery->jobs->name}</b> no kontrak <b>{$penyelia->permohonan->kontrak->no_kontrak}</b> di mulai",
                        'url' => '/staff/lhu',
                        'event' => 'PenyeliaLAB',
                        'event_id' => $penyelia->penyelia_hash,
                    );
                    Notifier::send($userQuery, $dataNotif);

                    $sideJobs = Penyelia_map::where('point_jobs', $subQuery->id_jobs)->where('id_penyelia', $idPenyelia)->first();
                    $sideJobs && $sideJobs->update(array('status' => 1));

                }

                // jika status = 2 akan mengirimkan notifikasi kepada manager untuk di tandatangani
                if($status == 2){
                    $userQuery = User::role('Manager')->whereRaw('JSON_CONTAINS(satuankerja_id, ?)', [(String) $penyelia->permohonan->layanan_jasa->satuankerja_id]);
                    $us = Auth::user();
                    $dataNotif = array(
                        'pesan' => 'Surat tugas uji di buat dengan no kontrak <b>'.$penyelia->permohonan->kontrak->no_kontrak.'</b> oleh <b>'.$us->name.'</b>',
                        'url' => '/manager/surat_tugas/v/'.$penyelia->penyelia_hash,
                        'event' => 'SuratTugas',
                        'event_id' => $penyelia->penyelia_hash,
                    );

                    Notifier::send($userQuery, $dataNotif);
                }

                // cek dokumen sudah ada atau belum
                $dokumen = Permohonan_dokumen::where('id_permohonan', $penyelia->id_permohonan)->where('jenis', 'surattugas')->first();

                if(!$dokumen){
                    // menambahkan dokumen perjanjian
                    $template = Documents::with('footer', 'header')
                        ->where('jenis', 'body')
                        ->where('name', 'SuratTugas')
                        ->where('status', '1')
                        ->first();

                    $penyeliaData = Penyelia::select('id_permohonan','id_penyelia')->find($idPenyelia);
                    $dataParams = array(
                        'id_permohonan' => $penyeliaData->id_permohonan,
                        'id_kontrak' => Permohonan::find($penyeliaData->id_permohonan)->id_kontrak,
                        'created_by' => Auth::user()->id,
                        'nama' => 'Surat Tugas Uji',
                        'jenis' => 'surattugas',
                        'id_doc_template' => $template->id_doc,
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
            $idPeriodeKontrak = $request->periodeNow ? decryptor($request->periodeNow) : false;

            $getPeriodeNow = Kontrak_periode::select('count_tld')->find($idPeriodeKontrak);

            $penyelia = Penyelia::with([
                'permohonan',
                'permohonan.kontrak.rincian_list_tld' => function($query) use ($getPeriodeNow) {
                    $query->where('count_tld', $getPeriodeNow->count_tld);
                },
                'permohonan.kontrak.jenis_layanan',
                'permohonan.kontrak.jenis_layanan_parent',
            ])->find($idPenyelia);
            $jobsNow = Penyelia_map::with('jobs')->where('id_map', $nowJobs)->first();

            $jobsNow->update(array(
                'status' => $sProgress == 'done' ? 2 : 0,
                'note' => $note,
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

                // send notifikasi ke petugas di jobs next
                $petugasUser = Penyelia_petugas::select('id_user')->where('id_map', $jobsNext->id_map)->get();
                $userQuery = array();
                $us = Auth::user();
                foreach($petugasUser as $value){
                    array_push($userQuery, $value->id_user);
                }
                $dataNotif = array(
                    'pesan' => "Proses <b>{$jobsNext->jobs->name}</b> no kontrak <b>{$penyelia->permohonan->kontrak->no_kontrak}</b> di mulai",
                    'url' => '/staff/lhu',
                    'event' => 'PenyeliaLAB',
                    'event_id' => $penyelia->penyelia_hash
                );
                Notifier::send($userQuery, $dataNotif);
            } else if($sProgress == 'done') {
                // kirim notifikasi ke pengiriman LHU telah selesai di upload
                $dataNotif = array(
                    'pesan' => "Proses <b>{$jobsNow->jobs->name}</b> no kontrak <b>{$penyelia->permohonan->kontrak->no_kontrak}</b> telah selesai",
                    'url' => '',
                    'event' => 'PenyeliaLAB',
                    'event_id' => $penyelia->penyelia_hash
                );
                $userQuery = User::role('Staff Pengiriman');
                Notifier::send($userQuery, $dataNotif);
            }

            if($sProgress == 'done') {
                // mencari jobs yang sifatnya paralel
                $jobsParalel = Penyelia_map::with('jobs:id_jobs,status,name')
                    ->where('order', 1)
                    ->where('id_penyelia', $idPenyelia)
                    ->where('point_jobs', $jobsNow->id_jobs)
                    ->first();

                if($jobsParalel){
                    if($jobsParalel->jobs->status == 17){ // Penyimpanan TLD
                        foreach($penyelia->permohonan->kontrak->rincian_list_tld as $key => $value){
                            if($value->status == 3) {
                                Master_tld::whereIn('id_tld', $value->id_tld)->update(array('status' => 0));
                                Kontrak_tld::where('id_kontrak_tld', $value->id_kontrak_tld)->update(['status' => 5]);

                                // mengecek jika sudah di periode terakhir
                                // Mengambil last periode
                                $kontrakPeriode = Kontrak_periode::where('id_kontrak', $penyelia->permohonan->kontrak->id_kontrak)->orderBy('periode', 'desc')->first();
                                $isLast = $kontrakPeriode->periode == $penyelia->permohonan->periode ? true : false;

                                if($isLast) {
                                    $layanan = jenislayanan($penyelia->permohonan->kontrak->jenis_layanan_parent, $penyelia->permohonan->kontrak->jenis_layanan);
                                    $isSewa = in_array($layanan, $this->global['arr_sewa']);
                                    if($isSewa){
                                        Master_tld::where('digunakan', $penyelia->permohonan->kontrak->no_kontrak)->update(array('status' => 0, 'digunakan' => null));
                                    }
                                    Master_pengguna::where('id_pengguna', $value->id_pengguna)->update(array('status' => 1));
                                }
                            } else if($value->status == 1) {
                                // if($penyelia->permohonan->is_zerocek == 0) {
                                //     Master_tld::whereIn('id_tld', $value->id_tld)->update(array('status' => 0));
                                //     kontrak_tld::where('id_kontrak_tld', $value->id_kontrak_tld)->update(['status' => 0]);
                                // }
                            }
                        }
                    }

                    $jobsParalel->update(array(
                        'status' => 1,
                    ));

                    // send notifikasi ke petugas di jobs paralel
                    $petugasUser = Penyelia_petugas::select('id_user')->where('id_map', $jobsParalel->id_map)->get();
                    $userQuery = array();
                    foreach($petugasUser as $value){
                        array_push($userQuery, $value->id_user);
                    }
                    $dataNotif = array(
                        'pesan' => "Proses <b>{$jobsParalel->jobs->name}</b> no kontrak <b>{$penyelia->permohonan->kontrak->no_kontrak}</b> di mulai",
                        'url' => '/staff/lhu',
                        'event' => 'PenyeliaLAB',
                        'event_id' => $penyelia->penyelia_hash
                    );
                    Notifier::send($userQuery, $dataNotif);
                } else {
                    if($jobsNow->jobs->status == 17){
                        foreach($penyelia->permohonan->kontrak->rincian_list_tld as $value){
                            // kondisi ketika setelah penyimpanan TLD
                            if($value->status == 5) {
                                $value->update(['status' => 6]);
                            }
                        }
                    }
                }
            }

            // menambahkan log penyelia
            // $this->log->addLog('penyelia', array(
            //     'id_penyelia' => $penyelia->id_penyelia,
            //     'status' => $jobsNow->jobs->status,
            //     'message' => $this->log->noteLog('penyelia', $jobsNow->jobs->status),
            //     'note' => $note,
            //     'created_by' => Auth::user()->id
            // ));

            // kondisi saat salah satu proses selesai
            if (!$nextJobs && !$jobsNow->point_jobs) {
                // $condition = $jobsNow->point_jobs ? 'whereNull' : 'whereNotNull';
                // $jobsParalel = Penyelia_map::where('status', 1)->$condition('point_jobs')->where('id_penyelia', $idPenyelia)->first();

                // if (!$jobsParalel) {
                    $permohonan = Permohonan::find($penyelia->id_permohonan);
                    $permohonan->update(['status' => 4]); // ketika proses lhu selesai

                    $penyelia->update(['status' => 3]);

                    // menambahkan log penyelia
                    // $this->log->addLog('penyelia', [
                    //     'id_penyelia' => $penyelia->id_penyelia,
                    //     'status' => $penyelia->status,
                    //     'message' => $this->log->noteLog('penyelia', $penyelia->status),
                    //     'created_by' => Auth::user()->id
                    // ]);
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
                $status = [1, 5];
                $typePencarian = 'not';
                break;
            case 'penyelialhu':
                $status = [4];
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
            } else {
                if(Auth::user()->hasRole('Staff LHU') && !Auth::user()->hasRole('Staff Penyelia')) {
                    $status = [99];
                }
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
                'permohonan.pelanggan.perusahaan.alamat',
                'permohonan.kontrak',
                'permohonan.kontrak.periode',
                'permohonan.kontrak.rincian_list_tld',
                'permohonan.kontrak.rincian_list_tld.pengguna',
                'permohonan.kontrak.jenis_layanan:id_jenisLayanan,name,parent',
                'permohonan.kontrak.jenis_layanan_parent:id_jenisLayanan,name',
                'permohonan.periodenow',
                'permohonan.dokumen',
                'permohonan.dokumen.doc_template',
            )
            ->when($status, function($q, $status) use ($typePencarian, $menu) {
                if($typePencarian == 'not'){
                    return $q->whereNotIn('status', $status);
                }

                return $q->whereHas('penyelia_map', function ($query) use ($status, $menu) {
                    $statusLhu = $menu == 'selesai' ? 2 : 1;
                    return $query->whereIn('id_jobs', $status)->where('status', $statusLhu)->whereHas('petugas', function ($q) {
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
                    } else if ($key === 'periode') {
                        $q->where('periode', $value);
                    } else {
                        $q->whereHas('permohonan', function ($p) use ($key, $value) {
                            $p->where($key, decryptor($value));
                        });
                    }
                }
            })
            ->when($userId, function($q, $userId) {
                return $q->whereHas('petugas', function ($p) use ($userId) {
                    return $p->where('id_user', $userId);
                });
            })
            ->whereHas('permohonan.layanan_jasa', function ($q) {
                return $q->whereIn('satuankerja_id', Auth::user()->satuankerja_id ? Auth::user()->satuankerja_id : [0]);
            })
            ->orderBy('id_penyelia','DESC')
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
                'penyelia_map.log.causer',
                'periodenow:id_periode,id_permohonan,count_tld,periode',
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
                'permohonan.kontrak.rincian_list_tld',
                'permohonan.kontrak.rincian_list_tld.pengguna',
                'permohonan.dokumen',
                'permohonan.invoice',
                'permohonan.permohonan_pengguna',
                'permohonan.rincian_list_tld',
                'permohonan.rincian_list_tld.pengguna',
                'log.causer',
            )->find($idPenyelia);
            DB::commit();

            if(isset($query->permohonan->rincian_list_tld) && count($query->permohonan->rincian_list_tld) > 0){
                $query->permohonan->rincian_list_tld->each(function($item) {
                    $item->tld = $item->id_tld ? Master_tld::whereIn('id_tld', $item->id_tld)->get() : null;
                });
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
                $arrIdDocument = array();
                if($dataPenyelia->document){
                    $arrIdDocument = $dataPenyelia->document;
                    $arrIdDocument[] = $fileUpload->getIdMedia();
                }else{
                    $arrIdDocument[] = $fileUpload->getIdMedia();
                }

                $update = $dataPenyelia->update(array('document' => $arrIdDocument));

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
                $arrIdDocument = $dataPenyelia->document;
                $arrIdDocument = array_filter($arrIdDocument, function($item) use ($idMedia) {
                    return $item != $idMedia;
                });
                $update = $dataPenyelia->update(array('document' => count($arrIdDocument) > 0 ? $arrIdDocument : null));

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
            Penyelia_petugas::where('id_penyelia', $idPenyelia)->get()->each->delete();
            Penyelia_map::where('id_penyelia', $idPenyelia)->get()->each->delete();

            // update penyelia
            $penyelia = Penyelia::find($idPenyelia);
            $penyelia->update(array(
                'status' => 1,
                'start_date' => null,
                'end_date' => null
            ));

            // hapus dokumen surat tugas
            Permohonan_dokumen::where('id_permohonan', $penyelia->id_permohonan)->where('jenis', 'surattugas')->get()->each->delete();

            DB::commit();

            return $this->output(array('msg' => 'Surat tugas berhasil dihapus!'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function createPengujian(Request $request)
    {
        $idPenyelia = $request->idPenyelia ? decryptor($request->idPenyelia) : false;
        $status = $request->status ? $request->status : false;
        $answers = $request->answers ? json_decode($request->answers) : false;

        DB::beginTransaction();
        try {
            $penyelia = Penyelia::with('permohonan')->find($idPenyelia);

            $penyelia->update(array(
                'status' => $status
            ));

            // mengambil template yg digunakan
            $template = $penyelia->template_surat->where('name', 'SuratPengujian')->first();

            $answers = array_map(function($answer) {
                $answer->id = (int) decryptor($answer->id);
                return $answer;
            }, $answers);

            // simpan ttd ke permohonan dokumen
            $document = Permohonan_dokumen::where('id_permohonan', $penyelia->id_permohonan)->where('jenis', 'permintaanpengujian')->first();

            if(!$document) {
                // generate nomer dokumen
                $nodokumen = generateNoDokumen('permintaanpengujian', $penyelia->id_permohonan);

                // set periode
                $arrPeriode = array();
                foreach($penyelia->permohonan->kontrak->periode as $periode) {
                    $arrPeriode[] = array($periode->start_date, $periode->end_date);
                }

                $contentValue = array(
                    'alasan' => $answers,
                    'periode' => $arrPeriode
                );

                // Simpan dokumen permintaan pengujian
                $document = Permohonan_dokumen::create(array(
                    'id_permohonan' => $penyelia->id_permohonan,
                    'id_doc_template' => $template->id_doc,
                    'id_kontrak' => $penyelia->permohonan->id_kontrak,
                    'created_by' => Auth::user()->id,
                    'nama' => 'Permintaan Pengujian',
                    'jenis' => 'SuratPengujian',
                    'status' => 1,
                    'nomer' => $nodokumen,
                    'content_value' => $contentValue,
                ));
            }

            // log penyelia
            $this->log->addLog('penyelia', array(
                'id_penyelia' => $idPenyelia,
                'status' => $status,
                'message' => 'Pengujian dibuat',
                'note' => '',
                'created_by' => Auth::user()->id
            ));

            DB::commit();

            return $this->output(array('msg' => 'Pengujian berhasil buat!'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
    public function approvePengujian(Request $request)
    {
        $idPenyelia = $request->idPenyelia ? decryptor($request->idPenyelia) : false;
        $ttd = $request->ttd ? $request->ttd : false;
        $catatan = $request->catatan ? $request->catatan : null;
        $type = $request->type ? $request->type : false;
        $ttd_by = Auth::user()->id;

        DB::beginTransaction();
        try {
            $status = $type == 'approve' ? 1 : 7;
            $penyelia = Penyelia::with('permohonan')->find($idPenyelia);
            $penyelia->update(array(
                'status' => $status
            ));

            // simpan ttd ke permohonan dokumen
            $dokumen = Permohonan_dokumen::where('id_permohonan', $penyelia->id_permohonan)->where('jenis', 'SuratPengujian')->first();
            $dokumen->update(array(
                'ttd' => $ttd,
                'ttd_by' => $ttd_by,
                'catatan' => $type
            ));

            // mengambil template yg digunakan
            $template = $penyelia->template_surat->where('name', 'KontrakPengujian')->first();

            // menambahkan dokumen perjanjian kontrak
            $no_kontrak = generateNoDokumen('KontrakPengujian', $penyelia->id_permohonan);
            $data = array(
                'id_kontrak' => $penyelia->permohonan->id_kontrak,
                'created_by' => Auth::user()->id,
                'nama' => 'Surat kontrak ('.convert_date($penyelia->permohonan->verify_at, 6).')',
                'jenis' => 'KontrakPengujian',
                'id_doc_template' => $template->id_doc,
                'status' => 1,
                'nomer' => $no_kontrak
            );
            $document = Permohonan_dokumen::create($data);

            DB::commit();

            return $this->output(array('msg' => $type == 'approve' ? 'Pengujian disetujui' : 'Pengujian ditolak'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
