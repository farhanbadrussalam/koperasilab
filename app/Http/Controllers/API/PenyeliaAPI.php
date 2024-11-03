<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Penyelia;
use App\Models\User;

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
            $arrPetugas = array();
            $textNote = $request->note ? $request->note : '';

            $document = $request->file("document");
            $file_document = false;

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
            
            if($petugas){
                $arr = json_decode($petugas);

                foreach ($arr as $value) {
                    array_push($arrPetugas, decryptor($value));
                }

                $params['petugas'] = json_encode($arrPetugas);
            }

            $params['status'] = $status;

            $penyelia = Penyelia::where('id_penyelia', $idPenyelia)->first();
            if($penyelia){
                !$penyelia->created_by && $params['created_by'] = Auth::user()->id;
            }else{
                $params['created_by'] = Auth::user()->id;
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
            } else {
                $result['status'] = "none";
                $result['msg'] = "Nothing has changed.";
            }

            // log penyelia
            if($result['status'] != "none"){
                $note = $this->log->noteLog('penyelia', $status, $textNote);
                $this->log->addLog('penyelia', array(
                    'id_penyelia' => $penyelia->id_penyelia,
                    'status' => $status,
                    'note' => $note,
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
            return response()->json(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function listPenyelia(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $menu = $request->has('menu') ? $request->menu : '';
        // $idPermohonan = $request->has('idPermohonan') ? decryptor($request->idPermohonan) : false;

        switch($menu) {
            case 'surattugas':
                $status = [1];
                break;
            case 'start':
                $status = [2];
                break;
            case 'anealing':
                $status = [3];
                break;
            case 'pembacaan':
                $status = [4];
                break;
            case 'penerbitanlhu':
                $status = [5];
                break;
            case 'selesai':
                $status = [6];
                break;
            default:
                $status = false;
                break;
        }
        
        DB::beginTransaction();
        try {
            $query = Penyelia::with(
                            'permohonan',
                            'usersig:id,name',
                            'permohonan.layanan_jasa:id_layanan,nama_layanan',
                            'permohonan.jenisTld:id_jenisTld,name', 
                            'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                            'permohonan.jenis_layanan_parent',
                            'permohonan.pelanggan:id,name'
                        )
                        ->orderBy('created_at','DESC')
                        ->offset(($page - 1) * $limit)
                        ->when($status, function($q, $status) {
                            return $q->whereIn('status', $status);
                        })
                        ->limit($limit)
                        ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');

            foreach ($query as $key => $value) {
                if($value->petugas){
                    $value->petugas = User::select('id','name','jobs')->whereIn("id", json_decode($value->petugas))->get();
                }
            }

            DB::commit();

            return response()->json($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
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
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }
    }

    public function destroy(Request $request)
    {
        $idPenyelia = $request->idPenyelia ? decryptor($request->idPenyelia) : false;

        DB::beginTransaction();
        try {

        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
