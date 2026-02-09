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
use App\Models\Master_pengguna;
use App\Models\Kontrak;
use App\Models\Kontrak_periode;
use App\Models\Kontrak_pengguna;
use App\Models\Kontrak_tld;
use App\Models\Master_tld;

use App\Models\Setting_layanan;

use App\Http\Controllers\API\TldAPI;
use App\Http\Controllers\API\PermohonanAPI;
use App\Http\Controllers\NotifController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StaffController extends Controller
{
    protected $permohonan, $tld, $global, $notif;
    public function __construct(){
        $this->permohonan = resolve(PermohonanAPI::class);
        $this->notif = resolve(NotifController::class);
        $this->tld = resolve(TldAPI::class);
        $this->global = config('customvariabel');
    }
    public function indexKeuangan()
    {
        $data = [
            'title' => 'Keuangan',
            'module' => 'staff-keuangan'
        ];
        notifRead('Keuangan');
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
        notifRead('PenyeliaLAB');
        $userJobs = Auth::user()->jobs;
        $listJobs = array();
        if($userJobs != null){
            foreach ($userJobs as $key => $value) {
                $dataJobs = Master_jobs::find($value);
                array_push($listJobs, $dataJobs->jobs_hash);
            }
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
        $userJobs = Auth::user()->jobs;
        $listJobs = array();
        $role = Auth::user()->getRoleNames()->toArray();
        if(in_array('Staff Penyelia', $role)){
            $dataJobs = Master_jobs::where('status', 14)->first();
            array_push($listJobs, $dataJobs->jobs_hash);
        }
        notifRead(['Penyelia', 'PenyeliaLAB']);
        $data = [
            'title' => 'Penyelia',
            'module' => 'staff-penyelia',
            'listJobs' => $listJobs
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
    public function indexJenisPembayaran() {
        $data = [
            'title' => 'Metode Pembayaran',
            'module' => 'staff-jenis-pembayaran'
        ];

        return view('pages.staff.pembayaran.index', $data);
    }


    public function createSuratTugas($idPenyelia)
    {
        // cek notifikasi read
        notifRead('SuratTugas', $idPenyelia);

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

        if(!$query)
            abort(404);

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
            $type = '';
            $JL = jenislayanan($query->permohonan->jenis_layanan_parent, $query->permohonan->jenis_layanan);
            if(in_array($JL, $this->global['arr_putus'])) {
                $type = 'putus';
            } else {
                if($query->permohonan->is_have_tld == 1){
                    $type = 'havetld';
                } else if ($query->permohonan->is_have_tld == 0) {
                    $type = 'nonhavetld';
                }
            }
            $listJobs = Setting_layanan::where('name', $type)->where('status', 1)->first()->list_jobs;
            $listJobsParalel = Setting_layanan::where('name', $type)->where('status', 1)->first()->list_jobs_paralel;
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
        notifRead(['PenyeliaLAB']);
        $data = [
            'title' => 'Permohonan',
            'module' => 'staff-pengiriman-permohonan'
        ];
        return view('pages.staff.pengiriman.permohonan', $data);
    }

    public function verifikasiPermohonan($idPermohonan)
    {
        notifRead('Permohonan', $idPermohonan);
        $arrTandaTerima = [1,4, 7];
        $id = decryptor($idPermohonan);
        $pertanyaan_tr = false;
        $dataPermohonan = Permohonan::with(
                            'file_lhu',
                            'layanan_jasa:id_layanan,nama_layanan',
                            'jenisTld:id_jenisTld,name',
                            'jenis_layanan:id_jenisLayanan,name,parent',
                            'jenis_layanan_parent',
                            'permohonan_pengguna',
                            'pelanggan',
                            'pelanggan.perusahaan',
                            'pelanggan.perusahaan.alamat',
                            'tandaterima',
                        )->where('id_permohonan', $id)->first();

        if(!$dataPermohonan)
            abort(404);

        if($dataPermohonan && in_array($dataPermohonan->jenis_layanan_parent->id_jenisLayanan, $arrTandaTerima)){
            $pertanyaan_tr = Master_pertanyaan::where('id_layananjasa', $dataPermohonan->layanan_jasa->id_layanan)->get();
        }
        if(isset($dataPermohonan->list_tld) && count($dataPermohonan->list_tld) > 0){
            $dataPermohonan->tldKontrol = Master_tld::whereIn('id_tld', $dataPermohonan->list_tld)->get();
        } else if($dataPermohonan->tld_kontrol){
            $dataPermohonan->tldKontrol = $dataPermohonan->tld_kontrol;
        }

        $layanan = jenislayanan($dataPermohonan->jenis_layanan_parent, $dataPermohonan->jenis_layanan);

        $isEvaluasi = in_array($layanan, $this->global['arr_evaluasi']);

        $data = [
            'title' => 'Verifikasi Permohonan',
            'module' => 'staff-permohonan',
            'permohonan' => $dataPermohonan,
            'pertanyaan' => $pertanyaan_tr,
            'isEvaluasi' => $isEvaluasi,
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
        $idPeriode = decryptor($periode) ?? false;
        $data = false;
        $periodeNow = false;
        $statusTld = false;
        $resTldPengguna = false;
        $resTldKontrol = false;
        // if($periode){
            // $idPeriode = decryptor($periode) ?? false;
            // mengambil periode sekarang
            // $periodeNow = Kontrak_periode::find($idPeriode);
            // dd($periodeNow);
            // mencari apakah ada permohonan di periode sekarang
            // $permohonan = Permohonan::select('id_permohonan')->where('id_kontrak', $id)->where('periode', $periodeNow->periode)->first();
            // $id = $permohonan ? $permohonan->id_permohonan : false;
        // }

        if($id){
            $data = Kontrak::with([
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'kontrak_detail',
                'kontrak_detail.tld_1',
                'kontrak_detail.tld_2',
                'kontrak_detail.entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'periode' => function ($q) use ($idPeriode) {
                    $q->where('id_periode', $idPeriode);
                },
                'periode.permohonan',
                'periode.permohonan.invoice',
                'periode.permohonan.invoice.pengiriman',
                'periode.permohonan.lhu',
                'periode.permohonan.lhu.pengiriman',
                'periode.permohonan.pengiriman',
                'periode.permohonan.file_lhu',
                'periode.permohonan.pelanggan:id,id_perusahaan,name',
                'periode.permohonan.pelanggan.perusahaan',
                'periode.permohonan.pelanggan.perusahaan.alamat',
            ])->find($id);
            // $data = Permohonan::with([
            //     'layanan_jasa:id_layanan,nama_layanan',
            //     'jenisTld:id_jenisTld,name',
            //     'jenis_layanan:id_jenisLayanan,name,parent',
            //     'jenis_layanan_parent',
            //     'pelanggan:id,id_perusahaan,name',
            //     'pelanggan.perusahaan',
            //     'pelanggan.perusahaan.alamat',
            //     'kontrak',
            //     'kontrak.periode',
            //     'kontrak.jenis_layanan',
            //     'kontrak.jenis_layanan_parent',
            //     'kontrak.kontrak_detail',
            //     'kontrak.kontrak_detail.tld_1',
            //     'kontrak.kontrak_detail.tld_2',
            //     'kontrak.kontrak_detail.entitas' => function (MorphTo $morphTo) {
            //         $morphTo->morphWith([
            //             Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
            //         ]);
            //     },
            //     'invoice',
            //     'invoice.pengiriman',
            //     'lhu',
            //     'lhu.pengiriman',
            //     'pengiriman',
            //     'file_lhu',
            //     'permohonan_detail',
            //     'permohonan_detail.tld',
            //     'permohonan_detail.entitas' => function (MorphTo $morphTo) {
            //         $morphTo->morphWith([
            //             Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
            //         ]);
            //     }
            // ])->find($id);

            // cek tld apakah sudah di kirim atau belum
            $statusTld = Pengiriman::with([
                'detail' => function($q){
                    return $q->where('jenis', 'tld');
                },
                'permohonan',
            ])->where('id_kontrak', $id)
            ->where('periode', $data->periode[0]->periode == 1 ? 0 : $data->periode)
            ->first();

            // cek apakah sudah di periode terakhir atau belum
            // $lastPeriode = Kontrak_periode::where('id_kontrak', $data->id_kontrak)->orderBy('periode', 'desc')->first();
            // $isLast = $lastPeriode->periode == $data->periode ? true : false;

            // if(!$isLast) {
                // Membuat kontrak_tld
            // }

            // mengambil periode dari kontrak_periode
            // $data->kontrak_periode = Kontrak_periode::where('id_kontrak', $data->id_kontrak)->where('periode', $data->periode)->first();
        }else{
            // $idKontrak = decryptor($idHash) ?? false;
            // if($periodeNow){
            //     $countTld = $periodeNow->periode % 2 == 1 ? 1 : 2;
            //     $kontrakTld = Kontrak_tld::where('id_kontrak', $idKontrak)->where('count_tld', $countTld)->get();
            //     // Jika tld kontrak untuk periode: $periode tidak ada akan menduplikat dari periode sebelumnya
            //     if(count($kontrakTld) == 0){
            //         $dataKontrakTldSebelum = Kontrak_tld::where('id_kontrak', $idKontrak)->where('count_tld', 1)->get();
            //         foreach($dataKontrakTldSebelum as $val){
            //             // Mengecek tld yang sedang di simpan di 2 periode sebelum dan digunakan lagi di periode ini
            //             $arr = array(
            //                 'id_kontrak' => $idKontrak,
            //                 'id_pengguna' => $val->id_pengguna,
            //                 'id_divisi' => $val->id_divisi,
            //                 'count_tld' => $countTld,
            //                 'status' => 6,
            //                 'count' => $val->count,
            //                 'created_by' => Auth::user()->id
            //             );
            //             Kontrak_tld::create($arr);
            //         }
            //     }
            // }

            // $data = Kontrak::with([
            //     'layanan_jasa:id_layanan,nama_layanan',
            //     'jenisTld:id_jenisTld,name',
            //     'jenis_layanan:id_jenisLayanan,name,parent',
            //     'jenis_layanan_parent',
            //     'pelanggan:id,id_perusahaan,name',
            //     'pelanggan.perusahaan',
            //     'pelanggan.perusahaan.alamat',
            //     'rincian_list_tld' => function ($query) use ($periodeNow) {
            //         $query->where('status', 6)->when($periodeNow, function ($q) use ($periodeNow) {
            //             return $q->where('count_tld', $periodeNow->periode % 2 == 1 ? 1 : 2);
            //         });
            //     },
            //     'rincian_list_tld.pengguna',
            //     'periode'
            // ])->find($idKontrak);

            // $tld_pengguna = $this->tld->searchTldNotUsed(new Request(['jenis' => 'pengguna']));
            // $tld_kontrol = $this->tld->searchTldNotUsed(new Request(['jenis' => 'kontrol']));

            // $resTldPengguna = json_decode($tld_pengguna->getContent(), true);
            // $resTldKontrol = json_decode($tld_kontrol->getContent(), true);

            // $indexPengguna = 0;
            // $indexKontrol = 0;

            // $data->rincian_list_tld->each(function($item) use (&$resTldPengguna, &$resTldKontrol, &$indexPengguna, &$indexKontrol) {
            //     if (!$item->id_tld) {
            //         if ($item->id_pengguna) {
            //             $item->tld = isset($resTldPengguna['data'][$indexPengguna]) ? [$resTldPengguna['data'][$indexPengguna]] : null;
            //             $indexPengguna++;
            //         } else {
            //             $tmp = [];
            //             for ($i = 0; $i < $item->count; $i++) {
            //                 $tmp[] = isset($resTldKontrol['data'][$indexKontrol]) ? $resTldKontrol['data'][$indexKontrol] : null;
            //                 $indexKontrol++;
            //             }
            //             $item->tld = $tmp;
            //         }
            //     }
            // });
        }

        // membuat permohonan
        $result = [
            'title' => 'Buat Pengiriman',
            'module' => 'staff-pengiriman-permohonan',
            'noPengiriman' => $this->generateNoPengiriman(),
            'informasi' => $data,
            'status_tld' => $statusTld
        ];

        $resTldPengguna ? $result['tld_pengguna'] = $resTldPengguna['data'] : null;
        $resTldKontrol ? $result['tld_kontrol'] = $resTldKontrol['data'] : null;

        return view('pages.staff.pengiriman.kirim', $result);
    }
    public function buatOrderPengembalian($idHash, Request $request)
    {
        // mengambil kontrak
        $idKontrak = decryptor($idHash);
        $periode = $request->has('periode') ? decryptor($request->periode) : false;
        // Log::info($idKontrak);

        // cek tld apakah sudah di kirim atau belum
        // $statusTld = Pengiriman::with([
        //     'detail' => function($q){
        //         return $q->where('jenis', 'tld');
        //     },
        //     'permohonan',
        // ])->where('id_kontrak', $data->id_kontrak)
        // ->where('periode', $data->periode == 1 ? null : $data->periode)
        // ->first();
        $periodeNow = Kontrak_periode::select('periode')->where('id_periode', $periode)->first();

        if(!$periodeNow){
            // melanjutkan periode berikutnya
            $periodeNow = Kontrak_periode::select('periode', 'end_date')->where('id_kontrak', $idKontrak)->where('periode', '>', 0)->orderBy('periode', 'desc')->first();
            $next = $periodeNow->periode + 1;
            $countTld = $next % 2 == 1 ? 1 : 2;
            // Log::info($next);

            $startDate = Carbon::parse($periodeNow->end_date);
            // awal bulan setelah startDate
            $startDate->modify('first day of +1 months');
            $startDate->setDate($startDate->format('Y'), $startDate->format('m'), 1);

            $endDate = clone $startDate;
            $endDate->modify('last day of +3 months');
            $endDate->setDate($endDate->format('Y'), $endDate->format('m'), 0);

            $periodePengembalian = Kontrak_periode::create([
                'id_kontrak' => $idKontrak,
                'periode' => $next,
                'count_tld' => $countTld,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 2,
                'created_by' => Auth::user()->id
            ]);
        }

        // $data = Kontrak::with([
        //     'layanan_jasa:id_layanan,nama_layanan',
        //     'jenisTld:id_jenisTld,name',
        //     'jenis_layanan:id_jenisLayanan,name,parent',
        //     'jenis_layanan_parent',
        //     'pelanggan:id,id_perusahaan,name',
        //     'pelanggan.perusahaan',
        //     'pelanggan.perusahaan.alamat',
        //     'rincian_list_tld' => function ($query) {
        //         $query->where('status', 5);
        //     },
        //     'rincian_list_tld.pengguna',
        //     'tld_aktif' => function ($query) {
        //         $query->where('status', 0);
        //     },
        // ])->find($idKontrak);

        // $result = [
        //     'title' => 'Buat Pengiriman',
        //     'module' => 'staff-pengiriman-permohonan',
        //     'noPengiriman' => $this->generateNoPengiriman(),
        //     'informasi' => $data, // $data,
        //     'periode' => false, // $periodeNow ? $periodeNow->periode : false,
        //     'status_tld' => false //$statusTld
        // ];

        // return view('pages.staff.pengiriman.kirim', $result);

        return redirect(Route('staff.pengiriman.permohonan.kirim.kontrak', [$idHash, $periodePengembalian->periode_hash]));
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
