<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\tbl_kip;

class KeuanganController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Keuangan',
            'module' => 'keuangan'
        ];

        return view('pages.keuangan.index', $data);
    }

    public function sendKIP(Request $request)
    {
        $validator = $request->validate([
            'id_permohonan' => 'required',
        ]);

        $noInvoice = 'I-'.generate();
        $data = array(
            'id_permohonan' => decryptor($request->id_permohonan),
            'no_invoice' => $noInvoice,
            'harga' => $request->harga,
            'pajak' => $request->pajak,
            'status' => 1
        );

        tbl_kip::create($data);

        return response()->json(['message' => 'Invoice berhasil terkirim'], 200);
    }
}
