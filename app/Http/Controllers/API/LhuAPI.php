<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\RestApi;

use App\Models\tbl_lhu;
use App\Models\tbl_kip;

use App\Http\Controllers\MediaController;

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
            $data_lhu = tbl_lhu::with('media')->where('id', $id_lhu)->first();

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
            $request->ttd_2 ? $data_lhu->ttd_2 = $request->ttd_2 : null;

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
        $validator =$request->validate([
            'file' => 'required',
            'idLhu' => 'required'
        ]);

        $idLhu = decryptor($request->idLhu);

        $lampiran = $request->file('file');
        $lhu = null;
        if($lampiran){
            $lhu = $this->media->upload($lampiran, 'lhu');
        }

        $data_lhu = tbl_lhu::where('id', $idLhu)->first();

        $data_lhu->document = $lhu;
        $data_lhu->level = 2;
        $data_lhu->active = 2;

        $data_lhu->update();

        if($data_lhu){
            $payload = array(
                'message' => 'Berhasil di kirim'
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Gagal mengirim LHU'
            ], 400);
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
}
