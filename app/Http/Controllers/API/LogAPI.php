<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use Auth;
use DB;

class LogAPI extends Controller
{
    use RestApi;

    public function __construct()
    {
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
    }

    public function addLog(Request $request)
    {
        $validator = $request->validate([
            'id_permohonan' => 'required',
            'status' => 'required',
            'file' => 'nullable'
        ]);

        DB::beginTransaction();
        try {
            $idPermohonan = $request->id_permohonan ? decryptor($request->id_permohonan) : false;
            $status = $request->status ? $request->status : false;
            $note = $request->note ? $request->note : false;
            $file = $request->file('file') ?? false;
            $mode = $request->mode ? $request->mode : false;

            $data = array(
                'id_permohonan' => $idPermohonan,
                'status' => $status,
                'note' => $note,
                'created_by' => Auth::user()->id
            );

            $file && $data['file'] = $this->media->upload($file, 'permohonan')->getIdMedia();

            $log = $this->log->addLog($mode, $data);

            DB::commit();

            return $this->output(array('msg' => 'Log berhasil ditambahkan'), 'Success', 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function listLog(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $idPermohonan = $request->has('id_permohonan') ? decryptor($request->id_permohonan) : false;

        DB::beginTransaction();
        try {
            $query = DB::table('log_permohonan')
                        ->where('id_permohonan', $idPermohonan)
                        ->orderBy('created_at','DESC')
                        ->offset(($page - 1) * $limit)
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

}
