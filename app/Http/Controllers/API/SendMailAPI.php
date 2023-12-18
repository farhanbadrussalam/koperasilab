<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\RestApi;

use Spatie\Permission\Models\Permission;
use App\Models\Petugas_layanan;

use App\Mail\SendVerifikasiPetugas;
use Mail;

class SendMailAPI extends Controller
{
    public function verifikasiPetugas(Request $request) {
        $idPermission = $request->otorisasi ? decryptor($request->otorisasi) : null;
        $idPetugasLayanan = $request->id ? decryptor($request->id) : null;

        $data['id'] = $request->id;
        $data['otorisasi'] = Permission::where('id', $idPermission)->first();

        $mail = new SendVerifikasiPetugas($data);
        Mail::to('badrussalam859@gmail.com')->queue($mail);
    }
}
