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
            // if (!Auth::user()->hasRole('Pelanggan') && $no_kontrak) {
            //     $cekTldKontrak = Master_tld::where('digunakan', $no_kontrak)->where('status', 0)->first();
            // }

            // dd($cekTldKontrak);
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
                // ->orderBy('tanggal_pengadaan', 'desc')
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

            $filter = $request->input('filter', []);
            $idKontrakStr = \Illuminate\Support\Arr::get($filter, 'id_kontrak');
            $periodeVal = \Illuminate\Support\Arr::get($filter, 'periode');
            $dateRange = \Illuminate\Support\Arr::get($filter, 'date_range');
            $searchVal = \Illuminate\Support\Arr::get($filter, 'search');
            $statusVal = \Illuminate\Support\Arr::get($filter, 'status');

            $idKontrak = $idKontrakStr ? decryptor($idKontrakStr) : null;

            // ============================================================
            // WIDGET 1: TLD di lab, terikat kontrak (status_tld = 5)
            // ============================================================
            $detailsQuery = \App\Models\Kontrak_detail::with([
                'kontrak.pelanggan.perusahaan',
                'kontrak.jenis_layanan',
                'kontrak.jenis_layanan_parent',
                'tld_awal',
                'tld_second',
                'entitas'
            ])
                ->where(function ($query) {
                    $query->where('status_tld_1', 5)
                        ->orWhere('status_tld_2', 5);
                })
                ->where('status', 1);

            if ($idKontrak) {
                $detailsQuery->where('id_kontrak', $idKontrak);
            }
            if ($periodeVal) {
                $detailsQuery->where(function ($q) use ($periodeVal) {
                    $q->where('periode_tld_1', $periodeVal)
                      ->orWhere('periode_tld_2', $periodeVal);
                });
            }
            if ($dateRange && is_array($dateRange) && count($dateRange) == 2) {
                $detailsQuery->whereHas('kontrak.periode', function ($q) use ($dateRange) {
                    $q->where('start_date', '<=', $dateRange[1])
                      ->where('end_date', '>=', $dateRange[0]);
                });
            }
            if ($searchVal) {
                $detailsQuery->where(function ($q) use ($searchVal) {
                    $q->whereHas('tld_awal', function ($qt) use ($searchVal) {
                        $qt->where('no_seri_tld', 'like', "%$searchVal%");
                    })
                    ->orWhereHas('tld_second', function ($qt) use ($searchVal) {
                        $qt->where('no_seri_tld', 'like', "%$searchVal%");
                    })
                    ->orWhereHas('entitas', function ($qe) use ($searchVal) {
                        $qe->where('name', 'like', "%$searchVal%");
                    })
                    ->orWhereHas('kontrak.pelanggan.perusahaan', function ($qp) use ($searchVal) {
                        $qp->where('nama_perusahaan', 'like', "%$searchVal%");
                    });
                });
            }

            $details = $detailsQuery->get();

            $tldDiLab = [];
            foreach ($details as $detail) {
                foreach (
                    [
                        ['status' => $detail->status_tld_1, 'tld' => $detail->tld_awal, 'periode' => $detail->periode_tld_1],
                        ['status' => $detail->status_tld_2, 'tld' => $detail->tld_second, 'periode' => $detail->periode_tld_2],
                    ] as $item
                ) {
                    if ($item['status'] == 5 && $item['tld']) {
                        $getPeriodeNow = \App\Models\Kontrak_periode::where('id_kontrak', $detail->id_kontrak)
                            ->where('periode', $item['periode'])
                            ->first();
                        $penyelia = \App\Models\Penyelia::where('id_kontrak', $detail->id_kontrak)
                            ->where('id_permohonan', $getPeriodeNow->id_permohonan)
                            ->whereHas('penyelia_map', function ($q) {
                                $q->where('id_jobs', 7)->where('status', 1);
                            })
                            ->first();
                        $tldDiLab[] = [
                            'no_seri_tld'   => $item['tld']->no_seri_tld,
                            'jenis_tld'     => $item['tld']->jenis,
                            'no_kontrak'    => $detail->kontrak->no_kontrak ?? '-',
                            'perusahaan'    => $detail->kontrak->pelanggan->perusahaan->nama_perusahaan ?? '-',
                            'periode'       => $item['periode'],
                            'periodenow'    => $getPeriodeNow,
                            'pengguna'      => $detail->entitas->name ?? '-',
                            'penyelia_hash' => $penyelia?->penyelia_hash,
                        ];
                    }
                }
            }

            // ============================================================
            // WIDGET 2 & 3: TLD di Evaluasi / Sewa (status_tld = 1 atau 2)
            // ============================================================
            $detailsAktifQuery = \App\Models\Kontrak_detail::with([
                'kontrak.pelanggan.perusahaan',
                'kontrak.jenis_layanan',
                'kontrak.jenis_layanan_parent',
                'tld_awal',
                'tld_second',
                'entitas'
            ])
                ->where(function ($query) {
                    $query->whereIn('status_tld_1', [1, 2])
                        ->orWhereIn('status_tld_2', [1, 2]);
                })
                ->where('status', 1)
                ->whereHas('kontrak', function ($q) {
                    $q->where('status', 1);
                });

            if ($idKontrak) {
                $detailsAktifQuery->where('id_kontrak', $idKontrak);
            }
            if ($periodeVal) {
                $detailsAktifQuery->where(function ($q) use ($periodeVal) {
                    $q->where('periode_tld_1', $periodeVal)
                      ->orWhere('periode_tld_2', $periodeVal);
                });
            }
            if ($dateRange && is_array($dateRange) && count($dateRange) == 2) {
                $detailsAktifQuery->whereHas('kontrak.periode', function ($q) use ($dateRange) {
                    $q->where('start_date', '<=', $dateRange[1])
                      ->where('end_date', '>=', $dateRange[0]);
                });
            }
            if ($searchVal) {
                $detailsAktifQuery->where(function ($q) use ($searchVal) {
                    $q->whereHas('tld_awal', function ($qt) use ($searchVal) {
                        $qt->where('no_seri_tld', 'like', "%$searchVal%");
                    })
                    ->orWhereHas('tld_second', function ($qt) use ($searchVal) {
                        $qt->where('no_seri_tld', 'like', "%$searchVal%");
                    })
                    ->orWhereHas('entitas', function ($qe) use ($searchVal) {
                        $qe->where('name', 'like', "%$searchVal%");
                    })
                    ->orWhereHas('kontrak.pelanggan.perusahaan', function ($qp) use ($searchVal) {
                        $qp->where('nama_perusahaan', 'like', "%$searchVal%");
                    });
                });
            }

            $detailsAktif = $detailsAktifQuery->get();

            $tldEvaluasi = [];
            $tldSewa     = [];

            foreach ($detailsAktif as $detail) {
                if (!$detail->kontrak || !$detail->kontrak->jenis_layanan_parent || !$detail->kontrak->jenis_layanan) {
                    continue;
                }
                $jl = jenislayanan($detail->kontrak->jenis_layanan_parent, $detail->kontrak->jenis_layanan);

                foreach (
                    [
                        ['status' => $detail->status_tld_1, 'tld' => $detail->tld_awal, 'periode' => $detail->periode_tld_1],
                        ['status' => $detail->status_tld_2, 'tld' => $detail->tld_second, 'periode' => $detail->periode_tld_2],
                    ] as $item
                ) {
                    if (in_array($item['status'], [1, 2]) && $item['tld']) {
                        $entry = [
                            'no_seri_tld' => $item['tld']->no_seri_tld,
                            'jenis_tld'   => $item['tld']->jenis,
                            'no_kontrak'  => $detail->kontrak->no_kontrak ?? '-',
                            'perusahaan'  => $detail->kontrak->pelanggan->perusahaan->nama_perusahaan ?? '-',
                            'periode'     => $item['periode'],
                            'pengguna'    => $detail->entitas->name ?? '-',
                        ];
                        if (in_array($jl, $arrEvaluasi)) {
                            $tldEvaluasi[] = $entry;
                        } elseif (in_array($jl, $arrSewa)) {
                            $tldSewa[] = $entry;
                        }
                    }
                }
            }

            // ============================================================
            // WIDGET 4: TLD Idle (status = 0 di master_tld)
            // ============================================================
            $idleTldsQuery = \App\Models\Master_tld::where('status', 0)->whereNull('digunakan')->whereNull('kepemilikan');

            if ($searchVal) {
                $idleTldsQuery->where('no_seri_tld', 'like', "%$searchVal%");
            }

            if ($idKontrak || $periodeVal || ($dateRange && is_array($dateRange) && count($dateRange) == 2)) {
                $idleTlds = collect();
            } else {
                $idleTlds = $idleTldsQuery->get();
            }

            $tldIdle = [];
            foreach ($idleTlds as $tld) {
                // Ambil history terakhir dari kontrak_map
                $lastHistory = \App\Models\Kontrak_map::with(['kontrak:id_kontrak,no_kontrak'])
                    ->where('id_tld', $tld->id_tld)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $tldIdle[] = [
                    'no_seri_tld'      => $tld->no_seri_tld,
                    'jenis_tld'        => $tld->jenis,
                    'merk'             => $tld->merk,
                    'tanggal_pengadaan' => $tld->tanggal_pengadaan,
                    'last_history'     => $lastHistory ? [
                        'no_kontrak' => $lastHistory->kontrak->no_kontrak ?? '-',
                        'periode'    => $lastHistory->periode,
                        'used_at'    => $lastHistory->created_at,
                    ] : null,
                ];
            }

            // Filter berdasarkan status
            if ($statusVal) {
                if ($statusVal == 'di_lab') {
                    $tldEvaluasi = [];
                    $tldSewa     = [];
                    $tldIdle     = [];
                } elseif ($statusVal == 'evaluasi') {
                    $tldDiLab    = [];
                    $tldSewa     = [];
                    $tldIdle     = [];
                } elseif ($statusVal == 'sewa') {
                    $tldDiLab    = [];
                    $tldEvaluasi = [];
                    $tldIdle     = [];
                } elseif ($statusVal == 'idle') {
                    $tldDiLab    = [];
                    $tldEvaluasi = [];
                    $tldSewa     = [];
                }
            }

            // ============================================================
            // SUMMARY COUNTER
            // ============================================================
            $summary = [
                'tld_di_lab'   => count($tldDiLab),
                'tld_evaluasi' => count($tldEvaluasi),
                'tld_sewa'     => count($tldSewa),
                'tld_idle'     => count($tldIdle),
                'total'        => count($tldDiLab) + count($tldEvaluasi) + count($tldSewa) + count($tldIdle),
            ];

            return $this->output([
                'summary'      => $summary,
                'tld_di_lab'   => $tldDiLab,
                'tld_evaluasi' => $tldEvaluasi,
                'tld_sewa'     => $tldSewa,
                'tld_idle'     => $tldIdle,
            ], 200);
        } catch (\Exception $ex) {
            info($ex);
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}