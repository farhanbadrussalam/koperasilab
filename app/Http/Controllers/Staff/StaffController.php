<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Penyelia;
use App\Models\Pengiriman;
use App\Models\Permohonan;
use App\Models\Permohonan_pengguna;
use App\Models\User;
use App\Models\Master_pertanyaan;
use App\Models\Master_jobs;
use App\Models\Master_ekspedisi;
use App\Models\Kontrak;
use App\Models\Kontrak_periode;
use App\Models\Kontrak_pengguna;
use App\Models\Kontrak_tld;
use App\Models\Master_tld;

use App\Http\Controllers\API\PermohonanAPI;

use Auth;
use Log;

class StaffController extends Controller
{
    public function __construct(){
        $this->permohonan = resolve(PermohonanAPI::class);
    }
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
            array_push($listJobs, $dataJobs->jobs_hash);
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

    public function indexPetugas()
    {
        $data = [
            'title' => 'Petugas',
            'module' => 'staff-petugas-lhu'
        ];
        return view('pages.staff.petugas.index', $data);
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
        $listJobsParalel = array();
        if(count($query->penyelia_map) != 0){
            foreach ($query->penyelia_map as $key => $value) {
                $dataJobs = Master_jobs::find(decryptor($value->jobs_hash));
                $dataJobs['order'] = $value->order;
                
                if($value->point_jobs == null){
                    array_push($listJobs, $dataJobs);
                }else{
                    array_push($listJobsParalel, $dataJobs);
                }
            }
        }else{
            // Mengambil jobs dari layanan jasa
            $list = $query->permohonan->jenis_layanan_parent->jobs;
            $listParalel = $query->permohonan->jenis_layanan_parent->jobs_paralel;
            foreach ($list as $key => $value) {
                $dataJobs = Master_jobs::find($value);
                array_push($listJobs, $dataJobs);
            }

            foreach ($listParalel as $key => $value) {
                $dataJobs = Master_jobs::find($value);
                array_push($listJobsParalel, $dataJobs);
            }
        }
        $data = [
            'title' => 'Surat tugas',
            'module' => 'staff-penyelia',
            'penyelia' => $query,
            'jobs' => $listJobs,
            'jobsParalel' => $listJobsParalel,
            'jobsPoint' => Master_jobs::find($query->permohonan->jenis_layanan_parent->jobs_paralel_point)->first(),
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
        if(isset($dataPermohonan->list_tld) && count($dataPermohonan->list_tld) > 0){
            $dataPermohonan->tldKontrol = Master_tld::whereIn('id_tld', $dataPermohonan->list_tld)->get();
        } else if($dataPermohonan->tld_kontrol){
            $dataPermohonan->tldKontrol = $dataPermohonan->tld_kontrol;
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
        $id = decryptor($idHash) ?? false;
        $permohonan = false;
        $data = false;
        $periodeNow = false;
        $statusTld = false;
        if($periode){
            $idPeriode = decryptor($periode) ?? false;
            // mengambil periode sekarang
            $periodeNow = Kontrak_periode::find($idPeriode);
            // mencari apakah ada permohonan di periode sekarang
            $permohonan = Permohonan::where('id_kontrak', $id)->where('periode', $periodeNow->periode)->first();
            $id = $permohonan ? $permohonan->id_permohonan : false;
        }

        if($id){
            $data = Permohonan::with([
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name', 
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,id_perusahaan,name',
                'pelanggan.perusahaan',
                'pelanggan.perusahaan.alamat',
                'kontrak',
                'kontrak.periode',
                'kontrak.rincian_list_tld' => function ($query) {
                    $query->where('status', 1);
                },
                'kontrak.rincian_list_tld.pengguna:id_pengguna,nama,posisi',
                'kontrak.rincian_list_tld.tld',
                'invoice',
                'invoice.pengiriman',
                'lhu',
                'lhu.media',
                'pengiriman',
                'file_lhu',
                'pengguna',
                'rincian_list_tld',
                'rincian_list_tld.pengguna:id_pengguna,nama,posisi',
                'rincian_list_tld.tld',
            ])->find($id);

            // cek tld apakah sudah di kirim atau belum
            $statusTld = Pengiriman::where('id_kontrak', $data->id_kontrak)->where('periode', $data->periode)->first();

            // Membuat kontrak_tld
            $kontrakTld = Kontrak_tld::where('id_kontrak', $data->id_kontrak)->where('periode', $data->periode)->get();
            // Jika tld kontrak untuk periode: $periode tidak ada akan menduplikat dari periode sebelumnya 
            if(count($kontrakTld) == 0){
                $dataKontrakTldSebelum = Kontrak_tld::where('id_kontrak', $data->id_kontrak)->where('periode', $data->periode-1)->get();
                foreach($dataKontrakTldSebelum as $val){
                    Kontrak_tld::create([
                        'id_kontrak' => $data->id_kontrak,
                        'id_pengguna' => $val->id_pengguna,
                        'periode' => $data->periode,
                        'id_tld' => $val->id_tld,
                        'status' => 1,
                        'created_by' => Auth::user()->id
                    ]);
                }
            }

            // mengambil periode dari kontrak_periode
            $kontrakPeriode = Kontrak_periode::where('id_kontrak', $data->id_kontrak)->where('periode', $data->periode)->first();
            $data->kontrak_periode = $kontrakPeriode;
        }else{
            $idKontrak = decryptor($idHash) ?? false;
            $kontrakTld = Kontrak_tld::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode)->get();
            // Jika tld kontrak untuk periode: $periode tidak ada akan menduplikat dari periode sebelumnya 
            if(count($kontrakTld) == 0){
                $dataKontrakTldSebelum = Kontrak_tld::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode-1)->get();
                foreach($dataKontrakTldSebelum as $val){
                    // Mengecek tld yang sedang di simpan di 2 periode sebelum dan digunakan lagi di periode ini
                    // $cek = Kontrak_tld::where('id_kontrak', $idKontrak)
                    //         ->where('periode', $periodeNow->periode-2)
                    //         ->when($val->id_pengguna, function ($query) use ($val) {
                    //             return $query->where('id_pengguna', $val->id_pengguna);
                    //         })
                    //         ->where('status', 0)->first();
                    
                    $arr = array(
                        'id_kontrak' => $idKontrak,
                        'id_pengguna' => $val->id_pengguna,
                        'periode' => $periodeNow->periode,
                        'status' => 1,
                        'created_by' => Auth::user()->id
                    );
                    Kontrak_tld::create($arr);
                }
            }
            
            $data = Kontrak::with([
                'pengguna',
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,id_perusahaan,name',
                'pelanggan.perusahaan',
                'pelanggan.perusahaan.alamat',
                'rincian_list_tld' => function ($query) {
                    $query->where('status', 1);
                },
                'rincian_list_tld.pengguna:id_pengguna,nama,posisi',
                'rincian_list_tld.tld',
                'periode'
            ])->find($idKontrak);

        }

        // membuat permohonan
        $result = [
            'title' => 'Buat Pengiriman',
            'module' => 'staff-pengiriman-permohonan',
            'noPengiriman' => $this->generateNoPengiriman(),
            'informasi' => $data,
            'periode' => $periodeNow ? $periodeNow->periode : false,
            'status_tld' => $statusTld
        ];

        // dd($result);

        return view('pages.staff.pengiriman.kirim', $result);
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
            'list_tld' => null,
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
