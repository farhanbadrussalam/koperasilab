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
use App\Models\Master_ekspedisi;
use App\Models\Kontrak;
use App\Models\Kontrak_periode;

use Auth;
use Log;

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

    public function indexPerusahaan()
    {
        $data = [
            'title' => 'Perusahaan',
            'module' => 'staff-perusahaan'
        ];
        return view('pages.staff.perusahaan.index', $data);
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
            'permohonan.layanan_jasa:id_layanan,nama_layanan',
            'permohonan.layanan_jasa.jobs_pelaksana',
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
            $listJobs = $query->permohonan->layanan_jasa->jobs_pelaksana;
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
        // Mengambil data dari master_ekspedisi
        $ekspedisi = Master_ekspedisi::all();

        $data = [
            'title' => 'Pengiriman',
            'module' => 'staff-pengiriman',
            'ekspedisi' => $ekspedisi
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
        $arrTandaTerima = [1,4];
        $id = decryptor($idPermohonan);
        $pertanyaan_tr = false;
        $dataPermohonan = Permohonan::with(
                            'file_lhu',
                            'layanan_jasa:id_layanan,nama_layanan',
                            'jenisTld:id_jenisTld,name', 
                            'jenis_layanan:id_jenisLayanan,name,parent',
                            'jenis_layanan_parent',
                            'pengguna',
                            'pelanggan',
                            'pelanggan.perusahaan',
                            'pelanggan.perusahaan.alamat',
                            'tandaterima',
                        )->where('id_permohonan', $id)->first();
        if($dataPermohonan && in_array($dataPermohonan->jenis_layanan_parent->id_jenisLayanan, $arrTandaTerima)){
            $pertanyaan_tr = Master_pertanyaan::where('id_layananjasa', $dataPermohonan->layanan_jasa->id_layanan)->get();
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

    public function buatOrderPengiriman($idHash, $periode = false)
    {
        if($periode){
            $idKontrak = decryptor($idHash) ?? false;
            $idPeriode = decryptor($periode) ?? false;

            // mengambil periode sekarang
            $periodeNow = Kontrak_periode::find($idPeriode);
            // mencari apakah ada permohonan di periode sekarang
            $permohonan = Permohonan::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode)->first();

            if($permohonan){
                $idPermohonan = $permohonan->id_permohonan;
            } else {
                $create = $this->createPermohonan($idKontrak, $periodeNow->periode);
                $idPermohonan = decryptor($create['data']['id']);
            }
        } else {
            $idPermohonan = decryptor($idHash) ?? false;
        }
        
        $dataPermohonan = Permohonan::with(
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name', 
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,id_perusahaan,name',
                'pelanggan.perusahaan',
                'pelanggan.perusahaan.alamat',
                'kontrak',
                'kontrak.periode',
                'invoice',
                'invoice.pengiriman',
                'lhu',
                'lhu.media',
                'pengiriman',
                'file_lhu'
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

    private function createPermohonan($idKontrak, $periode){
        $dataKontrak = Kontrak::find($idKontrak);

        $params = [
            'idKontrak' => encryptor($idKontrak),
            'periode' => $periode,
            'tipeKontrak' => 'kontrak lama',
            'jenisLayanan2' => encryptor($dataKontrak->jenis_layanan_2),
            'jenisLayanan1' => encryptor($dataKontrak->jenis_layanan_1),
            'dataTld' => json_encode($dataKontrak->list_tld),
            'createBy' => encryptor($dataKontrak->id_pelanggan),
            'status' => 11 // sewa
        ];

        // Make a request to your permohonanAction endpoint
        $permohonanResponse = app()->handle(Request::create(url('api/v1/permohonan/tambahPengajuan'), 'POST', $params));

        // Check the response for success/failure
        if ($permohonanResponse->getStatusCode() == 200) {
            // permohonan creation successful - you can log or further process if needed
            $permohonanData = json_decode($permohonanResponse->getContent(), true);
            // ... process $permohonanData
            return $permohonanData;
        } else {
            // Handle permohonan creation failure appropriately (log, rollback, etc.)
            Log::error("permohonan creation failed: " . $permohonanResponse->getContent());
            // ... consider throwing an exception or other error handling
        }
    
    }
}
