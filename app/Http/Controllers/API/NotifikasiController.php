<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\notifikasi;
use Auth;

class NotifikasiController extends Controller
{
    public function getNotifikasi(){
        $user = Auth::user();
        $data = notifikasi::where('recipient', $user->id)
                    ->where('status', '!=', 99)
                    ->with('getRecipient', 'getSender')
                    ->orderBy('created_at', 'DESC')
                    ->get();

        return response()->json(['data' => $data], 200);
    }

    public function setNotifikasi(Request $request){
        $validator = $request->validate([
            'id' => 'required'
        ]);

        $data = notifikasi::findOrFail($request->id);

        isset($request->status) ? $data->status = $request->status : false;

        $data->update();

        return response()->json(['message' => 'Berhasil di update'], 200);
    }
}
