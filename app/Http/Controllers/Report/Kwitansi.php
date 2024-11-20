<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Permohonan;
use App\Models\keuangan;

use PDF;
use Auth;

class Kwitansi extends Controller
{
    public function index($id)
    {
        $idKeuangan = decryptor($id);

        $query = keuangan::with(
            'permohonan',
            'permohonan.jenis_layanan',
            'permohonan.pelanggan',
            'permohonan.pelanggan.perusahaan'
        )->where('id_keuangan', $idKeuangan)->first();

        $data['data'] = $query;
        $data['title'] = 'Kwitansi';
        $data['date'] = Carbon::now();

        $pdf = PDF::loadView('report.kwitansi', $data);

        $pdf->render();

        return $pdf->stream();
    }
}
