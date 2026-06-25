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
use App\Models\Permohonan_dokumen;

use App\Models\Setting_layanan;

use App\Http\Controllers\API\TldAPI;
use App\Http\Controllers\API\PermohonanAPI;
use App\Http\Controllers\NotifController;
use App\Models\Documents;
use App\Models\Kontrak_detail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StaffController extends Controller
{
    protected PermohonanAPI $permohonan;
    protected TldAPI $tld;
    protected NotifController $notif;
    protected mixed $global;
    public function __construct()
    {
        $this->permohonan = resolve(PermohonanAPI::class);
        $this->notif = resolve(NotifController::class);
        $this->tld = resolve(TldAPI::class);
        $this->global = config('customvariabel');
    }

    public function indexApproval()
    {
        $data = [
            'title' => 'Approval',
            'module' => 'staff-approval'
        ];
        return view('pages.staff.pelanggan.index', $data);
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
        if ($userJobs != null) {
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

    public function indexPenyimpanan()
    {
        $data = [
            'title' => 'Penyimpanan TLD',
            'module' => 'staff-penyimpanan'
        ];
        return view('pages.staff.penyimpanan.index', $data);
    }

    public function indexPenyelia()
    {
        $userJobs = Auth::user()->jobs;
        $listJobs = array();
        $role = Auth::user()->getRoleNames()->toArray();
        if (in_array('Staff Penyelia', $role)) {
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
    public function indexJenisPembayaran()
    {
        $data = [
            'title' => 'Metode Pembayaran',
            'module' => 'staff-jenis-pembayaran'
        ];

        return view('pages.staff.pembayaran.index', $data);
    }


    public function createSuratTugas(string $idPenyelia)
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

        if (!$query) {
            // Check if it is a Permohonan_dokumen instead!
            $doc = Permohonan_dokumen::with([
                'permohonan',
                'kontrak',
                'kontrak.periode',
                'kontrak.jenisTld',
                'kontrak.jenis_layanan',
                'kontrak.jenis_layanan_parent',
                'kontrak.layanan_jasa',
                'kontrak.pelanggan',
                'kontrak.pelanggan.perusahaan',
            ])->find($idPenyelia);

            if ($doc) {
                // Construct a mock/virtual Penyelia object!
                $kontrak = $doc->kontrak;
                $permohonanData = $doc->permohonan ?: (object) [
                    'tipe_kontrak' => $kontrak->tipe_kontrak ?? null,
                    'jenis_layanan_parent' => $kontrak->jenis_layanan_parent ?? null,
                    'jenis_layanan' => $kontrak->jenis_layanan ?? null,
                    'jenis_tld' => $kontrak->jenisTld ?? null,
                    'layanan_jasa' => $kontrak->layanan_jasa ?? null,
                    'periode' => $doc->periode,
                    'created_at' => $doc->created_at,
                    'kontrak' => $kontrak,
                    'is_have_tld' => $kontrak->is_have_tld ?? 0,
                    'is_zerocek' => $kontrak->is_zerocek ?? 0,
                    'pelanggan' => $kontrak->pelanggan ?? null,
                    'dokumen' => collect([$doc])
                ];

                $query = new Penyelia([
                    'periode' => $doc->periode,
                    'status' => 2,
                    'created_at' => $doc->created_at,
                    'is_surpeng_signed' => $doc->ttd ? 1 : 0
                ]);
                $query->id_penyelia = $doc->id_dokumen;
                $query->setRelation('permohonan', $permohonanData);
                $query->setRelation('petugas', collect([]));
                $query->setRelation('penyelia_map', collect([]));
            }
        }

        if (!$query)
            abort(404);

        // mengambil data jobs
        $listJobs = array();
        $listJobsParalel = array();
        if (count($query->penyelia_map) != 0) {
            foreach ($query->penyelia_map as $key => $value) {
                $dataJobs = Master_jobs::find(decryptor($value->jobs_hash));
                $dataJobs['order'] = $value->order;

                if ($value->point_jobs == null) {
                    array_push($listJobs, $dataJobs);
                } else {
                    array_push($listJobsParalel, $dataJobs);
                }
            }
        } else {
            // Mengambil jobs dari layanan jasa
            $type = '';
            if ($query->permohonan->tipe_kontrak == 'adendum') {
                if ($query->permohonan->is_zerocek == 1) {
                    if ($query->permohonan->is_have_tld == 1) {
                        $type = 'havetld';
                    } else if ($query->permohonan->is_have_tld == 0) {
                        $type = 'nonhavetld';
                    }
                } else {
                    $type = 'adendum';
                }
            } else {
                $JL = jenislayanan($query->permohonan->jenis_layanan_parent, $query->permohonan->jenis_layanan);
                if (in_array($JL, $this->global['arr_putus'])) {
                    $type = 'putus';
                } else {
                    if ($query->permohonan->is_have_tld == 1) {
                        $type = 'havetld';
                    } else if ($query->permohonan->is_have_tld == 0) {
                        $type = 'nonhavetld';
                    }
                }
            }
            $listJobs = Setting_layanan::where('name', $type)->where('status', 1)->first()->list_jobs;
            $listJobsParalel = Setting_layanan::where('name', $type)->where('status', 1)->first()->list_jobs_paralel;
        }

        $context = request()->segment(count(request()->segments()) - 2);
        $titleStr = $context == 'surpeng' ? 'Surat Pengantar' : 'Surat Tugas';
        $moduleStr = $context == 'surpeng' ? 'manager-surpeng' : 'staff-penyelia';

        $data = [
            'title' => $titleStr,
            'module' => $moduleStr,
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

    public function verifikasiPermohonan(string $idPermohonan)
    {
        notifRead('Permohonan', $idPermohonan);
        $arrTandaTerima = [1, 4, 7];
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

        if (!$dataPermohonan)
            abort(404);

        if ($dataPermohonan->locked_by && $dataPermohonan->locked_by != Auth::user()->id) {
            $lockTime = Carbon::parse($dataPermohonan->locked_at);
            if (Carbon::now()->diffInMinutes($lockTime) < 3) {
                $lockedBy = User::find($dataPermohonan->locked_by);
                $userName = $lockedBy ? $lockedBy->name : 'Staff Lain';
                return redirect()->route('staff.permohonan')->with('error', "Permohonan sedang diverifikasi oleh {$userName}");
            }
        }
        $dataPermohonan->update([
            'locked_by' => Auth::user()->id,
            'locked_at' => Carbon::now()
        ]);

        if ($dataPermohonan && in_array($dataPermohonan->jenis_layanan_parent->id_jenisLayanan, $arrTandaTerima)) {
            $pertanyaan_tr = Master_pertanyaan::where('id_layananjasa', $dataPermohonan->layanan_jasa->id_layanan)->get();
        }
        if (isset($dataPermohonan->list_tld) && count($dataPermohonan->list_tld) > 0) {
            $dataPermohonan->tldKontrol = Master_tld::whereIn('id_tld', $dataPermohonan->list_tld)->get();
        } else if ($dataPermohonan->tld_kontrol) {
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

    public function buatOrderPengiriman(string $idHash, $periode = false)
    {
        $id = decryptor($idHash) ?? false;
        $idPeriode = decryptor($periode) ?? false;
        $data = false;

        if ($id) {
            if ($periode) {
                // melakukan set kontrak adendum
                $kontrakPeriode = Kontrak_periode::where('id_periode', $idPeriode)->first();
                if ($kontrakPeriode) {
                    // Generate surpeng document if not exists and associated permohonan is null
                    $associatedPermohonan = Permohonan::where('id_kontrak', $id)->where('periode', $kontrakPeriode->periode)->first();
                    if (!$associatedPermohonan && !$kontrakPeriode->nomer_surpeng) {
                        $noSurpeng = generateNoDokumen('surpeng');
                        $kontrakPeriode->update(['nomer_surpeng' => $noSurpeng, 'created_surpeng_at' => Carbon::now()]);

                        $dokumen = Permohonan_dokumen::firstOrNew([
                            'id_kontrak' => $id,
                            'periode' => $kontrakPeriode->periode,
                            'jenis' => 'surpeng'
                        ]);

                        if (!$dokumen->exists) {
                            $template = Documents::where('jenis', 'body')
                                ->where('name', 'SuratPengantar')
                                ->where('status', '1')
                                ->first();

                            $dokumen->fill([
                                'id_doc_template' => $template->id_doc ?? null,
                                'nama' => "Surat Pengantar",
                                'created_by' => Auth::id(),
                                'status' => 1
                            ]);
                        }

                        $dokumen->nomer = $noSurpeng;
                        $dokumen->save();

                        // Send notification to Manager Administrasi
                        $kontrakObj = Kontrak::with(['layanan_jasa'])->find($id);
                        if ($kontrakObj) {
                            $us = Auth::user();
                            $sk = $kontrakObj->layanan_jasa ? $kontrakObj->layanan_jasa->satuankerja_id : null;

                            $userQueryAdmin = User::role('Manager Administrasi')
                                ->when($sk, function ($query) use ($sk) {
                                    return $query->whereRaw('JSON_CONTAINS(satuankerja_id, ?)', [(string) $sk]);
                                });

                            $dataNotifAdmin = array(
                                'pesan' => 'Permintaan persetujuan Surat Pengantar untuk no kontrak <b>' . $kontrakObj->no_kontrak . '</b> diajukan oleh <b>' . $us->name . '</b>',
                                'url' => '/manager/surpeng/v/' . $dokumen->dokumen_hash,
                                'event' => 'Surpeng',
                                'event_id' => $dokumen->dokumen_hash,
                            );

                            \App\Services\Notifier::send($userQueryAdmin, $dataNotifAdmin);
                        }
                    }
                }

                $data = Kontrak::with([
                    'layanan_jasa:id_layanan,nama_layanan',
                    'jenisTld:id_jenisTld,name',
                    'jenis_layanan:id_jenisLayanan,name,parent',
                    'jenis_layanan_parent',
                    'pengiriman',
                    'pengiriman.detail',
                    'kontrak_detail:id,id_kontrak,id_pengguna_divisi,tld_1,status_tld_1,periode_tld_1,tld_2,status_tld_2,periode_tld_2,jenis',
                    'kontrak_detail.tld_1:id_tld,no_seri_tld,jenis',
                    'kontrak_detail.tld_2:id_tld,no_seri_tld,jenis',
                    'kontrak_detail.entitas' => function (MorphTo $morphTo) {
                        $morphTo->morphWith([
                            Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                        ]);
                    },
                    'periode' => function ($q) use ($idPeriode) {
                        $q->where('id_periode', $idPeriode);
                    },
                    'pelanggan',
                    'pelanggan.perusahaan',
                    'pelanggan.perusahaan.alamat',
                    'periode.permohonan',
                    'periode.permohonan.invoice',
                    'periode.permohonan.invoice.pengiriman',
                    'periode.permohonan.lhu',
                    'periode.permohonan.lhu.pengiriman',
                    'periode.permohonan.pengiriman',
                    'periode.permohonan.file_lhu',
                    'periode.permohonan.dokumen',
                    'periode.permohonan.pelanggan:id,id_perusahaan,name',
                    'periode.permohonan.pelanggan.perusahaan',
                    'periode.permohonan.pelanggan.perusahaan.alamat',
                    'periode.permohonan.alamat',
                    'dokumen'
                ])->find($id);

                // cek tld apakah sudah di kirim atau belum
                $statusTld = Pengiriman::with([
                    'detail' => function ($q) {
                        return $q->where('jenis', 'tld');
                    },
                    'permohonan',
                ])->where('id_kontrak', $id)
                    ->where('periode', $data->periode[0]->periode == 1 ? 0 : $data->periode[0]->periode)
                    ->whereHas('permohonan', function ($q) use ($idPeriode) {
                        $q->where('tipe_kontrak', '!=', 'adendum');
                    })
                    ->first();
                $data->statusTld = $statusTld->status ?? false;
                $data->sumber = 'kontrak';

                // Pengecekan adendum di kontrak periode
                $data->adendum = Kontrak_detail::with([
                    'entitas' => function (MorphTo $morphTo) {
                        $morphTo->morphWith([
                            Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                        ]);
                    },
                    'tld_1:id_tld,no_seri_tld,jenis',
                    'tld_2:id_tld,no_seri_tld,jenis',
                ])
                    ->where('id_kontrak', $id)
                    ->where('periode', $data->periode[0]->periode)
                    ->where('status', 2) // status 2 = adendum
                    // ->where('type', 'baru')
                    ->get();
            } else {
                $data = Permohonan::with([
                    'layanan_jasa:id_layanan,nama_layanan',
                    'jenisTld:id_jenisTld,name',
                    'jenis_layanan:id_jenisLayanan,name,parent',
                    'jenis_layanan_parent',
                    'kontrak',
                    'kontrak.kontrak_detail:id,id_kontrak,id_pengguna_divisi,tld_1,status_tld_1,periode_tld_1,tld_2,status_tld_2,periode_tld_2,jenis',
                    'kontrak.kontrak_detail.tld_1:id_tld,no_seri_tld,jenis',
                    'kontrak.kontrak_detail.tld_2:id_tld,no_seri_tld,jenis',
                    'alamat',
                    'kontrak.periode',
                    'kontrak.pengiriman',
                    'kontrak.pengiriman.detail',
                    'permohonan_detail',
                    'permohonan_detail.entitas' => function (MorphTo $morphTo) {
                        $morphTo->morphWith([
                            Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                        ]);
                    },
                    'permohonan_detail.tld',
                    'invoice',
                    'invoice.pengiriman',
                    'lhu',
                    'lhu.pengiriman',
                    'lhu.penyelia_map',
                    'lhu.penyelia_map.jobs',
                    'pengiriman',
                    'pengiriman.detail',
                    'file_lhu',
                    'dokumen',
                    'pelanggan:id,id_perusahaan,name',
                    'pelanggan.perusahaan',
                    'pelanggan.perusahaan.alamat',
                ])->find($id);
                $data->sumber = 'permohonan';

                if ($data->pengiriman == null) {
                    $data->pengiriman_baru = Pengiriman::with('detail')->where('id_kontrak', $data->id_kontrak)->where('periode', $data->periode)->get();
                }

                if ($data->tipe_kontrak == 'adendum' && $data->is_periode_berjalan == 1) {
                    // buatkan dokumen surpeng untuk TLD
                    $dokumen = Permohonan_dokumen::firstOrNew([
                        'id_permohonan' => $data->id_permohonan,
                        'periode' => $data->periode,
                        'jenis' => 'surpeng',
                    ]);

                    if (!$dokumen->exists) {
                        $noSurpeng = generateNoDokumen('surpeng');
                        $template = Documents::where('jenis', 'body')
                            ->where('name', 'SuratPengantar')
                            ->first();

                        $dokumen->fill([
                            'id_doc_template' => $template->id_doc,
                            'id_kontrak' => $data->id_kontrak,
                            'nama' => 'Surat Pengantar',
                            'created_by' => Auth::id(),
                            'status' => 1,
                            'nomer' => $noSurpeng
                        ]);
                        $dokumen->save();
                    }
                }
            }
        } else {
            return redirect()->back();
        }

        // membuat permohonan
        $result = [
            'title' => 'Buat Pengiriman',
            'module' => 'staff-pengiriman-permohonan',
            'noPengiriman' => $this->generateNoPengiriman(),
            'informasi' => $data
        ];

        return view('pages.staff.pengiriman.kirim', $result);
    }
    public function buatOrderPengembalian(string $idHash, Request $request)
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

        if (!$periodeNow) {
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

    private function generateNoPengiriman()
    {
        // Format tanggal: milisecond (timestamp)
        $milliseconds = round(microtime(true) * 1000);

        // Angka acak (3 digit)
        $randomNumber = mt_rand(100, 999);

        // Kombinasi nomor pengiriman
        $noPengiriman = "D-" . $milliseconds . $randomNumber;

        return $noPengiriman;
    }

    private function createPermohonan(int $idKontrak, int $periode)
    {
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
