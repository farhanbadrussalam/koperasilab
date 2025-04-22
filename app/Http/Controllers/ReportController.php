<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Kontrak;
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
        $data['stempel'] = public_path('icons/Stempel-Lab.png');

        $periodePemakaian = $query->permohonan->periode_pemakaian;

        if($query->permohonan && count($periodePemakaian) > 0){
            $data['periode_start'] = $periodePemakaian[0];
            $data['periode_end'] = $periodePemakaian[count($periodePemakaian) - 1] ?? null;
        }
        
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
            'permohonan.pelanggan.perusahaan',
            'permohonan.kontrak:id_kontrak,no_kontrak'
        )->where('id_keuangan', $idKeuangan)->first();

        $data['data'] = $query;
        $data['title'] = 'Kwitansi';
        $data['date'] = Carbon::now();
        $data['ttd_default'] = public_path('icons/default/white.png');
        $data['stempel'] = public_path('icons/Stempel-Lab.png');

        // mengambil periode pertama dan terakhir
        $start = $query->permohonan->periode_pemakaian[0]['start_date'];
        $end = $query->permohonan->periode_pemakaian[count($query->permohonan->periode_pemakaian) - 1]['end_date'];
        $data['periode_start'] = convert_date($start, 6);
        $data['periode_end'] = convert_date($end, 6);

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
            'jenis_layanan:id_jenisLayanan,name',
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

    public function suratPengantar($id  = null, $periode = null)
    {
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $query = Kontrak::with([
            'jenisTld:id_jenisTld,name',
            'pelanggan',
            'pelanggan.perusahaan',
            'layanan_jasa:id_layanan,nama_layanan',
            'jenis_layanan:id_jenisLayanan,name',
            'pengguna',
            'periode' => function($query) use ($periode) {
                return $query->where('periode', $periode);
            },
            'rincian_list_tld' => function($query) {
                return $query->where('status', 1);
            },
            'rincian_list_tld.tld',
            'rincian_list_tld.pengguna'
        ])->find($id);

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Pengantar';
        $data['data'] = $query;

        $pdf = PDF::loadView('report.suratPengantar', $data);

        $pdf->render();

        return $pdf->stream();
    }

    public function perjanjian($id = null){
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $query = Kontrak::with(
            'jenisTld:id_jenisTld,name',
            'jenis_layanan:id_jenisLayanan,name',
            'jenis_layanan_parent:id_jenisLayanan,name',
            'layanan_jasa:id_layanan,nama_layanan',
        )->find($id);

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Kontrak';
        $data['data'] = $query;
        // dd($query);

        $pdf = PDF::loadView('report.perjanjian', $data);
        $pdf->render();

        // Dapatkan canvas dari DomPDF
        $canvas = $pdf->getDomPDF()->get_canvas();

        // Tentukan posisi dan sudut rotasi
        $canvas->save(); // Simpan state awal canvas
        $canvas->rotate(-45, $canvas->get_width() / 2, $canvas->get_height() / 2); // Rotasi -45 derajat di tengah halaman

        // Tambahkan teks "DRAFT" di latar belakang
        $canvas->set_opacity(0.1); // Transparansi teks
        $canvas->text(150, 350, 'DRAFT', null, 100, [0, 0, 0]);

        $canvas->restore(); // Kembali ke state awal setelah rotasi

        return $pdf->stream();
    }
}
