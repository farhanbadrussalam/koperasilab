<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Petugas_layanan;
use App\Models\Jadwal_petugas;
use App\Models\jadwal;
use App\Models\User;
use App\Traits\RestApi;

use DB;

class PetugasLayananAPI extends Controller
{
    use RestApi;
    /**
     * Display a listing of the resource.
     */
    // NEW API
    public function listPetugas(Request $request)
    {
        $idJobs = isset($request->idJobs) ? decryptor($request->idJobs) : false;
        DB::beginTransaction();
        try {
            $query = User::select('id','name', 'email')->whereRaw('JSON_CONTAINS(jobs, ?)', [$idJobs])->get();
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
        
    }

    // OLD API
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
}
