<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Models\Master_tld;
use App\Traits\RestApi;

use DB;
use Auth;

class TldAPI extends Controller
{
    use RestApi;

    public function action(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->id ? decryptor($request->id) : false;
            $kode = $request->has('kode') ? $request->kode : false;
            $jenis = $request->has('jenis') ? $request->jenis : false;
            $status = $request->has('status') ? $request->status : false;

            $data = array();

            $kode ? $data['kode'] = $kode : false;
            $jenis ? $data['jenis'] = $jenis : false;
            $status ? $data['status'] = $status : false;

            $id && $data['id'] = $id;

            //save to db
            $tld = Master_tld::updateOrCreate(
                ['id_tld' => $id],
                $data
            );

            DB::commit();
            return $this->output(array('msg' => 'Data berhasil disimpan!', 'id' => $tld->tld_hash));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function searchTld(Request $request)
    {
        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;
            $kode_lencana = $request->has('kode_lencana') ? $request->kode_lencana : false;
            $limit = 10;
            $data = array();

            if (!empty($kode_lencana)) {
                $data = Master_tld::where('jenis', $jenis)
                    ->where('kode_lencana', 'like', '%' . $kode_lencana . '%')
                    ->limit($limit)
                    ->orderBy('status', 'desc')->get();
            } else {
                $data = Master_tld::limit($limit)->where('jenis', $jenis)->orderBy('status', 'desc')->get();
            }

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function searchTldNotUsed(Request $request)
    {
        $request->validate([
            'jenis' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;

            // if role nya pelanggan
            if (Auth::user()->hasRole('Pelanggan')) {
                $idPerusahaan = Auth::user()->id_perusahaan;
                $data = Master_tld::where('status', 0)->where('kepemilikan', $idPerusahaan)->whereNull('digunakan')->where('jenis', $jenis)->get();
            } else {
                $data = Master_tld::where('status', 0)->whereNull('kepemilikan')->where('jenis', $jenis)->get();
            }


            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getById($id)
    {
        DB::beginTransaction();
        try {
            // $id = decryptor($id);
            $data = Master_tld::find($id);
            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getData(Request $request)
    {
        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;
            $status = $request->has('status') ? $request->status : false;
            $search = $request->has('search') ? $request->search : false;
            $no_kontrak = $request->has('no_kontrak') ? $request->no_kontrak : false;

            $page = $request->has('page') ? $request->page : 1;
            $limit = $request->has('limit') ? $request->limit : 5;

            // pengecekan role user
            $role = count(Auth::user()->getRoleNames()) > 0 ? true : false;

            // pengecekan tld yang sedang digunakan oleh kontrak
            $cekTldKontrak = false;

            $data = Master_tld::when($role, function ($query, $role) use ($no_kontrak, $cekTldKontrak) {
                if (Auth::user()->hasRole('Pelanggan')) {
                    return $query->where('kepemilikan', Auth::user()->id_perusahaan)->whereNull('digunakan');
                } else {
                    if (!$cekTldKontrak) {
                        return $query->whereNull('kepemilikan');
                    }
                }
            })
                ->when($cekTldKontrak, function ($query, $cekTldKontrak) use ($no_kontrak) {
                    return $query->where('digunakan', $no_kontrak)->where('status', 0);
                })
                ->when($jenis, function ($query, $jenis) {
                    return $query->where('jenis', $jenis);
                })
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($search, function ($query, $search) {
                    return $query->where('no_seri_tld', 'like', '%' . $search . '%')->orWhere('merk', 'like', '%' . $search . '%');
                })
                ->orderBy('status', 'asc')
                ->orderBy('jenis', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->paginate($limit);

            $arr = $data->toArray();
            $this->pagination = Arr::except($arr, 'data');
            DB::commit();

            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPenyimpanan(Request $request)
    {
        try {
            $arrEvaluasi = config('customvariabel.arr_evaluasi');
            $arrSewa     = config('customvariabel.arr_sewa');

            $filter       = $request->input('filter', []);
            $idKontrakStr = Arr::get($filter, 'id_kontrak');
            $searchVal    = Arr::get($filter, 'search');
            $statusVal    = Arr::get($filter, 'status');
            $dateRange    = Arr::get($filter, 'date_range');

            $idKontrak = $idKontrakStr ? decryptor($idKontrakStr) : null;

            // ============================================================
            // SATU QUERY: ambil semua kontrak_detail dengan TLD aktif
            // status_tld IN (1=aktif/evaluasi, 2=sewa, 5=di lab/kembali)
            // ============================================================
            $detailsQuery = \App\Models\Kontrak_detail::with([
                'kontrak',
                'kontrak.pelanggan.perusahaan',
                'kontrak.jenis_layanan',
                'kontrak.jenis_layanan_parent',
                'tld_awal',
                'tld_second',
                'entitas',
            ])
                ->where(function ($q) {
                    $q->whereIn('status_tld_1', [1, 2, 3, 5, 6])
                        ->orWhereIn('status_tld_2', [1, 2, 3, 5, 6]);
                })
                ->where('status', 1)
                ->whereHas('kontrak', fn($q) => $q->where('status', 1));

            if ($idKontrak) {
                $detailsQuery->where('id_kontrak', $idKontrak);
            }
            if ($searchVal) {
                $detailsQuery->where(function ($q) use ($searchVal) {
                    $q->whereHas('tld_awal', fn($qt) => $qt->where('no_seri_tld', 'like', "%$searchVal%"))
                        ->orWhereHas('tld_second', fn($qt) => $qt->where('no_seri_tld', 'like', "%$searchVal%"))
                        ->orWhereHas('entitas', fn($qe) => $qe->where('name', 'like', "%$searchVal%"))
                        ->orWhereHas('kontrak.pelanggan.perusahaan', fn($qp) => $qp->where('nama_perusahaan', 'like', "%$searchVal%"));
                });
            }
            if ($dateRange && is_array($dateRange) && count($dateRange) == 2) {
                $detailsQuery->whereHas('kontrak.periode', function ($q) use ($dateRange) {
                    $q->where('start_date', '<=', $dateRange[1])
                        ->where('end_date', '>=', $dateRange[0]);
                });
            }

            $details = $detailsQuery->get();

            // ============================================================
            // GROUPING PER KONTRAK
            // ============================================================
            $grouped = [];

            foreach ($details as $detail) {
                if (!$detail->kontrak || !$detail->kontrak->jenis_layanan_parent || !$detail->kontrak->jenis_layanan) {
                    continue;
                }

                $idK     = $detail->id_kontrak;
                $kontrak = $detail->kontrak;
                $jl      = jenislayanan($kontrak->jenis_layanan_parent, $kontrak->jenis_layanan);

                // Tentukan tipe layanan
                if (in_array($jl, $arrEvaluasi)) {
                    $tipe = 'evaluasi';
                } elseif (in_array($jl, $arrSewa)) {
                    $tipe = 'sewa';
                } else {
                    $tipe = 'di_lab';
                }

                // Filter berdasarkan status yang dipilih
                if ($statusVal && $statusVal !== $tipe) {
                    continue;
                }

                // Inisialisasi entri kontrak jika belum ada
                if (!isset($grouped[$idK])) {
                    $grouped[$idK] = [
                        'id_kontrak'       => $idK,
                        'kontrak_hash'     => $kontrak->kontrak_hash,
                        'no_kontrak'       => $kontrak->no_kontrak ?? '-',
                        'perusahaan'       => $kontrak->pelanggan?->perusahaan
                            ? ($kontrak->pelanggan->perusahaan->kode_perusahaan . ' - ' . $kontrak->pelanggan->perusahaan->nama_perusahaan)
                            : '-',
                        'tipe'             => $tipe,
                        'kontrak_pengguna' => 0, // akan diisi dari query count di bawah
                        'kontrak_kontrol'  => 0, // akan diisi dari query count di bawah
                        'tlds'             => [],
                    ];
                }

                // Proses TLD 1 dan TLD 2 dari setiap baris kontrak_detail
                $tldSlots = [
                    ['status' => $detail->status_tld_1, 'tld' => $detail->tld_awal,   'periode' => $detail->periode_tld_1],
                    ['status' => $detail->status_tld_2, 'tld' => $detail->tld_second, 'periode' => $detail->periode_tld_2],
                ];

                foreach ($tldSlots as $slot) {
                    if (!in_array($slot['status'], [1, 2, 3, 5, 6]) || !$slot['tld']) {
                        continue;
                    }

                    // Label status
                    $labelStatus = match ((int) $slot['status']) {
                        1, 5    => 'LAB',
                        2       => 'Pelanggan',
                        3       => 'evaluasi',
                        6       => 'Siap dikirim',
                        default => ucfirst($tipe),
                    };

                    // Ambil penyelia untuk tombol Update LHU (hanya jika TLD masih di luar)
                    $penyeliaHash = null;
                    if (in_array((int) $slot['status'], [1, 5])) {
                        $getPeriode = \App\Models\Kontrak_periode::where('id_kontrak', $idK)
                            ->where('periode', $slot['periode'])
                            ->first();
                        if ($getPeriode?->id_permohonan) {
                            $penyelia = \App\Models\Penyelia::where('id_kontrak', $idK)
                                ->where('id_permohonan', $getPeriode->id_permohonan)
                                ->whereHas('penyelia_map', fn($q) => $q->where('id_jobs', 7)->where('status', 1))
                                ->first();
                            $penyeliaHash = $penyelia?->penyelia_hash;
                        }
                    }

                    $grouped[$idK]['tlds'][] = [
                        'no_seri_tld'   => $slot['tld']->no_seri_tld,
                        'jenis_tld'     => $slot['tld']->jenis,
                        'jenis'         => $detail->jenis,
                        'pengguna'      => $detail->entitas?->name ?? '-',
                        'periode'       => $slot['periode'],
                        'status_tld'    => (int) $slot['status'],
                        'label_status'  => $labelStatus,
                        'penyelia_hash' => $penyeliaHash,
                    ];
                }
            }

            // ============================================================
            // PATCH jumlah pengguna & kontrol dari kontrak_detail status=1
            // (satu query untuk semua kontrak, bukan N+1)
            // ============================================================
            if (!empty($grouped)) {
                $kontrakIds   = array_keys($grouped);
                $detailCounts = \App\Models\Kontrak_detail::whereIn('id_kontrak', $kontrakIds)
                    ->where('status', 1)
                    ->selectRaw('id_kontrak, jenis, COUNT(*) as total')
                    ->groupBy('id_kontrak', 'jenis')
                    ->get()
                    ->groupBy('id_kontrak');

                foreach ($grouped as $idK => &$entry) {
                    $counts = $detailCounts->get($idK, collect());
                    $entry['kontrak_pengguna'] = (int) ($counts->firstWhere('jenis', 'pengguna')?->total ?? 0);
                    $entry['kontrak_kontrol']  = (int) ($counts->firstWhere('jenis', 'kontrol')?->total ?? 0);
                }
                unset($entry);
            }

            // ============================================================
            // HITUNG semua_kembali & SUMMARY
            // ============================================================
            $result       = [];
            $totalKontrak = 0;
            $belumKembali = 0;
            $sudahKembali = 0;
            $totalTld     = 0;

            foreach ($grouped as $entry) {
                if (empty($entry['tlds'])) {
                    continue;
                }

                // all-or-nothing: semua kembali = semua TLD berstatus 1 atau 5 (LAB)
                $semuaKembali = collect($entry['tlds'])->every(fn($t) => in_array($t['status_tld'], [1, 5]));

                $entry['semua_kembali'] = $semuaKembali;
                $result[]               = $entry;
                $totalKontrak++;
                $totalTld += count($entry['tlds']);

                $semuaKembali ? $sudahKembali++ : $belumKembali++;
            }

            // Urutkan: kontrak yang belum kembali tampil di atas
            usort($result, fn($a, $b) => (int) $a['semua_kembali'] - (int) $b['semua_kembali']);

            return $this->output([
                'summary' => [
                    'total_kontrak'  => $totalKontrak,
                    'total_tld'      => $totalTld,
                    'belum_kembali'  => $belumKembali,
                    'sudah_kembali'  => $sudahKembali,
                ],
                'kontrak' => $result,
            ], 200);
        } catch (\Exception $ex) {
            info($ex);
            return $this->output(['msg' => $ex->getMessage()], 'Fail', 500);
        }
    }
}
