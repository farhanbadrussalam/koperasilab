<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Petugas_layanan;
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
}
