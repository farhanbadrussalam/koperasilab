<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Traits\RestApi;

use App\Models\Master_tld;
use App\Models\Master_media;
use App\Models\Master_pengguna;
use App\Models\Pengiriman;
use App\Models\Pengiriman_detail;
use App\Models\Permohonan;
use App\Models\Permohonan_pengguna;
use App\Models\Permohonan_dokumen;
use App\Models\Documents;
use App\Models\User;
use App\Models\Keuangan;
use App\Models\Penyelia;
use App\Models\Kontrak;
use App\Models\Kontrak_pengguna;
use App\Models\Kontrak_tld;
use App\Models\Kontrak_periode;

use App\Services\Notifier;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;
use App\Models\Kontrak_detail;
use App\Models\Kontrak_map;
use Auth;
use DB;
use Log;

class PengirimanAPI extends Controller
{
    use RestApi;
    protected MediaController $media;
    protected LogController $log;
    protected mixed $global;
    protected mixed $pagination;

    public function __construct()
    {
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
        $this->global = config('customvariabel');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function listPermohonan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $filter = $request->has('filter') ? $request->filter : [];

        DB::beginTransaction();
        try {
            $query = Permohonan::with([
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,id_perusahaan,name',
                'pelanggan.perusahaan',
                'kontrak',
                'kontrak.periode',
                'kontrak.pengiriman',
                'kontrak.pengiriman.permohonan',
                'kontrak.pengiriman.detail',
                'kontrak.jenis_layanan',
                'kontrak.jenis_layanan_parent',
                'pengiriman',
                'invoice',
                'invoice.pengiriman',
                'lhu',
                'lhu.pengiriman',
                'lhu.penyelia_map',
                'lhu.penyelia_map.jobs',
                'lhu.petugas',
                'file_lhu'
            ])->when($search, function ($q, $search) {
                return $q->where('no_kontrak', 'like', "%$search%");
            })
                ->when($filter, function ($q, $filter) {
                    foreach ($filter as $key => $value) {
                        if ($key == 'id_perusahaan') {
                            $q->whereHas('pelanggan.perusahaan', function ($q) use ($value) {
                                $q->where('id_perusahaan', decryptor($value));
                            });
                        } else {
                            $q->where($key, decryptor($value));
                        }
                    }
                })
                ->whereIn('status', [2, 3, 4, 5])
                ->orderBy('status', 'asc')
                ->orderBy('verify_at', 'DESC')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->paginate($limit);

            $arr = $query->toArray();
            DB::commit();
            $this->pagination = Arr::except($arr, 'data');

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }
    }

    public function listAdendum(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $noKontrak = $request->has('no_kontrak') ? $request->no_kontrak : '';
        $filter = $request->has('filter') ? $request->filter : [];

        DB::beginTransaction();
        try {
            $query = Permohonan::with([
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,id_perusahaan,name',
                'pelanggan.perusahaan',
                'kontrak',
                'pengiriman',
                'invoice',
                'invoice.pengiriman',
                'lhu',
                'lhu.pengiriman',
                'permohonan_detail'
            ])
                ->where('tipe_kontrak', 'adendum')
                ->whereIn('status', [2, 3, 4, 5])
                ->when($noKontrak, function ($q, $search) {
                    return $q->where(function ($sub) use ($search) {
                        $sub->whereHas('kontrak', function ($q) use ($search) {
                            $q->where('no_kontrak', $search);
                        });
                    });
                })
                ->when($filter, function ($q, $filter) {
                    foreach ($filter as $key => $value) {
                        if ($key == 'id_perusahaan') {
                            $q->whereHas('pelanggan.perusahaan', function ($q) use ($value) {
                                $q->where('id_perusahaan', decryptor($value));
                            });
                        } else {
                            $q->where($key, decryptor($value));
                        }
                    }
                });

            // Filter secara manual untuk adendum yang status pengiriman dokumennya belum selesai
            $query->where(function ($q) {
                // Pengiriman LHU belum selesai
                $q->whereDoesntHave('lhu.pengiriman', function ($sub) {
                    $sub->whereIn('status', [1, 2, 3]);
                })
                    // ATAU Pengiriman Invoice belum selesai (untuk adendum yang memiliki penambahan pengguna baru)
                    ->orWhere(function ($sub) {
                        $sub->whereHas('permohonan_detail', function ($detail) {
                            $detail->where('type', 'baru');
                        })->whereDoesntHave('invoice.pengiriman', function ($ship) {
                            $ship->whereIn('status', [1, 2, 3]);
                        });
                    });
            });

            $total = $query->count();
            $data = $query->orderBy('created_at', 'DESC')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            // Setup pagination manual
            $pagination = [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit),
                'from' => ($page - 1) * $limit + 1,
                'to' => min($page * $limit, $total)
            ];
            $this->pagination = $pagination;

            DB::commit();

            return response()->json([
                'status' => 'Success',
                'data' => $data,
                'pagination' => $pagination
            ], 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }
    }

