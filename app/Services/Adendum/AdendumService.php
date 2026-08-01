<?php

namespace App\Services\Adendum;

use App\Http\Controllers\API\KeuanganAPI;
use App\Http\Controllers\API\PenyeliaAPI;
use App\Http\Controllers\NotifController;
use App\Models\Documents;
use App\Models\Keuangan;
use App\Models\Keuangan_diskon;
use App\Models\Kontrak;
use App\Models\Kontrak_detail;
use App\Models\Kontrak_map;
use App\Models\Kontrak_periode;
use App\Models\Log_activity;
use App\Models\Log_keuangan;
use App\Models\Log_pengiriman;
use App\Models\Log_permohonan;
use App\Models\Log_penyelia;
use App\Models\Log_proses;
use App\Models\Master_pengguna;
use App\Models\Master_tld;
use App\Models\Pengiriman;
use App\Models\Pengiriman_detail;
use App\Models\Permohonan;
use App\Models\Permohonan_detail;
use App\Models\Permohonan_dokumen;
use App\Models\Permohonan_pengguna;
use App\Models\Permohonan_tandaterima;
use App\Models\Permohonan_tld;
use App\Models\Penyelia;
use App\Models\Penyelia_map;
use App\Models\Penyelia_petugas;
use App\Services\Adendum\Validators\AdendumValidatorPipeline;
use App\Services\Keuangan\FinancialCalculatorService;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Log;

class AdendumService
{
    protected AdendumValidatorPipeline $pipeline;
    protected KeuanganAPI $keuangan;
    protected PenyeliaAPI $penyelia;
    protected NotifController $notif;
    protected FinancialCalculatorService $calculator;

