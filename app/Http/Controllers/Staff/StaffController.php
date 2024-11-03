<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Permohonan;
use App\Models\Permohonan_pengguna;
use App\Models\User;
use App\Models\Master_pertanyaan;

use Auth;

class StaffController extends Controller
{
    public function indexKeuangan()
    {
        $data = [
            'title' => 'Keuangan',
            'module' => 'staff-keuangan'
        ];
        return view('pages.staff.keuangan.index', $data);
    }

    public function indexPermohonan()
    {
        $data = [
            'title' => 'Permohonan',
            'module' => 'staff-permohonan'
        ];
        return view('pages.staff.permohonan.index', $data);
    }

    public function indexLhu()
    {
        $data = [
            'title' => 'LHU',
            'module' => 'staff-lhu'
        ];
        return view('pages.staff.lhu.index', $data);
    }

    public function indexPenyelia()
    {
        $data = [
            'title' => 'Penyelia',
            'module' => 'staff-penyelia'
        ];
        return view('pages.staff.penyelia.index', $data);
    }

    public function indexPengiriman()
    {
        $data = [
            'title' => 'Pengiriman',
            'module' => 'staff-pengiriman'
        ];
        return view('pages.staff.pengiriman.index', $data);
    }

    public function verifikasiPermohonan($idPermohonan)
    {
        $arrTandaTerima = [1];
        $id = decryptor($idPermohonan);
        $pertanyaan_tr = false;
        $dataPermohonan = Permohonan::with(
                            'layanan_jasa:id_layanan,nama_layanan',
                            'jenisTld:id_jenisTld,name', 
                            'jenis_layanan:id_jenisLayanan,name,parent',
                            'jenis_layanan_parent',
                            'pengguna',
                            'pelanggan:id,name'
                        )->where('id_permohonan', $id)->first();
        
        if($dataPermohonan && in_array($dataPermohonan->jenis_layanan_parent->id_jenisLayanan, $arrTandaTerima)){
            $pertanyaan_tr = Master_pertanyaan::where('id_jenisLayanan', $dataPermohonan->jenis_layanan_parent->id_jenisLayanan)->get();
        }

        $dataPengguna = Permohonan_pengguna::where('id_permohonan', $id)->first();
        $data = [
            'title' => 'Verifikasi Permohonan',
            'module' => 'staff-permohonan',
            'permohonan' => $dataPermohonan,
            'pertanyaan' => $pertanyaan_tr
        ];

        return view('pages.staff.permohonan.verifikasi', $data);
    }

    public function tambahPengiriman()
    {
        $data = [
            'title' => 'Buat Pengiriman',
            'module' => 'staff-pengiriman'
        ];
        return view('pages.staff.pengiriman.tambah', $data);
    }
}
