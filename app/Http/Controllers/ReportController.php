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

        if($idKeuangan == null){
            return redirect()->back();
        }

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
        $data['ttd_default'] = public_path('icons/default/white.png');

        $pdf = PDF::loadView('report.invoice', $data);

        $pdf->render();

        return $pdf->stream();
    }

    public function kwitansi($id)
    {
        $idKeuangan = decryptor($id);

        if($idKeuangan == null){
            return redirect()->back();
        }

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

    public function tandaTerima($idPermohonan)
    {
        $idPermohonan = decryptor($idPermohonan);

        if($idPermohonan == null){
            return redirect()->back();
        }

        $query = Permohonan::with([
            'jenisTld:id_jenisTld,name', 
            'pelanggan',
            'pelanggan.perusahaan',
            'kontrak',
            'tandaterima',
            'tandaterima.pertanyaan',
            'dokumen' => function($query) {
                return $query->where('jenis', 'tandaterima');
            },
            'lhu',
            'signature:id,name',
        ])->find($idPermohonan);

        $data['data'] = $query;
        $data['date'] = Carbon::now();
        $data['title'] = "Tanda Terima";
        $data['ttd_default'] = public_path('icons/default/white.png');

        $pdf = PDF::loadView('report.tandaTerima', $data);

        $pdf->render();

        return $pdf->stream();;
    }

    public function suratTugas($id = null)
    {
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $query = Permohonan::with([
            'jenisTld:id_jenisTld,name', 
            'pelanggan',
            'pelanggan.perusahaan',
            'layanan_jasa',
            'jenis_layanan',
            'kontrak',
            'dokumen' => function($query) {
                return $query->where('jenis', 'surattugas');
            },
            'lhu',
            'lhu.petugas',
            'lhu.petugas.user:id,name',
            'lhu.petugas.jobs:id_map,id_jobs',
            'lhu.petugas.jobs.jobs:id_jobs,name',
            'lhu.createBy',
            'lhu.createBy.satuankerja',
            'lhu.usersig:id,name',
        ])->find($id);

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'SURAT TUGAS UJI';
        $data['ttd_default'] = public_path('icons/default/white.png');
        $data['data'] = $query;
        
        $pdf = PDF::loadView('report.suratTugas', $data);

        $pdf->render();

        return $pdf->stream();
    }

/**
 * Generates a PDF stream of the "Surat Pengantar" report.
 *
 * This function retrieves the report data based on the provided ID, 
 * sets the necessary title and date information, and then loads 
 * the 'suratPengantar' view to generate a PDF document. The PDF 
 * is then rendered and streamed back to the user.
 *
 * @param string|null $id Encrypted report identifier.
 * @return \Illuminate\Http\Response The PDF stream response.
 */

    public function suratPengantar($id  = null)
    {
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $query = Permohonan::with([
            'jenisTld:id_jenisTld,name', 
            'pelanggan',
            'pelanggan.perusahaan',
            'layanan_jasa',
            'jenis_layanan',
            'kontrak',
            'dokumen' => function($query) {
                return $query->where('jenis', 'surpeng');
            },
            'pengguna',
            'lhu',
            'lhu.petugas',
            'lhu.petugas.user:id,name',
            'lhu.petugas.jobs:id_map,id_jobs',
            'lhu.petugas.jobs.jobs:id_jobs,name',
            'lhu.createBy',
            'lhu.createBy.satuankerja',
            'lhu.usersig:id,name',
        ])->find($id);

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Pengantar';
        $data['data'] = $query;

        $pdf = PDF::loadView('report.suratPengantar', $data);

        $pdf->render();

        return $pdf->stream();
    }
}