    public function __construct()
    {
        $this->pipeline = new AdendumValidatorPipeline();
        $this->keuangan = resolve(KeuanganAPI::class);
        $this->penyelia = resolve(PenyeliaAPI::class);
        $this->notif    = resolve(NotifController::class);
        $this->calculator = resolve(FinancialCalculatorService::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // STORE — Simpan adendum baru
    // ═══════════════════════════════════════════════════════════════

    /**
     * Buat & simpan adendum baru.
     *
     * @param  array $payload  Data dari request (sudah di-parse, ID sudah di-decrypt)
     * @return array ['status' => true/false, 'msg' => '...']
     */
    public function store(array $payload): array
    {
        DB::beginTransaction();
        try {
            // ── 1. Ambil data kontrak & periode ──────────────────────
            $dataKontrak = Kontrak::find($payload['id_kontrak']);
            $dataPeriode = Kontrak_periode::find($payload['id_periode']);

            if (!$dataKontrak || !$dataPeriode) {
                return ['status' => false, 'msg' => 'Kontrak atau Periode tidak ditemukan.', 'code' => 422];
            }

            $periodeActive = $dataKontrak->periode_active;
            if (!$periodeActive) {
                return ['status' => false, 'msg' => 'Tidak ada periode aktif pada kontrak ini.', 'code' => 422];
            }

            // ── 2. Bangun context & jalankan pipeline validasi ────────
            $context = new AdendumContext(
                kontrak: $dataKontrak,
                dataPeriode: $dataPeriode,
                periodeActive: $periodeActive,
                pengguna: $payload['pengguna'],
                kontrol: $payload['kontrol'],
                isZeroCek: $payload['is_zerocek'],
                isHaveTld: $payload['is_havetld'],
                bulanMulai: $payload['bulan_mulai'],
                idKontrak: $payload['id_kontrak'],
                idPeriode: $payload['id_periode'],
            );

            $error = $this->pipeline->run($context);
            if ($error !== null) {
                return ['status' => false, 'msg' => $error, 'code' => 422];
            }

            // ── 3. Simpan record Permohonan ───────────────────────────
            $data = [
                'id_layanan'        => $dataKontrak->id_layanan,
                'jenis_layanan_1'   => $dataKontrak->jenis_layanan_1,
                'jenis_layanan_2'   => $dataKontrak->jenis_layanan_2,
                'tipe_kontrak'      => 'adendum',
                'id_kontrak'        => $payload['id_kontrak'],
                'periode'           => $dataPeriode->periode,
                'bulan_mulai'       => $payload['bulan_mulai'],
                'jenis_tld'         => $dataKontrak->jenis_tld,
                'jumlah_pengguna'   => $context->jumPenggunaBaru,
                'jumlah_kontrol'    => $context->jumKontrolBaru,
                'total_harga'       => $this->calculator->calculateAdendum(
                                            $dataKontrak, 
                                            $context->jumPenggunaBaru + $context->jumKontrolBaru, 
                                            $payload['bulan_mulai'] ?? 1, 
                                            $dataPeriode->periode
                                        ),
                'harga_layanan'     => $dataKontrak->harga_layanan,
                'note'              => $payload['note'] ?? null,
                'is_zerocek'        => $payload['is_zerocek'],
                'is_have_tld'       => $payload['is_havetld'],
                'status'            => 1,
                'created_by'        => Auth::id(),
            ];

            if ($context->isPeriodeAktif()) {
                $data['is_periode_berjalan'] = 1;
            }

            $permohonan = Permohonan::create($data);

            // ── 4. Simpan detail TLD & buat dokumen surat adendum ─────
            $this->saveTldAdendum($permohonan->id_permohonan, $context->pengguna, $context->kontrol);

            DB::commit();
            return ['status' => true, 'msg' => 'Adendum berhasil disimpan'];
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('[AdendumService::store] ' . $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return ['status' => false, 'msg' => $ex->getMessage(), 'code' => 500];
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // VERIFY — Verifikasi adendum oleh staff
    // ═══════════════════════════════════════════════════════════════

    /**
     * Verifikasi adendum oleh staff front desk.
     *
     * @param  array $payload  ['id_permohonan', 'ttd', 'ttd_by', 'list_tld', 'tanggal_selesai']
     * @return array ['status' => true/false, 'msg' => '...']
     */
    public function verify(array $payload): array
    {
        DB::beginTransaction();
        try {
            $idPermohonan  = $payload['id_permohonan'];
            $ttd           = $payload['ttd'] ?? null;
            $ttdBy         = $payload['ttd_by'] ?? null;
            $listTld       = $payload['list_tld'] ?? [];
            $tglSelesai    = $payload['tanggal_selesai'] ?? null;

            // ── 1. Update TLD dari request ────────────────────────────
            $dataPermohonan = Permohonan::with(['kontrak:id_kontrak,no_kontrak'])
                ->where('id_permohonan', $idPermohonan)
                ->first();

            if (!$dataPermohonan) {
                return ['status' => false, 'msg' => 'Permohonan tidak ditemukan', 'code' => 404];
            }

            foreach ($listTld as $item) {
                $idTld = (int) decryptor($item->tld);
                $id    = decryptor($item->id);

                if ($idTld) {
                    Master_tld::find($idTld)?->update([
                        'status'    => 1,
                        'digunakan' => $dataPermohonan->kontrak->no_kontrak,
                    ]);
                }

                Permohonan_detail::find($id)?->update([
                    'id_tld' => $idTld ?: null,
                    'status' => $dataPermohonan->is_have_tld ? 3 : 5,
                ]);
            }

            // ── 2. Load ulang dengan relasi lengkap ───────────────────
            $dataPermohonan = Permohonan::with([
                'permohonan_detail',
                'permohonan_detail.entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi'],
                    ]);
                },
                'permohonan_detail.tld',
                'kontrak:id_kontrak,no_kontrak',
            ])->where('id_permohonan', $idPermohonan)->first();

            // ── 3. Update kontrak_detail & pengguna ───────────────────
            $kontrakPeriode = Kontrak_periode::where('id_kontrak', $dataPermohonan->id_kontrak)
                ->where('periode', $dataPermohonan->periode)
                ->first();

            $isPeriodOne = $kontrakPeriode ? $kontrakPeriode->count_tld == 1 : false;

            foreach ($dataPermohonan->permohonan_detail as $detail) {
                $dataDetail = [
                    'status'            => 2,
                    'id_pengguna_divisi' => $detail->id_pengguna_divisi,
                    'id_divisi_selected' => $detail->id_divisi_selected,
                    'kode_lencana_selected' => $detail->kode_lencana_selected,
                    'jenis'             => $detail->jenis,
                    'type'              => $detail->type,
                    'id_kontrak'        => $dataPermohonan->id_kontrak,
                    'created_by'        => Auth::id(),
                    'periode'           => $dataPermohonan->periode,
                ];

                if ($detail->type === 'ganti') {
                    $kontrakDetail = Kontrak_detail::where('id_kontrak', $dataPermohonan->id_kontrak)
                        ->where('id_pengguna_divisi', $detail->pengguna_lama)
                        ->where('status', 1)
                        ->first();

                    [$tld_1, $tld_2, $st1, $st2, $p1, $p2] = $this->resolveTldForGanti(
                        $isPeriodOne,
                        $detail,
                        $dataPermohonan->periode
                    );

                    $dataDetail['tld_1']        = $kontrakDetail?->tld_1        ?? $tld_1;
                    $dataDetail['status_tld_1']  = $kontrakDetail?->status_tld_1 ?? $st1;
                    $dataDetail['periode_tld_1'] = $kontrakDetail?->periode_tld_1 ?? $p1;
                    $dataDetail['tld_2']        = $kontrakDetail?->tld_2        ?? $tld_2;
                    $dataDetail['status_tld_2']  = $kontrakDetail?->status_tld_2 ?? $st2;
                    $dataDetail['periode_tld_2'] = $kontrakDetail?->periode_tld_2 ?? $p2;
                    $dataDetail['pengguna_lama'] = $detail->pengguna_lama;
                } elseif ($detail->type === 'baru') {
                    if ($isPeriodOne) {
                        $dataDetail['tld_1']        = $detail->id_tld;
                        $dataDetail['status_tld_1']  = $dataPermohonan->is_have_tld ? 1 : 5;
                        $dataDetail['periode_tld_1'] = $dataPermohonan->periode;
                    } else {
                        $dataDetail['tld_2']        = $detail->id_tld;
                        $dataDetail['status_tld_2']  = $dataPermohonan->is_have_tld ? 1 : 5;
                        $dataDetail['periode_tld_2'] = $dataPermohonan->periode;
                    }
                }

                Kontrak_detail::create($dataDetail);
            }

            // ── 4. Update status permohonan & TTD ─────────────────────
            $dataPermohonan->update([
                'ttd'       => $ttd,
                'ttd_by'    => $ttdBy,
                'verify_at' => now()->toDateTimeString(),
                'status'    => 2,
                'locked_by' => null,
                'locked_at' => null,
            ]);

            // Simpan TTD di dokumen tandaterima
            Permohonan_dokumen::where('id_permohonan', $idPermohonan)
                ->where('jenis', 'tandaterima')
                ->where('status', 1)
                ->first()
                ?->update(['ttd' => $ttd, 'ttd_by' => $ttdBy]);

            // ── 5. Buat invoice jika total harga > 0 ─────────────────
            if ($dataPermohonan->total_harga > 0) {
                $invoiceResp = $this->keuangan->keuanganAction(new Request([
                    'idPermohonan' => $dataPermohonan->permohonan_hash,
                    'status'       => 1,
                ]));

                if ($invoiceResp->getStatusCode() !== 200) {
                    $content = $invoiceResp->getData();
                    throw new \Exception($content->msg ?? 'Gagal membuat invoice');
                }
            }

            // ── 6. Buat penyelia ──────────────────────────────────────
            $penyeliaResp = $this->penyelia->actionPenyelia(new Request([
                'idPermohonan' => $dataPermohonan->permohonan_hash,
                'status'       => 1,
                'endDate'      => $tglSelesai,
                'startDate'    => now()->toDateTimeString(),
            ]));

            if ($penyeliaResp->getStatusCode() !== 200) {
                $content = $penyeliaResp->getData();
                throw new \Exception($content->msg ?? 'Gagal membuat penyelia');
            }

            DB::commit();
            return ['status' => true, 'msg' => 'Permohonan berhasil diverifikasi'];
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('[AdendumService::verify] ' . $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return ['status' => false, 'msg' => $ex->getMessage(), 'code' => 500];
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // DESTROY — Hapus adendum beserta data terkait
    // ═══════════════════════════════════════════════════════════════

    /**
     * Hapus adendum beserta seluruh data turunannya.
     * Hanya Super Admin atau Developer yang boleh memanggil ini.
     *
     * @param  int $id  ID permohonan (sudah di-decrypt)
     * @return array ['status' => true/false, 'msg' => '...']
     */
    public function destroy(int $id): array
    {
        DB::beginTransaction();
        try {
            $permohonan = Permohonan::where('id_permohonan', $id)->first();

            if (!$permohonan) {
                return ['status' => false, 'msg' => 'Data permohonan tidak ditemukan', 'code' => 404];
            }

            if ($permohonan->tipe_kontrak !== 'adendum') {
                return ['status' => false, 'msg' => 'Data yang ingin dihapus bukan merupakan adendum', 'code' => 400];
            }

            // 1. Bersihkan Penyelia
            $penyeliaIds = Penyelia::where('id_permohonan', $id)->pluck('id_penyelia')->toArray();
            if (!empty($penyeliaIds)) {
                Penyelia_petugas::whereIn('id_penyelia', $penyeliaIds)->delete();
                Penyelia_map::whereIn('id_penyelia', $penyeliaIds)->delete();
                Log_penyelia::whereIn('id_penyelia', $penyeliaIds)->delete();
                Penyelia::whereIn('id_penyelia', $penyeliaIds)->delete();
            }

            // 2. Bersihkan Keuangan
            $keuanganIds = Keuangan::where('id_permohonan', $id)->pluck('id_keuangan')->toArray();
            if (!empty($keuanganIds)) {
                Keuangan_diskon::whereIn('id_keuangan', $keuanganIds)->delete();
                Log_keuangan::whereIn('id_keuangan', $keuanganIds)->delete();
                Keuangan::whereIn('id_keuangan', $keuanganIds)->delete();
            }

            // 3. Bersihkan Pengiriman
            $pengirimanIds = Pengiriman::where('id_permohonan', $id)->pluck('id_pengiriman')->toArray();
            if (!empty($pengirimanIds)) {
                Pengiriman_detail::whereIn('id_pengiriman', $pengirimanIds)->delete();
                Log_pengiriman::whereIn('id_pengiriman', $pengirimanIds)->delete();
                Pengiriman::whereIn('id_pengiriman', $pengirimanIds)->delete();
            }

            // 4. Bersihkan Log
            Log_proses::where('subject_type', 'App\Models\Permohonan')->where('subject_id', $id)->delete();
            Log_activity::where('subject_type', 'App\Models\Permohonan')->where('subject_id', $id)->delete();
            Log_permohonan::where('id_permohonan', $id)->delete();

            // 5. Bersihkan relasi lainnya
            Permohonan_tandaterima::where('id_permohonan', $id)->delete();
            Permohonan_tld::where('id_permohonan', $id)->delete();

            // 6. Reset status TLD & pengguna
            $dataTld = Permohonan_detail::where('id_permohonan', $id)->get();
            foreach ($dataTld as $item) {
                if ($item->jenis === 'pengguna') {
                    Master_pengguna::find($item->id_pengguna_divisi)?->update(['status' => 1]);
                }
                if ($item->id_tld) {
                    Master_tld::find($item->id_tld)?->update(['status' => 0, 'digunakan' => null]);

                    Kontrak_detail::where('status', 2)->where('id_kontrak', $permohonan->id_kontrak)
                        ->where('tld_1', $item->id_tld)->orWhere('tld_2', $item->id_tld)->get()->each->delete();
                }
            }

            // 7. Hapus detail & dokumen adendum
            Permohonan_pengguna::where('id_permohonan', $id)->get()->each->delete();
            Permohonan_detail::where('id_permohonan', $id)->get()->each->delete();
            Permohonan_dokumen::where('id_permohonan', $id)->where('jenis', 'adendum')->get()->each->delete();

            // 8. Hapus record permohonan
            $permohonan->delete();

            // 9. Hapus notifikasi
            $this->notif->deleteNotification(new Request([
                'id_event' => $id,
                'event'    => 'Permohonan',
            ]));

            DB::commit();
            return ['status' => true, 'msg' => 'Adendum berhasil dihapus!'];
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('[AdendumService::destroy] ' . $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return ['status' => false, 'msg' => $ex->getMessage(), 'code' => 500];
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // LIST — Daftar adendum untuk halaman pengiriman staff
    // ═══════════════════════════════════════════════════════════════

    /**
     * Ambil daftar adendum yang masih perlu diproses pengirimannya.
     *
     * @param  array $filters ['limit', 'page', 'no_kontrak', 'filter']
     * @return array ['data' => [...], 'pagination' => [...]]
     */
    public function list(array $filters): array
    {
        $limit    = $filters['limit']     ?? 10;
        $page     = $filters['page']      ?? 1;
        $noKontrak = $filters['no_kontrak'] ?? '';
        $filter   = $filters['filter']    ?? [];

        $query = Permohonan::with([
            'layanan_jasa:id_layanan,nama_layanan',
            'jenisTld:id_jenisTld,name',
            'jenis_layanan:id_jenisLayanan,name,parent',
            'jenis_layanan_parent',
            'pelanggan:id,id_perusahaan,name',
            'pelanggan.perusahaan',
            'kontrak',
            'kontrak.kontrak_detail:id,id_kontrak,id_pengguna_divisi,tld_1,status_tld_1,periode_tld_1,tld_2,status_tld_2,periode_tld_2',
            'kontrak.kontrak_detail.tld_awal:id_tld,no_seri_tld,jenis',
            'kontrak.kontrak_detail.tld_second:id_tld,no_seri_tld,jenis',
            'pengiriman',
            'pengiriman_tld',
            'invoice',
            'invoice.pengiriman',
            'lhu',
            'lhu.pengiriman',
            'permohonan_detail',
        ])
            ->where('tipe_kontrak', 'adendum')
            ->whereIn('status', [2, 3, 4])
            ->when($noKontrak, fn($q) => $q->whereHas('kontrak', fn($q2) => $q2->where('no_kontrak', $noKontrak)))
            ->when($filter, function ($q) use ($filter) {
                foreach ($filter as $key => $value) {
                    if ($key === 'id_perusahaan') {
                        $q->whereHas('pelanggan.perusahaan', fn($q2) => $q2->where('id_perusahaan', decryptor($value)));
                    } else {
                        $q->where($key, decryptor($value));
                    }
                }
            });

        // Filter adendum yang masih perlu diproses pengirimannya
        $query->where(function ($q) {
            $q->whereDoesntHave('lhu.pengiriman', fn($s) => $s->whereIn('status', [1, 2, 3]))
                ->orWhere(function ($sub) {
                    $sub->whereHas('permohonan_detail', fn($d) => $d->where('type', 'baru'))
                        ->whereDoesntHave('invoice.pengiriman', fn($s) => $s->whereIn('status', [1, 2, 3]));
                })
                ->orWhere(function ($sub) {
                    $sub->whereHas('permohonan_detail', fn($d) => $d->where('type', 'baru'))
                        ->whereRaw('permohonan.periode = (SELECT MIN(periode) FROM kontrak_periode WHERE kontrak_periode.id_kontrak = permohonan.id_kontrak AND kontrak_periode.selesai IS NULL AND kontrak_periode.periode != 0)')
                        ->whereDoesntHave('pengiriman_tld', fn($s) => $s->whereIn('status', [1, 2, 3]))
                        ->whereExists(function ($existsQ) {
                            $existsQ->select(DB::raw(1))
                                ->from('pengiriman')
                                ->join('pengiriman_detail', 'pengiriman.id_pengiriman', '=', 'pengiriman_detail.id_pengiriman')
                                ->whereColumn('pengiriman.id_kontrak', 'permohonan.id_kontrak')
                                ->whereColumn('pengiriman.periode', 'permohonan.periode')
                                ->where('pengiriman_detail.jenis', 'tld')
                                ->whereIn('pengiriman.status', [1, 2, 3])
                                ->where(function ($q) {
                                    $q->whereNull('pengiriman.id_permohonan')
                                        ->orWhereExists(function ($sq) {
                                            $sq->select(DB::raw(1))
                                                ->from('permohonan as p2')
                                                ->whereColumn('p2.id_permohonan', 'pengiriman.id_permohonan')
                                                ->where('p2.tipe_kontrak', '!=', 'adendum');
                                        });
                                });
                        });
                });
        });

        $total = $query->count();
        $data  = $query->orderBy('created_at', 'DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return [
            'data'       => $data,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $limit,
                'current_page' => $page,
                'last_page'    => $total > 0 ? (int) ceil($total / $limit) : 1,
                'from'         => ($page - 1) * $limit + 1,
                'to'           => min($page * $limit, $total),
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // SYNC — Sinkronisasi kontrak adendum (dipindah dari LabHelper)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Sinkronisasi status pengguna & TLD yang sudah pernah diganti via adendum.
     * Dipanggil saat membuka halaman Evaluasi Kontrak.
     *
     * Sebelumnya ada di LabHelper::setKontrakAdendum().
     */
    public function syncKontrakAdendum(int $idKontrak, int $periode): void
    {
        $kontrak = Kontrak::find($idKontrak);
        if (!$kontrak || !$kontrak->periode_active) {
            return;
        }

        if ($periode < $kontrak->periode_active->periode) {
            return;
        }

        $result = Kontrak_detail::where('id_kontrak', $idKontrak)
            ->where('status', 2)
            ->where('periode', '<=', $kontrak->periode_active->periode)
            ->get();

        foreach ($result as $value) {
            if ($value->pengguna_lama) {
                $change = Kontrak_detail::where('id_kontrak', $idKontrak)
                    ->where('status', 1)
                    ->where('id_pengguna_divisi', $value->pengguna_lama)
                    ->first();

                if ($change) {
                    // Sync status TLD
                    $value->status_tld_1 = $change->status_tld_1;
                    $value->status_tld_2 = $change->status_tld_2;

                    // Nonaktifkan pengguna lama yang sudah diganti
                    Master_pengguna::where('id_pengguna', $change->id_pengguna_divisi)
                        ->update(['status' => 1]);

                    // Soft delete map TLD pengguna lama dari periode aktif ini dan seterusnya
                    Kontrak_map::where('id_kontrak', $idKontrak)
                        ->where('id_pengguna_divisi', $change->id_pengguna_divisi)
                        ->where('periode', '>=', $kontrak->periode_active->periode)
                        ->delete();

                    $change->delete();
                }
            }

            $value->status = 1;
            $value->save();
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Simpan detail TLD pengguna & kontrol, lalu buat dokumen surat adendum.
     */
    private function saveTldAdendum(int $idPermohonan, array $tldPengguna, array $tldKontrol): void
    {
        // Simpan pengguna
        foreach ($tldPengguna as $value) {
            $idPengguna     = null;
            $idPenggunaLama = null;
            $idTld          = null;

            if ($value->status === 'baru') {
                $idPengguna = (int) decryptor($value->pengguna);
                $idTld      = isset($value->tld) ? (int) decryptor($value->tld) : null;
            } elseif ($value->status === 'ganti') {
                $permohonan     = Permohonan::find($idPermohonan);
                $idPengguna     = (int) decryptor($value->pengguna_baru);
                $idPenggunaLama = (int) decryptor($value->pengguna);

                $kontrakDetail  = Kontrak_detail::where('id_kontrak', $permohonan->id_kontrak)
                    ->where('id_pengguna_divisi', $idPenggunaLama)
                    ->first();
                $kontrakPeriode = Kontrak_periode::where('id_kontrak', $permohonan->id_kontrak)
                    ->where('periode', $permohonan->periode)
                    ->first();

                $idTld = $kontrakPeriode?->count_tld == 1 ? $kontrakDetail?->tld_1 : $kontrakDetail?->tld_2;
            }

            $idDivisiSelected = isset($value->id_divisi_selected) && $value->id_divisi_selected ? decryptor($value->id_divisi_selected) : null;
            if (!$idDivisiSelected && isset($value->id_divisi_selected) && is_numeric($value->id_divisi_selected)) {
                $idDivisiSelected = (int) $value->id_divisi_selected;
            }
            $kodeLencanaSelected = $value->kode_lencana_selected ?? null;

            $permohonanForStatus = Permohonan::find($idPermohonan);
            Permohonan_detail::create([
                'id_permohonan'      => $idPermohonan,
                'id_pengguna_divisi' => $idPengguna,
                'id_divisi_selected' => $idDivisiSelected,
                'kode_lencana_selected' => $kodeLencanaSelected,
                'jenis'              => 'pengguna',
                'status'             => $this->resolveStatusDetail($permohonanForStatus, $value->status),
                'type'               => $value->status,
                'pengguna_lama'      => $idPenggunaLama,
                'created_by'         => Auth::id(),
                'id_tld'             => $idTld,
            ]);

            Master_tld::find($idTld)?->update(['status' => 1]);
        }

        // Simpan kontrol
        $permohonanForStatus = $permohonanForStatus ?? Permohonan::find($idPermohonan);
        foreach ($tldKontrol as $value) {
            $idTld = isset($value->tld) ? (int) decryptor($value->tld) : null;

            // Kontrol: status mengikuti apakah TLD dari Pelanggan atau LAB
            $statusKontrol = $permohonanForStatus?->is_have_tld ? 3 : 5;

            Permohonan_detail::create([
                'id_permohonan'      => $idPermohonan,
                'id_pengguna_divisi' => null,
                'jenis'              => 'kontrol',
                'status'             => $statusKontrol,
                'type'               => 'baru',
                'created_by'         => Auth::id(),
                'id_tld'             => $idTld,
            ]);

            Master_tld::find($idTld)?->update(['status' => 1]);
        }

        // Buat dokumen surat adendum
        $template = Documents::where('jenis', 'body')
            ->where('name', 'PermohonanAdendum')
            ->where('status', 1)
            ->first();

        Permohonan_dokumen::create([
            'id_permohonan'  => $idPermohonan,
            'created_by'     => Auth::id(),
            'nama'           => 'Permohonan Adendum',
            'jenis'          => 'adendum',
            'id_doc_template' => $template?->id_doc,
            'status'         => 1,
            'nomer'          => generateNoDokumen('adendum', $idPermohonan),
        ]);
    }

    /**
     * Resolve nilai TLD untuk case 'ganti' saat verifikasi.
     * Mengembalikan [tld_1, tld_2, status_tld_1, status_tld_2, periode_tld_1, periode_tld_2]
     */
    private function resolveTldForGanti(bool $isPeriodOne, $detail, int $periode): array
    {
        if ($isPeriodOne) {
            return [$detail->id_tld, null, $detail->status, null, $periode, null];
        }

        return [null, $detail->id_tld, null, $detail->status, null, $periode];
    }

    /**
     * Menentukan status awal permohonan_detail berdasarkan type adendum dan sumber TLD.
     *
     * Status:
     *   3 = Siap (TLD sudah ada / dari pelanggan)
     *   5 = Menunggu (TLD dari LAB/penyimpanan, belum dikirim)
     *
     * @param  Permohonan|null $permohonan
     * @param  string          $type  'ganti' | 'baru'
     * @return int
     */
    private function resolveStatusDetail(?Permohonan $permohonan, string $type): int
    {
        // Pergantian pengguna: TLD sudah ada di pelanggan dari periode sebelumnya
        if ($type === 'ganti') {
            return 3;
        }

        // Penambahan baru: bergantung pada sumber TLD
        // is_have_tld = 1 → TLD dari Pelanggan (status 3)
        // is_have_tld = 0 → TLD dari LAB/penyimpanan (status 5, perlu pengiriman)
        return $permohonan?->is_have_tld ? 3 : 5;
    }
}
