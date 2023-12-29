<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jadwal_petugas;
use App\Traits\RestApi;
use Auth;

class PenugasanAPI extends Controller
{
    use RestApi;

    public function show($id)
    {
        $idPenugasan = decryptor($id);
        $data = Jadwal_petugas::with('permohonan', 'permohonan.layananjasa', 'jadwal:id,date_mulai,date_selesai')
            ->where('id', $idPenugasan)
            ->first();

        return $this->output($data);
    }

    public function update(Request $request)
    {
        $id_jadwal_petugas = $request->idJadwalPetugas ? decryptor($request->idJadwalPetugas) : null;
        $status = $request->status ? decryptor($request->status) : null;

        $tmp = array();

        if($status){
            $tmp['status'] = $status;
        };

        $dJadwalPetugas = Jadwal_petugas::where('id', $id_jadwal_petugas)->first();

        if($status){
            $dJadwalPetugas->status = $status;
        }
        $dJadwalPetugas->update();

        if($dJadwalPetugas){
            $payload = array(
                'message' => $status == 2 ? 'Anda bersedia' : 'Anda tidak bersedia',
                'info' => $dJadwalPetugas
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Fail'
            ], 400);
        }
    }
}
