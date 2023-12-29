<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Permohonan;

use PDF;

class SuratTugas extends Controller
{
    public function index($id)
    {
        $idPermohonan = decryptor($id);

        $dPermohonan = Permohonan::with('jadwal')->where('id', $idPermohonan)->first();

        $data = [
            'title' => 'Laravel Example PDF',
            'content' => 'This is a sample PDF'
        ];

        $pdf = PDF::loadView('report.suratTugas', $data);

        $pdf->render();

        return $pdf->stream();
    }
}
