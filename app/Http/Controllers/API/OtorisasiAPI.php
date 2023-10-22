<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Auth;

class OtorisasiAPI extends Controller
{
    private $prefix = 'Otorisasi-';

    public function getOtorisasi(Request $request){
        $user_id = isset($request->user_id) ? (decryptor($request->user_id) ? decryptor($request->user_id) : $request->user_id) : null;
        $dataOtorisasi = array();
        if($user_id){
            $petugas = User::where('id', $user_id)->first();
            $dataOtorisasi = $petugas->getDirectPermissions();
        }else{
            $dataOtorisasi = Permission::orderBy('name', 'ASC')->where('name', 'like', $this->prefix.'%')->get();
        }

        return response()->json(['data' => $dataOtorisasi], 200);
    }
}
