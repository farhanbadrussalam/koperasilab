<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Permohonan;
use App\Models\Keuangan;
use App\Models\Keuangan_diskon;
use App\Models\jadwal;
use App\Models\Jadwal_petugas;
use App\Models\Penyelia;

use PDF;
use Auth;

class ReportController extends Controller
{
    public function invoice($id)
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

    public function kwitansi($id)
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

    public function suratTugas($id)
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

    public function tandaTerima($idPermohonan)
    {
        $idPermohonan = decryptor($idPermohonan);
        $query = Permohonan::with(
            'jenisTld:id_jenisTld,name', 
            'pelanggan',
            'pelanggan.perusahaan',
            'kontrak'
        )->find($idPermohonan);

        $data['data'] = $query;
        $data['date'] = Carbon::now();
        $data['title'] = "Tanda Terima";

        $pdf = PDF::loadView('report.tandaTerima', $data);

        $pdf->render();

        return $pdf->stream();;
    }

    public function suratPengantar($id  = null)
    {
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Pengantar';
        $data['data'] = false;

        $pdf = PDF::loadView('report.suratPengantar', $data);

        $pdf->render();

        return $pdf->stream();
    }
}
