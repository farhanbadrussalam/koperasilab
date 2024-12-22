<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Penyelia;
use App\Models\Permohonan;
use App\Models\Permohonan_pengguna;
use App\Models\User;
use App\Models\Master_pertanyaan;
use App\Models\Master_jobs;

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
        $userJobs = Auth::user()->jobs;
        $listJobs = array();
        foreach ($userJobs as $key => $value) {
            $dataJobs = Master_jobs::find($value);
            array_push($listJobs, encryptor($dataJobs->status));
        }

        $data = [
            'title' => 'LHU',
            'module' => 'staff-lhu',
            'listJobs' => $listJobs
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

    public function createSuratTugas($idPenyelia)
    {
        $idPenyelia = decryptor($idPenyelia);
        
        // Mendapatkan segmen terakhir dari URL
        $segmenTerakhir = request()->segment(count(request()->segments()) - 1);
        $typeSurat = '';
        switch ($segmenTerakhir) {
            case 'c':
                # code...
                $typeSurat = 'tambah';
                break;
            case 'e':
                # code...
                $typeSurat = 'update';
                break;
            case 'v':
                # code...
                $typeSurat = 'verif';
                break;
            case 's':
                # code...
                $typeSurat = 'show';
                break;
        }

        $query = Penyelia::with(
            'petugas',
            'petugas.jobs',
            'penyelia_map',
            'petugas.user:id,name,email',
            'permohonan',
            'usersig:id,name',
            'permohonan.kontrak',
            'permohonan.kontrak.periode',
            'permohonan.layanan_jasa:id_layanan,nama_layanan,jobs',
            'permohonan.jenisTld:id_jenisTld,name', 
            'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
            'permohonan.jenis_layanan_parent',
            'permohonan.pelanggan',
            'permohonan.pelanggan.perusahaan',
        )->find($idPenyelia);

        // mengambil data jobs
        $listJobs = array();
        if(count($query->penyelia_map) != 0){
            foreach ($query->penyelia_map as $key => $value) {
                $dataJobs = Master_jobs::find(decryptor($value->jobs_hash));
                $dataJobs['order'] = $value->order;
                array_push($listJobs, $dataJobs);
            }
        }else{
            foreach ($query->permohonan->layanan_jasa->jobs as $key => $jobs) {
                $dataJobs = Master_jobs::find($jobs);
                $dataJobs['order'] = $key+1;
                array_push($listJobs, $dataJobs);
            }
        }
        
        $data = [
            'title' => 'Surat tugas',
            'module' => 'staff-penyelia',
            'penyelia' => $query,
            'jobs' => $listJobs,
            'type' => $typeSurat
        ];

        return view('pages.staff.penyelia.suratTugas', $data);
    }

    public function indexPengiriman()
    {
        $data = [
            'title' => 'Pengiriman',
            'module' => 'staff-pengiriman'
        ];
        return view('pages.staff.pengiriman.index', $data);
    }

    public function indexPengirimanPermohonan()
    {
        $data = [
            'title' => 'Permohonan',
            'module' => 'staff-pengiriman-permohonan'
        ];
        return view('pages.staff.pengiriman.permohonan', $data);
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
                            'pelanggan',
                            'pelanggan.perusahaan',
                            'pelanggan.perusahaan.alamat',
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

    public function buatCustomPengiriman()
    {
        $data = [
            'title' => 'Buat Pengiriman',
            'module' => 'staff-pengiriman'
        ];
        return view('pages.staff.pengiriman.tambah', $data);
    }

    public function buatOrderPengiriman($idPermohonan)
    {
        $idPermohonan = decryptor($idPermohonan) ?? false;

        $dataPermohonan = Permohonan::with(
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name', 
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,id_perusahaan,name',
                'pelanggan.perusahaan',
                'pelanggan.perusahaan.alamat',
                'kontrak',
                'invoice',
                'lhu',
                'lhu.media'
            )->find($idPermohonan);

        $data = [
            'title' => 'Buat Pengiriman',
            'module' => 'staff-pengiriman-permohonan',
            'noPengiriman' => $this->generateNoPengiriman(),
            'permohonan' => $dataPermohonan
        ];

        return view('pages.staff.pengiriman.kirim', $data);
    }

    private function generateNoPengiriman() {
        // Format tanggal: milisecond (timestamp)
        $milliseconds = round(microtime(true) * 1000);
        
        // Angka acak (3 digit)
        $randomNumber = mt_rand(100, 999);
    
        // Kombinasi nomor pengiriman
        $noPengiriman = "D-" . $milliseconds . $randomNumber;
    
        return $noPengiriman;
    }
}
