<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class LayananjasaController extends Controller
{
    public function getPegawai(Request $request){

        $credential = $request->validate([
            'role' => ['required']
        ]);

        if($credential){
            $satuanKerja = isset($request->satuankerja) ? $request->satuankerja : null;
            $pegawai = User::role($request->role);

            if($satuanKerja){
                $pegawai->where('satuankerja_id', $satuanKerja);
            }

            $dataPegawai = $pegawai->get();

            return response()->json(['data' => $dataPegawai], 200);
        }else{
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

    }
}