    public function listPengiriman(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $filter = $request->has('filter') ? $request->filter : '';
        $idPelanggan = $request->has('idPelanggan') ? decryptor($request->idPelanggan) : '';

        DB::beginTransaction();
        try {
            $query = Pengiriman::with([
                'kontrak',
                'kontrak.pelanggan',
                'kontrak.pelanggan.perusahaan',
                'detail',
                'alamat',
                'permohonan'
            ])
                ->orderBy('recived_at', 'ASC')
                ->orderBy('created_at', 'DESC')
                ->offset(($page - 1) * $limit)
                ->when($filter, function ($q, $filter) {
                    foreach ($filter as $key => $value) {
                        if ($key == 'no_kontrak') {
                            $q->where('id_kontrak', decryptor($value));
                        } else if ($key == 'search') {
                            $q->where('id_pengiriman', $value)->orWhere('no_resi', 'like', "%$value%");
                        } else if ($key == 'status') {
                            $q->where('status', decryptor($value));
                        } else if ($key == 'perusahaan') {
                            $q->whereHas('kontrak.pelanggan.perusahaan', function ($q) use ($value) {
                                $q->where('id_perusahaan', decryptor($value));
                            });
                        }
                    }
                })
                // ->when($status, function($q, $status) {
                //     return $q->whereIn('status', $status);
                // })
                ->when($idPelanggan, function ($q, $idPelanggan) {
                    return $q->where('tujuan', $idPelanggan);
                })
                ->limit($limit)
                ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');

            DB::commit();

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }
    }

