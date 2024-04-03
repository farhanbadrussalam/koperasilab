<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\jadwal;
use App\Models\Jadwal_petugas;

use PDF;
use Auth;

class SuratTugas extends Controller
{
    public function index($id)
    {
        $idJadwal = decryptor($id);
        $Rjadwal = jadwal::with(
            'petugas',
            'signature_1',
            'petugas.petugas',
            'permohonan',
            'permohonan.layananjasa.satuanKerja',
            'permohonan.user.perusahaan'
            )->where('id', $idJadwal)->first();

        $data['date'] = Carbon::now()->year;
        $data['data'] = $Rjadwal;

        $pdf = PDF::loadView('report.suratTugas', $data);

        $pdf->render();

        return $pdf->stream();
    }
}
