<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\RestApi;

use App\Models\tbl_lhu;
use App\Models\tbl_kip;
use App\Models\Jawaban_lhu;
use App\Models\pertanyaan_lhu;

use App\Http\Controllers\MediaController;

use Auth;
use DB;

class LhuAPI extends Controller
{
    use RestApi;

    public function __construct(){
        $this->media = resolve(MediaController::class);
    }

    public function getDokumenLHU(string $id_lhu)
    {
        $id_lhu = $id_lhu ? decryptor($id_lhu) : null;

        if($id_lhu){
            $data_lhu = tbl_lhu::with('jawaban', 'jawaban.pertanyaan:id,title', 'signature_1')->where('id', $id_lhu)->first();

            return $this->output($data_lhu);
        }
    }

    public function getDokumenKIP(string $id_kip)
    {
        $id_kip = $id_kip ? decryptor($id_kip) : null;

        if($id_kip){
            $data_lhu = tbl_kip::with(['permohonan', 'permohonan.layananjasa', 'permohonan.user'])
                        ->where('id', $id_kip)->first();

            return $this->output($data_lhu);
        }
    }

    public function validasiLHU(Request $request)
    {
        $validator = $request->validate([
            'idLhu' => 'required'
        ]);

        $idLhu = decryptor($request->idLhu);

        $data_lhu = tbl_lhu::where('id', $idLhu)->first();


        if($request->active == 99){
            $data_lhu->delete();
        }else{
            $request->level ? $data_lhu->level = $request->level : null;
            $request->ttd_1 ? $data_lhu->ttd_1 = $request->ttd_1 : null;
            $request->ttd_1_by ? $data_lhu->ttd_1_by = decryptor($request->ttd_1_by) : null;
            $request->ttd_2 ? $data_lhu->ttd_2 = $request->ttd_2 : null;
            $request->ttd_2_by ? $data_lhu->ttd_2_by = decryptor($request->ttd_2_by) : null;

            $data_lhu->update();
        }


        if($data_lhu){
            $payload = array(
                'message' => 'success'
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Fail'
            ], 400);
        }
    }

    public function validasiKIP(Request $request)
    {
        $validator = $request->validate([
            'idKip' => 'required'
        ]);

        $idKip = decryptor($request->idKip);

        $data_kip = tbl_kip::where('id', $idKip)->first();

        $request->ttd_1 ? $data_kip->ttd_1 = $request->ttd_1 : null;
        $request->status ? $data_kip->status = $request->status : null;

        $data_kip->update();

        if($data_kip){
            $payload = array(
                'message' => 'success'
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Fail'
            ], 400);
        }
    }

    public function sendDokumen(Request $request)
    {
        $validator = $request->validate([
            'ttd_1' => 'required',
            'id_jadwal' => 'required',
            'answer' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $idJadwal = decryptor($request->id_jadwal);
            $answer = json_decode($request->answer);
            $ttd = $request->ttd_1;
            $ttd_by = Auth::user()->id;

            $data = array(
                'id_jadwal' => $idJadwal,
                'ttd_1' => $ttd,
                'ttd_1_by' => $ttd_by,
                'created_by' => $ttd_by,
                'level' => 2,
                'active' => 2,
                'tgl_selesai' => convert_date(now(), 5)
            );

            $data_lhu = tbl_lhu::create($data);

            if(isset($data_lhu)){
                foreach ($answer as $key => $value) {
                    Jawaban_lhu::create(array(
                        'lhu_id' => $data_lhu->id,
                        'pertanyaan_id' => decryptor($value->pertanyaan_id),
                        'jawaban' => $value->jawaban,
                        'created_by' => $ttd_by
                    ));
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'LHU terkirim!'
            ], 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }

    }

    public function sendToPelanggan(Request $request)
    {
        $validator = $request->validate([
            'idLhu' => 'required',
            'idKip' => 'required'
        ]);

        $idLhu = decryptor($request->idLhu);
        $idKip = decryptor($request->idKip);

        $data_lhu = tbl_lhu::where('id', $idLhu)->update([
            'level' => 5
        ]);

        $data_kip = tbl_kip::where('id', $idKip)->update([
            'status' => 3
        ]);

        if($data_lhu && $data_kip){
            $payload = array(
                'message' => 'Document Berhasil di kirim'
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Gagal mengirim'
            ], 400);
        }
    }

    public function sendPayment(Request $request)
    {
        $validator = $request->validate([
            'idKip' => 'required',
            'file' => 'required'
        ]);

        $idKip = decryptor($request->idKip);
        $file = $request->file('file');

        if($file){
            $imgKip = $this->media->upload($file, 'kip');
        }

        $data_kip = tbl_kip::where('id', $idKip)->first();

        $data_kip->bukti_pembayaran = $imgKip;
        $data_kip->update();

        if($data_kip){
            $payload = array(
                'message' => 'Bukti berhasil di kirim'
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Gagal mengirim'
            ], 400);
        }
    }

    public function ambilPertanyaanLhu(Request $request)
    {
        $idLhu = $request->id_lhu ? $request->id_lhu : null;

        $data = pertanyaan_lhu::all();

        return $this->output($data);
    }
}