    public function getPengirimanById(string $idPengiriman)
    {
        $id = $idPengiriman;

        DB::beginTransaction();
        try {
            $query = Pengiriman::with([
                'ekspedisi',
                'kontrak',
                'kontrak.periode',
                'detail',
                'alamat',
                'tujuan:id,id_perusahaan,name',
                'tujuan.perusahaan:id_perusahaan,nama_perusahaan',
                'permohonan:id_permohonan,periode_pemakaian,jumlah_pengguna,jumlah_kontrol,created_by',
                'permohonan.pelanggan',
                'permohonan.pelanggan.perusahaan',
                'permohonan.invoice',
                'permohonan.lhu',
                'permohonan.dokumen',
                'logs.causer',
            ])->where('id_pengiriman', $id)->first();

            // mengambil media pengiriman
            if ($query->bukti_pengiriman) {
                $query->media_pengiriman = Master_media::whereIn('id', $query->bukti_pengiriman)->get();
            }

            // mengambil media penerima
            if ($query->bukti_penerima) {
                $query->media_penerima = Master_media::whereIn('id', $query->bukti_penerima)->get();
            }

            // mengambil dokumen surpeng
            // cek apakah ada tld di detail
            $isTld = $query->detail->where('jenis', 'tld')->first();
            if ($isTld) {
                $periode = $query->periode == 0 ? 1 : $query->periode;
                $query->dokumen = Permohonan_dokumen::where('id_kontrak', $query->id_kontrak)
                    ->where('periode', $periode)
                    ->where('jenis', 'surpeng')
                    ->first();
            }

            $query->detail->map(function ($item) {
                if ($item->jenis == 'tld' && !empty($item->list_tld)) {
                    $item->data_tld = Master_tld::whereIn('id_tld', $item->list_tld)->get();
                }
                return $item;
            });

            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPermohonan(Request $request)
    {
        $tipe = $request->has('tipe') ? $request->tipe : null;
        $search = $request->has('search') ? $request->search : '';
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;

        $idPermohonan = $request->has('idPermohonan') ? $request->idPermohonan : false;

        DB::beginTransaction();
        try {
            if ($idPermohonan) {
                $query = Permohonan::with([
                    'pelanggan',
                    'pelanggan.perusahaan',
                    'pelanggan.perusahaan.alamat',
                    'invoice',
                    'invoice.usersig',
                    'invoice.diskon',
                    'lhu',
                    'lhu.log',
                    'kontrak',
                    'jenis_layanan',
                    'jenisTld',
                    'layanan_jasa'
                ])->whereHas('lhu.log', function ($q) {
                    $q->whereColumn('log_penyelia.status', 'penyelia.status');
                })
                    ->where('id_permohonan', decryptor($idPermohonan))->first();
            } else {
                $query = Permohonan::with([
                    'layanan_jasa:id_layanan,nama_layanan',
                    'pelanggan',
                    'pelanggan.perusahaan',
                    'jenis_layanan_parent',
                    'kontrak'
                ])->when($search, function ($q, $search) {
                    return $q->where('no_kontrak', 'like', "%$search%");
                })
                    ->whereNotIn('status', ['80', '99'])
                    ->orderBy('created_at', 'DESC')
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->paginate($limit);

                $arr = $query->toArray();
                $this->pagination = Arr::except($arr, 'data');
            }
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function actionPengiriman(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPengiriman = $request->idPengiriman ? $request->idPengiriman : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $idEkspedisi = $request->has('idEkspedisi') ? decryptor($request->idEkspedisi) : null;
            $noResi = $request->has('noResi') ? $request->noResi : false;
            $jenisPengiriman = $request->jenisPengiriman ? $request->jenisPengiriman : false;
            $idKontrak = $request->idKontrak ? decryptor($request->idKontrak) : false;
            $alamat = $request->alamat ? decryptor($request->alamat) : false;
            $tujuan = $request->tujuan ? $request->tujuan : false;
            $periode = $request->has('periode') ? ($request->periode == 0 ? 1 : $request->periode) : false;
            $status = $request->status ? $request->status : false;
            $detail = $request->detail ? $request->detail : false;
            $sendAt = $request->sendAt ? $request->sendAt : false;
            $recivedAt = $request->dateRecived ? $request->dateRecived : false;
            $statusPermohonan = $request->statusPermohonan ? $request->statusPermohonan : false;
            $buktiPengiriman = $request->file('buktiPengiriman') ? $request->file('buktiPengiriman') : array();
            $buktiPenerima = $request->file('buktiPenerima') ? $request->file('buktiPenerima') : array();

            $params = array();
            $request->has('noResi') && $params['no_resi'] = $noResi;
            $request->has('idEkspedisi') && $params['id_ekspedisi'] = $idEkspedisi ? $idEkspedisi : null;
            $idPermohonan && $params['id_permohonan'] = $idPermohonan;
            $jenisPengiriman && $params['jenis_pengiriman'] = $jenisPengiriman;
            $idKontrak && $params['id_kontrak'] = $idKontrak;
            $alamat && $params['alamat'] = $alamat;
            $tujuan && $params['tujuan'] = $tujuan;
            $periode && $params['periode'] = $periode;
            $status && $params['status'] = $status;
            $recivedAt && $params['recived_at'] = $recivedAt;
            $sendAt && $params['send_at'] = Carbon::parse($sendAt)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

            // upload file
            $bukti = array();
            $tmpFileBukti = array();
            if (count($buktiPengiriman) != 0) {
                foreach ($buktiPengiriman as $key => $file) {
                    $fileBukti = $this->media->upload($file, 'pengiriman');
                    array_push($bukti, $fileBukti->getIdMedia());
                    array_push($tmpFileBukti, $fileBukti);
                }

                $params['bukti_pengiriman'] = $bukti;
            }

            $pengiriman = Pengiriman::with(['detail', 'kontrak', 'kontrak.pengguna', 'kontrak.pelanggan'])->where('id_pengiriman', $idPengiriman)->first();
            if (!$pengiriman) {
                $params['created_by'] = Auth::user()->id;
            }

            $query = Pengiriman::updateOrCreate(
                ["id_pengiriman" => $idPengiriman],
                $params
            );

            // update status
            if ($statusPermohonan) {
                Permohonan::where('id_permohonan', $query->id_permohonan)
                    ->update(array('status' => $statusPermohonan));
            }

            if ($status == 3 && isset($pengiriman->kontrak)) {
                // menghapus bukti pengiriman
                foreach ($pengiriman->bukti_pengiriman as $item) {
                    $this->media->destroy($item);
                }
                $pengiriman->update(['bukti_pengiriman' => null]);
            } else if ($status == 1 && isset($pengiriman->kontrak)) {
                // mengirim notifikasi ke pelanggan saat di kirim
                $userQuery = User::where('id_perusahaan', $pengiriman->kontrak->pelanggan->id_perusahaan)->where('status', 1);
                $dataNotif = array(
                    'pesan' => 'Pengiriman dengan no resi <b>' . $noResi . '</b> telah dikirim',
                    'url' => "/permohonan/pengiriman",
                    'event' => 'pengiriman',
                    'event_id' => $pengiriman->pengiriman_hash,
                );
                Notifier::send($userQuery, $dataNotif);
            }

            $result['id_pengiriman'] = $query->pengiriman_hash;

            if ($query->wasRecentlyCreated) {
                $result['status'] = "created";
                $result['msg'] = "Pengiriman berhasil dibuat.";
            } elseif ($query->wasChanged()) {
                $result['status'] = "updated";
                $result['msg'] = "Pengiriman berhasil diedit.";
            } else {
                $result['status'] = "none";
                $result['msg'] = "Nothing has changed.";
            }

            if (count($tmpFileBukti) != 0) {
                foreach ($tmpFileBukti as $key => $file) {
                    $file->store();
                }
            }

            DB::commit();

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function diterima(Request $request)
    {
        DB::beginTransaction();
        try {
            $recivedAt = $request->dateRecived ? $request->dateRecived : false;
            $idPengiriman = $request->idPengiriman ? $request->idPengiriman : false;
            $status = $request->status;
            $buktiPenerima = $request->file('buktiPenerima') ? $request->file('buktiPenerima') : array();
            $statusPermohonan = $request->statusPermohonan ? $request->statusPermohonan : false;

            $params = array();
            $params['recived_at'] = $recivedAt;
            $params['status'] = $status;

            $tmpBuktiPenerima = array();
            $tmpFilePenerima = array();
            if (count($buktiPenerima) != 0) {
                foreach ($buktiPenerima as $key => $file) {
                    $fileBukti = $this->media->upload($file, 'pengiriman');
                    array_push($tmpBuktiPenerima, $fileBukti->getIdMedia());
                    array_push($tmpFilePenerima, $fileBukti);
                }

                $params['bukti_penerima'] = $tmpBuktiPenerima;
            }

            $query = Pengiriman::with([
                'detail',
                'kontrak',
                'permohonan',
                'permohonan.jenis_layanan_parent',
                'permohonan.jenis_layanan',
            ])->where('id_pengiriman', $idPengiriman)->first();
            $query->update($params);

            // jika LHU sudah dikirim
            if ($statusPermohonan) {
                Permohonan::where('id_permohonan', $query->id_permohonan)
                    ->update(array('status' => $statusPermohonan));
            }

            // cek apakah periode sudah complete seperti Invoice, LHU, TLD sesuai dengan periode nya
            info("================ Cek apakah Periode sudah complete ===============");
            info("Kontrak: " . $query->id_kontrak);
            $kontrakPeriode = Kontrak_periode::where('id_kontrak', $query->id_kontrak)->where('periode', $query->periode)->first();
            if (!$kontrakPeriode->selesai) { // jika value nya null
                info("Proses pengecekan di periode : " . $query->periode);
                $cekPeriode = false;
                if ($kontrakPeriode->status == 2) {
                    $cekPeriode = true;
                } else if (isset($query->permohonan) && $query->permohonan->tipe_kontrak != 'adendum') {
                    $cekPeriode = cekPeriodeComplete($query->id_kontrak, $query->periode);
                }
                if ($cekPeriode) {
                    $kontrakPeriode->update(['selesai' => 1]);
                    info("Update Kontrak Periode: selesai = 1");
                } else {
                    info("Periode " . $query->periode . " belum selesai");
                }
            }
            info("================ Selesai ===============");
            $isPeriodOne = $kontrakPeriode->count_tld == 1 || $query->periode == 0;

            // mereset TLD jika di kembalikan
            if ($query->permohonan) {
                $JL = jenislayanan($query->permohonan->jenis_layanan_parent, $query->permohonan->jenis_layanan);
            } else {
                $JL = null;
            }

            if ($kontrakPeriode->status == 2 || $JL === "EvaluasiTanpaKontrak") {
                info("================ Prosess Pengembalian TLD ===============");
                // mengambil TLD dari Kontrak_tld
                $dataTld = Kontrak_detail::where('id_kontrak', $query->id_kontrak)->get();

                if ($dataTld) {
                    foreach ($dataTld as $item) {
                        $idTld = $isPeriodOne ? $item->tld_1 : $item->tld_2;
                        info("Prosess update TLD " . $idTld);
                        Master_tld::where('id_tld', $idTld)->update(['status' => 0, 'digunakan' => null]);
                    }
                }

                info("================ Selesai Pengembalian TLD ===============");
            }

            // Mengganti status di kontrak_tld menjadi 2 artinya sudah diterima oleh pelanggan, khusus pengiriman TLD
            $listTld = $query->detail->where('jenis', 'tld')->flatten()->toArray();
            if (!empty($listTld)) {
                // mengganti status di kontrak_tld menjadi 2 artinya sudah diterima oleh pelanggan
                $periodeNow = $query->periode == 0 ? 1 : $query->periode;

                $updateData = $isPeriodOne
                    ? ['status_tld_1' => 2, 'periode_tld_1' => $periodeNow]
                    : ['status_tld_2' => 2, 'periode_tld_2' => $periodeNow];

                Kontrak_detail::where('id_kontrak', $query->id_kontrak)->where('status', 1)->update($updateData);

                // menambahkan ke kontrak_map untuk mendaftarkan tld dan pengguna yang di pakai
                $kontrakDetail = Kontrak_detail::where('id_kontrak', $query->id_kontrak)->where('status', 1)->get();
                foreach ($kontrakDetail as $item) {
                    $idTld = $isPeriodOne ? $item->tld_1 : $item->tld_2;
                    Kontrak_map::create([
                        'id_kontrak' => $item->id_kontrak,
                        'id_kontrak_detail' => $item->id,
                        'id_tld' => $isPeriodOne ? $item->tld_1 : $item->tld_2,
                        'id_pengguna_divisi' => $item->id_pengguna_divisi,
                        'jenis' => $item->jenis,
                        'periode' => $periodeNow,
                        'created_by' => Auth::user()->id
                    ]);
                }
            }

            // kondisi ketika semua periode complete, dan akan mengganti status di kontrak nya menjadi 2
            // Mengambil data kontrak
            $kontrak = Kontrak::with(['jenis_layanan', 'jenis_layanan_parent', 'tld_aktif'])->where('id_kontrak', $query->id_kontrak)->first();
            $isComplete = Kontrak_periode::where('id_kontrak', $query->id_kontrak)->where('status', 1)->whereNull('selesai')->exists() ? false : true;
            $layanan = jenislayanan($kontrak->jenis_layanan_parent, $kontrak->jenis_layanan);
            $isSewa = in_array($layanan, $this->global['arr_sewa']);
            if ($isComplete) {
                info("================ Prosess Ketika kontrak complete ===============");
                $isAktifTld = $kontrak->tld_aktif->count() > 0 ? true : false;
                if (!$isAktifTld) {
                    info("Semua TLD sudah tidak ada yang aktif");
                    $kontrak->update(['status' => 2]);
                    if ($isSewa) {
                        // Master_tld::where('digunakan', $kontrak->no_kontrak)->update(['digunakan' => null, 'status' => 0]);
                        $tldIds = Kontrak_detail::where('id_kontrak', $query->id_kontrak)
                            ->where('status', 1)
                            ->get(['tld_1', 'tld_2'])
                            ->flatMap(static fn($detail) => [$detail->tld_1, $detail->tld_2])
                            ->filter()
                            ->unique();

                        if ($tldIds->isNotEmpty()) {
                            Master_tld::whereIn('id_tld', $tldIds)->update(['digunakan' => null, 'status' => 0]);
                        }
                    }

                    info("Reset Pengguna TLD");
                    $penggunaInKontrak = Kontrak_detail::select('id_pengguna_divisi')->where('id_kontrak', $query->id_kontrak)
                        ->where('jenis', 'pengguna')
                        ->get();
                    foreach ($penggunaInKontrak as $item) {
                        Master_pengguna::where('id_pengguna', $item->id_pengguna_divisi)->update(['status' => 1]);
                    }
                } else {
                    info("Masih ada TLD yang aktif");
                }
                info("================ Selesai Ketika kontrak complete ===============");
            }

            if (count($tmpFilePenerima) != 0) {
                foreach ($tmpFilePenerima as $key => $file) {
                    $file->store();
                }
            }

            DB::commit();

            $result = array(
                'id_pengiriman' => $query->pengiriman_hash,
                'status' => 'Success',
                'msg' => 'Pengiriman berhasil diterima'
            );

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Membuat pengiriman baru
     *
     * @param Request $request
     * @return array
     */
    public function buatPengiriman(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPengiriman = $request->idPengiriman;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : null;
            $idKontrak = $request->idKontrak ? decryptor($request->idKontrak) : null;
            $alamat = $request->alamat ? decryptor($request->alamat) : null;

            $params = array_filter([
                'id_pengiriman' => $idPengiriman,
                'id_permohonan' => $idPermohonan,
                'alamat'        => $alamat,
                'tujuan'        => $request->tujuan,
                'status'        => $request->status,
                'periode'       => $request->has('periode') ? $request->periode : null,
                'id_kontrak'    => $idKontrak,
                'created_by'    => Auth::id(),
            ], function ($v) {
                return !is_null($v);
            });

            $pengiriman = Pengiriman::create($params);

            if ($request->detail) {
                $details = json_decode($request->detail);

                // Remove existing details
                Pengiriman_detail::where('id_pengiriman', $idPengiriman)->get()->each->delete();

                foreach ($details as $value) {
                    $this->processDetailItem($value, $idPengiriman, $idKontrak);
                }
            }

            DB::commit();

            $result = array(
                'id_pengiriman' => $pengiriman->pengiriman_hash,
                'status' => 'Success',
                'msg' => 'Pengiriman berhasil dibuat'
            );

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroy(string $idPengiriman)
    {
        $id = $idPengiriman;

        DB::beginTransaction();
        try {
            $fileBukti = Pengiriman::select(
                'bukti_pengiriman',
                'bukti_penerima',
                'id_kontrak',
                'periode'
            )->where('id_pengiriman', $id)->first();
            $detailTld = Pengiriman_detail::where('id_pengiriman', $id)->where('jenis', 'tld')->first();

            if ($detailTld) {
                Master_tld::whereIn('id_tld', $detailTld->list_tld)->update(['status' => 0]);

                $kontrakPeriode = Kontrak_periode::where('id_kontrak', $fileBukti->id_kontrak)
                    ->where('periode', $detailTld->periode)
                    ->first();

                $update = array();
                if ($kontrakPeriode->count_tld == 1) {
                    $update['status_tld_1'] = 5;
                } else if ($kontrakPeriode->count_tld == 2) {
                    $update['status_tld_2'] = 5;
                }
                Kontrak_detail::where('id_kontrak', $fileBukti->id_kontrak)->where('status', 1)->update($update);

                $kontrakPeriode->update([
                    'nomer_surpeng' => null,
                    'created_surpeng_at' => null
                ]);
            }

            $delete = Pengiriman::where('id_pengiriman', $id)->get()->each->delete();

            // update id_pengiriman di invoice, lhu, dan tld
            Keuangan::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);
            Penyelia::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);
            Permohonan::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);

            DB::commit();

            if ($fileBukti && $delete) {
                $buktiPengiriman = $fileBukti->bukti_pengiriman;
                $buktiPenerima = $fileBukti->bukti_penerima;

                if ($buktiPengiriman) {
                    foreach ($buktiPengiriman as $key => $value) {
                        $this->media->destroy($value);
                    }
                }

                if ($buktiPenerima) {
                    foreach ($buktiPenerima as $key => $value) {
                        $this->media->destroy($value);
                    }
                }

                return $this->output(array('msg' => 'Data berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Data gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Process detail item from request
     *
     * @param object $value Detail item from request
     * @param int $idPengiriman ID of pengiriman
     * @param int $idKontrak ID of kontrak
     *
     * @return void
     */
    private function processDetailItem($value, $idPengiriman, $idKontrak)
    {
        $valPeriode = $value->periode ?? null;
        $periodeTld = ($valPeriode === 0 || $valPeriode === '0') ? 1 : $valPeriode;

        $params = [
            'id_pengiriman' => $idPengiriman,
            'jenis' => $value->jenis,
            'periode' => $valPeriode,
            'list_tld' => []
        ];

        if (!empty($value->listTld)) {
            $kPeriode = Kontrak_periode::where('id_kontrak', $idKontrak)
                ->where('periode', $periodeTld)->first();

            foreach ($value->listTld as $val) {
                $idKontrakDetail = (int) decryptor($val->id);
                $idTld = (int) decryptor($val->tld);

                $kontrakTld = Kontrak_detail::with('kontrak:id_kontrak,no_kontrak')
                    ->find($idKontrakDetail);

                if ($kontrakTld && $kPeriode) {
                    $isPeriodOne = $kPeriode->count_tld == 1 || $valPeriode == 0;

                    $updateData = [];
                    if ($idTld) {
                        $updateData = $isPeriodOne
                            ? ['tld_1' => $idTld, 'status_tld_1' => 1, 'periode_tld_1' => $periodeTld]
                            : ['tld_2' => $idTld, 'status_tld_2' => 1, 'periode_tld_2' => $periodeTld];
                    }

                    $updateData['status'] = 1;

                    if ($kontrakTld->type == 'ganti') {
                        Kontrak_detail::where('id_kontrak', $idKontrak)
                            ->where('id_pengguna_divisi', $kontrakTld->pengguna_lama)
                            ->update(['status' => 99]);
                    }

                    $kontrakTld->update($updateData);

                    Master_tld::where('id_tld', $idTld)->update([
                        'status' => 1,
                        'digunakan' => $kontrakTld->kontrak->no_kontrak
                    ]);
                }
                $params['list_tld'][] = $idTld;
            }
        }

        if ($value->jenis == 'tld') {
            $params['periode'] = $periodeTld;
            $this->handleSuratPengantar($idKontrak, $periodeTld);
        }

        Pengiriman_detail::create($params);

        $this->updateEntityReference($value, $idPengiriman);
    }

    /**
     * Handle Surat Pengantar, generate a new document if not exists
     *
     * @param int $idKontrak ID of kontrak
     * @param int $periode Periode of kontrak
     *
     * @return void
     */
    private function handleSuratPengantar($idKontrak, $periode)
    {
        $kPeriode = Kontrak_periode::where('id_kontrak', $idKontrak)
            ->where('periode', $periode)->first();

        if ($kPeriode && $kPeriode->nomer_surpeng == null) {
            $noSurpeng = generateNoDokumen('surpeng');
            $kPeriode->update(['nomer_surpeng' => $noSurpeng, 'created_surpeng_at' => Carbon::now()]);

            $dokumen = Permohonan_dokumen::firstOrNew([
                'id_kontrak' => $idKontrak,
                'periode' => $periode,
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
        }
    }

    /**
     * @param int $idPengiriman
     * @return void
     */
    private function updateEntityReference($value, $idPengiriman)
    {
        $id = isset($value->id) ? decryptor($value->id) : null;
        if (!$id) return;

        if ($value->jenis == 'invoice') {
            Keuangan::where('id_keuangan', $id)->update(['id_pengiriman' => $idPengiriman]);
        } elseif ($value->jenis == 'lhu') {
            $penyelia = Penyelia::with('permohonan.kontrak')->find($id);
            if ($penyelia) {
                $penyelia->update(['id_pengiriman' => $idPengiriman]);

                $kontrak = $penyelia->permohonan->kontrak ?? null;
                $skipPermohonanUpdate = $kontrak && $kontrak->jenis_layanan_2 == '3' && $kontrak->is_have_tld == 1;

                if (!$skipPermohonanUpdate) {
                    Permohonan::where('id_permohonan', $penyelia->id_permohonan)
                        ->update(['id_pengiriman' => $idPengiriman]);
                }
            }
        } elseif ($value->jenis == 'tld') {
            Permohonan::where('id_permohonan', $id)->update(['id_pengiriman' => $idPengiriman]);
        }
    }
}
