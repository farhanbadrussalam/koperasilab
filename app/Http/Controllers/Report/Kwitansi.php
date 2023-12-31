<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Permohonan;
use App\Models\tbl_kip;

use PDF;
use Auth;

class Kwitansi extends Controller
{
    public function index($id)
    {
        $idKip = decryptor($id);

        $dKip = tbl_kip::with('permohonan', 'permohonan.user', 'permohonan.user.perusahaan', 'user')->where('id', $idKip)->first();

        $data['kip'] = $dKip;
        $data['date'] = Carbon::now();

        $pdf = PDF::loadView('report.kwitansi', $data);

        $pdf->render();

        return $pdf->stream();
    }
}
