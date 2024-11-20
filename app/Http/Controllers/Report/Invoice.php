<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Permohonan;
use App\Models\Keuangan;
use App\Models\Keuangan_diskon;

use PDF;
use Auth;

class Invoice extends Controller
{
    public function index($id)
    {
        $idKeuangan = decryptor($id);

        $query = Keuangan::with(
            'diskon',
            'usersig',
            'permohonan',
            'permohonan.layanan_jasa:id_layanan,nama_layanan',
            'permohonan.jenisTld:id_jenisTld,name', 
            'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
            'permohonan.jenis_layanan_parent',
            'permohonan.pelanggan',
            'permohonan.pelanggan.perusahaan',
            'permohonan.pelanggan.perusahaan.alamat',
            'permohonan.kontrak'
        )->where('id_keuangan', $idKeuangan)->first();

        $data['data'] = $query;
        $data['date'] = Carbon::now();
        $data['title'] = "Invoice";

        $pdf = PDF::loadView('report.invoice', $data);

        $pdf->render();

        return $pdf->stream();
    }
}
