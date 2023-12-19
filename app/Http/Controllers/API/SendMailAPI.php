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
        $nameOtorisasi = $request->nameOtorisasi ? decryptor($request->nameOtorisasi) : null;
        $idPetugasLayanan = $request->id ? decryptor($request->id) : null;

        $dataPetugas = Petugas_layanan::with('petugas')->where('id', $idPetugasLayanan)->first();

        $data['id'] = $request->id;
        $data['otorisasi'] = $nameOtorisasi;

        $mail = new SendVerifikasiPetugas($data);
        Mail::to($dataPetugas->petugas->email)->queue($mail);
    }
}
