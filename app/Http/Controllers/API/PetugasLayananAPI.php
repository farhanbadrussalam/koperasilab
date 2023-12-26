<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Petugas_layanan;
use App\Models\Jadwal_petugas;
use App\Models\jadwal;
use App\Models\User;
use App\Traits\RestApi;

class PetugasLayananAPI extends Controller
{
    use RestApi;
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

    public function searchData(Request $request)
    {
        $search = $request->search ? $request->search : null;
        $idSatuankerja = $request->satuankerja_id ? $request->satuankerja_id : null;
        $data = Petugas_layanan::with('petugas:id,name')
                ->whereHas('petugas', function($query) use ($search, $idSatuankerja){
                    $query->where('name', 'like', "%$search%");
                    if($idSatuankerja){
                        $query->where('satuankerja_id', $idSatuankerja);
                    }
                })->get();

        return $this->output($data);
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
            $jadwal = jadwal::where('id', $idJadwal)->first();


            # Send notifikasi
            // $pjContent = $value == $layanan_jasa->user_id ? "dan menjadi Penanggung jawab" : "";
            $sendNotif = notifikasi(array(
                'to_user' => $idPetugas,
                'type' => 'jadwal'
            ), "Anda ditugaskan untuk layanan ".$jadwal->layananjasa->nama_layanan." pada tanggal ".$jadwal->date_mulai);

            return response()->json(['message' => 'Petugas berhasil ditambah'], 200);
        }

        return response()->json(['message' => 'Gagal ditambah'], 500);
    }

    public function updateJadwalPetugas(Request $request)
    {
        $idPetugas = decryptor($request->idPetugas);
        $idHash = decryptor($request->id);

        $jadwalPetugas = Jadwal_petugas::findOrFail($idHash);
        $jadwalPetugas->petugas_id = $idPetugas;
        $jadwalPetugas->status = 1;
        $jadwalPetugas->update();

        return response()->json(['message' => 'Petugas Berhasil diupdate'], 200);
    }

    public function destroyJadwalPetugas($jadwalPetugas_hash)
    {
        $idJadwalPetugas = decryptor($jadwalPetugas_hash);

        Jadwal_petugas::findOrFail($idJadwalPetugas)->delete();

        return response()->json(['message' => 'Jadwal petugas berhasil dihapus'], 200);
    }
}
