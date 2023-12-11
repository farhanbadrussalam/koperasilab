<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\tbl_kip;

class KeuanganController extends Controller
{
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.keuangan.index', $data);
    }

    public function sendKIP(Request $request)
    {
        $validator = $request->validate([
            'no_kontrak' => 'required',
        ]);

        $noInvoice = 'I-'.generate();
        $data = array(
            'no_kontrak' => $request->no_kontrak,
            'no_invoice' => $noInvoice,
            'harga' => $request->harga,
            'pajak' => $request->pajak,
            'status' => 1
        );

        tbl_kip::create($data);

        return response()->json(['message' => 'Invoice berhasil terkirim'], 200);
    }
}
