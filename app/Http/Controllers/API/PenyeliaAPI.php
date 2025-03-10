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
            $sProgress = $request->sProgress ? $request->sProgress : '';
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
            
            // Menambahkan jobs ke penyelia
            if($jobsMap){
                $arrJobsMap = json_decode($jobsMap);
                
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
            }

            // Menambahkan petugas
            if($petugas){
                $arr = json_decode($petugas);
                
                foreach ($arr as $value) {
                    $findMap = Penyelia_map::where('id_jobs', decryptor($value->idJobs))->where('id_penyelia', $idPenyelia)->first();
                    if($findMap){
                        $data = array(
                            'status' => 1,
                            'created_by' => Auth::user()->id
                        );

                        Penyelia_petugas::updateOrCreate(
                            [
                                'id_map' => decryptor($findMap->map_hash),
                                'id_penyelia' => $idPenyelia,
                                'id_user' => decryptor($value->idPetugas),
                            ],
                            $data
                        );
                    }
                }
                
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

            $params['status'] = $status;

            $penyelia = Penyelia::where('id_penyelia', $idPenyelia)->first();
            if($penyelia){
                !$penyelia->created_by && $params['created_by'] = Auth::user()->id;
            }else{
                $params['created_by'] = Auth::user()->id;
            }

            // cek penyelia map
            if($sProgress){
                $idJobs = false;
                if($sProgress == 'done') {
                    $idJobs = Master_jobs::select('id_jobs','status')->where('status', $penyelia->status)->first();
                    if($idJobs) {
                        Penyelia_map::where('id_jobs', $idJobs->id_jobs)
                            ->where('id_penyelia', $idPenyelia)
                            ->update(array(
                                'status' => 1,
                                'done_by' => Auth::user()->id,
                                'done_at' => date('Y-m-d H:i:s')
                            ));
                    }
                }else{
                    $idJobs = Master_jobs::select('id_jobs','status')->where('status', $status)->first();    
                    if($idJobs) {
                        Penyelia_map::where('id_jobs', $idJobs->id_jobs)
                            ->where('id_penyelia', $idPenyelia)
                            ->update(array(
                                'status' => 0,
                                'done_by' => null,
                                'done_at' => null
                            ));
                    }
                    // remove dokument LHU saat dikembalikan
                    $this->destroyDokumenLhu($penyelia->penyelia_hash, encryptor($penyelia->document));
                }

                // menambahkan log penyelia
                if($idJobs){
                    $this->log->addLog('penyelia', array(
                        'id_penyelia' => $penyelia->id_penyelia,
                        'status' => $sProgress == 'done' ? $idJobs->status : $penyelia->status,
                        'message' => $this->log->noteLog('penyelia', $sProgress == 'done' ? $idJobs->status : $penyelia->status, $jenisLog),
                        'note' => $textNote,
                        'created_by' => Auth::user()->id
                    ));
                    $flagSkipLog = true;
                }
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
                    // menambahkan dokumen surat pengantar 
                    $data = array(
                        'id_permohonan' => $penyelia->id_permohonan,
                        'created_by' => Auth::user()->id,
                        'nama' => 'SURAT PENGANTAR',
                        'jenis' => 'surpeng',
                        'status' => 1,
                        'nomer' => generateNoDokumen('surpeng')
                    );
                    $document = Permohonan_dokumen::create($data);

                    // log surat tugas
                    $this->log->addLog('penyelia', array(
                        'id_penyelia' => $penyelia->id_penyelia,
                        'message' => 'Surat tugas ditandatangani',
                        'created_by' => Auth::user()->id
                    ));
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
        // $idPermohonan = $request->has('idPermohonan') ? decryptor($request->idPermohonan) : false;

        switch($menu) {
            // case 'surattugas':
            //     $status = [2];
            //     $typePencarian = 'not';
                // break;
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
            //     $tmpArr = array();
            //     foreach ($paramStatus as $key => $value) {
            //         array_push($tmpArr, decryptor($value));
            //     }
            //     $status = $tmpArr;
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
                            'usersig:id,name',
                            'permohonan.layanan_jasa:id_layanan,nama_layanan',
                            'permohonan.jenisTld:id_jenisTld,name', 
                            'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                            'permohonan.jenis_layanan_parent',
                            'permohonan.pelanggan',
                            'permohonan.pelanggan.perusahaan',
                            'permohonan.kontrak',
                            'permohonan.kontrak.periode'
                        )
                        ->orderBy('id_penyelia','DESC')
                        ->offset(($page - 1) * $limit)
                        ->when($status, function($q, $status) use ($typePencarian) {
                            if($typePencarian == 'not'){
                                return $q->whereNotIn('status', $status);
                            }
                            return $q->whereIn('status', $status);
                        })
                        ->when($filter, function($q, $filter) {
                            foreach ($filter as $key => $value) {
                                if ($key === 'id_perusahaan') {
                                    $q->whereHas('permohonan.pelanggan.perusahaan', function ($v) use ($value) {
                                        $v->where('id_perusahaan', decryptor($value));
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
                'log',
                'media'
            )->find($idPenyelia);
            DB::commit();
    
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
            Penyelia::find($idPenyelia)->update(array(
                'status' => 1,
                'start_date' => null,
                'end_date' => null
            ));

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
        } catch (\Exception $th) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
