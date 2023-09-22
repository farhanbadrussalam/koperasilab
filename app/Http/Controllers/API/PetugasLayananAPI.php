<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Petugas_layanan;
use App\Models\Jadwal_petugas;
use App\Models\jadwal;
use App\Models\User;

class PetugasLayananAPI extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getPetugas(Request $request)
    {
        $idPetugas = $request->idPetugas ? decryptor($request->idPetugas) : null;
        $idSatuankerja = $request->idSatuan ? decryptor($request->idSatuan) : null;

        $query = Petugas_layanan::with('satuankerja','lab', 'petugas:id,name,email', 'user:id,name,email')
                    ->where('status', '!=', 99);

        if($idPetugas){
            $query->where('id', $idPetugas);
            $dataPetugas = $query->first();
            if($dataPetugas){
                $petugas = User::where('id', $dataPetugas->petugas->id)->first();
                $dataPetugas['otorisasi'] = $petugas->getDirectPermissions();
            }
        } else if($idSatuankerja){
            $query->where('satuankerja_id', $idSatuankerja);
            $query->where('status_verif', 2);
            $dataPetugas = $query->get();
            foreach ($dataPetugas as $key => $value) {
                $petugas = User::where('id', $value->petugas->id)->first();
                $value['otorisasi'] = $petugas->getDirectPermissions();
            }
        } else{
            $query->orderBy('id', 'desc');
            $dataPetugas = $query->get();
        }


        return response()->json(['data' => $dataPetugas], 200);
    }

    public function getJadwalPetugas($jadwal_hash)
    {
        $idJadwal = decryptor($jadwal_hash);

        $dataPetugas = Jadwal_petugas::with('petugas')->where('jadwal_id', $idJadwal)->get();

        return response()->json(['data' => $dataPetugas], 200);
    }

    public function storeJadwalPetugas(Request $request)
    {
        $idPetugas = decryptor($request->idPetugas);
        $idJadwal = decryptor($request->idJadwal);

        $jadwalPetugas = Jadwal_petugas::create([
            'jadwal_id' => $idJadwal,
            'petugas_id' => $idPetugas,
            'status' => 1
        ]);

        if($jadwalPetugas){
            // $jadwal = jadwal::where('id', $idJadwal)->first();


            // # Send notifikasi
            // // $pjContent = $value == $layanan_jasa->user_id ? "dan menjadi Penanggung jawab" : "";
            // $sendNotif = notifikasi(array(
            //     'to_user' => $value,
            //     'type' => 'jadwal'
            // ), "Anda ditugaskan untuk layanan ".$jadwal->layananjasa->nama_layanan." pada tanggal ".$jadwal->date_mulai);

            return response()->json(['message' => 'Petugas berhasil ditambah'], 200);
        }

        return response()->json(['message' => 'Gagal ditambah'], 500);
    }
}
