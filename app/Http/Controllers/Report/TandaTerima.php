<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Penyelia;

use PDF;
use Auth;

class TandaTerima extends Controller
{
    public function index($idPenyelia)
    {
        $idPenyelia = decryptor($idPenyelia);
        $query = Penyelia::with(
            'permohonan',
            'usersig:id,name',
            'permohonan.jenisTld:id_jenisTld,name', 
            'permohonan.pelanggan',
            'permohonan.pelanggan.perusahaan',
            'permohonan.kontrak'
        )->find($idPenyelia);

        $data['data'] = $query;
        $data['date'] = Carbon::now();
        $data['title'] = "Tanda Terima";

        $pdf = PDF::loadView('report.tandaTerima', $data);

        $pdf->render();

        return $pdf->stream();;
    }
}
