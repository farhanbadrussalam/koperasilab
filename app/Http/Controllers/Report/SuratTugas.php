<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Permohonan;
use App\Models\Jadwal_petugas;

use PDF;
use Auth;

class SuratTugas extends Controller
{
    public function index($id)
    {
        $idPermohonan = decryptor($id);

        $dPermohonan = Permohonan::with(
                'jadwal',
                'layananjasa',
                'layananjasa.satuanKerja',
                'layananjasa.manager:id,name',
                'petugas',
                'petugas.petugas',
                'user',
                'user.perusahaan'
            )
            ->where('id', $idPermohonan)
            ->first();

        $jadwalPetugas = Jadwal_petugas::where('petugas_id', Auth::user()->id)
            ->where('permohonan_id', $idPermohonan)
            ->first();

        $data['permohonan'] = $dPermohonan;
        $data['date'] = Carbon::now()->year;
        $data['jadwalPetugas'] = $jadwalPetugas;

        $pdf = PDF::loadView('report.suratTugas', $data);

        $pdf->render();

        return $pdf->stream();
    }
}
