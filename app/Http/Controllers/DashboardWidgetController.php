<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\Keuangan;
use App\Models\Kontrak;
use App\Models\Master_ekspedisi;
use App\Models\Master_jenisLayanan;
use App\Models\Master_jenistld;
use App\Models\Master_jobs;
use App\Models\Master_tld;
use App\Models\Penyelia;
use App\Models\Master_layanan_jasa;
use App\Models\Pengiriman;
use App\Models\Master_media;
use App\Models\Penyelia_petugas;
use App\Models\User;
use Auth;

class DashboardWidgetController extends Controller
{
    // Widget Summary Card
    public function summaryCards(Request $request)
    {
        $jenisCard = $request->has('jenis') ? $request->jenis : null;
        $dateFilter = $request->input('date_filter', 'monthly');

        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        $user = Auth::user();
        $html = '';

        switch ($jenisCard) {
            case 'permohonan':
                $countsQuery = Permohonan::query()
                    ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                        $q->whereHas('pelanggan', function ($q) use ($user) {
                            $q->where('id_perusahaan', $user->id_perusahaan);
                        });
                    });
                $applyFilter($countsQuery);
                $counts = $countsQuery->whereIn('status', [1, 2, 3, 4, 5, 90])
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $countBaru = $counts->get(1, 0);
                $countVerifikasi = $counts->get(2, 0) + $counts->get(3, 0) + $counts->get(4, 0) + $counts->get(5, 0);
                $countDitolak = $counts->get(90, 0);

                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-journal-text',
                    'text' => 'Permohonan',
                    'type' => 'list',
                    'count' => [
                        ['text' => 'Baru', 'icon' => 'bi-file-earmark-plus', 'count' => $countBaru, 'color' => 'text-primary'],
                        ['text' => 'Terverifikasi', 'icon' => 'bi-shield-check', 'count' => $countVerifikasi, 'color' => 'text-warning-emphasis'],
                        ['text' => 'Ditolak', 'icon' => 'bi-x-circle', 'count' => $countDitolak, 'color' => 'text-danger'],
                    ],
                    'color' => 'text-info',
                    'url' => $user->hasRole('Pelanggan') ? route('permohonan.pengajuan') : route('staff.permohonan')
                ])->render();
                break;
            case 'pembayaran':
                $countsQuery = Keuangan::query() // Menghapus eager loading yang tidak perlu
                    ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                        $q->whereHas('permohonan', function ($q) use ($user) {
                            $q->whereHas('pelanggan', function ($q) use ($user) {
                                $q->where('id_perusahaan', $user->id_perusahaan); // Menggunakan $user->id_perusahaan
                            });
                        });
                    });
                $applyFilter($countsQuery);
                $counts = $countsQuery->whereIn('status', [3, 5, 90])
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $countBelumLunas = $counts->get(3, 0);
                $countLunas = $counts->get(5, 0);
                $countDitolak = $counts->get(90, 0);

                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-wallet2',
                    'text' => 'Pembayaran',
                    'type' => 'list',
                    'count' => [
                        array('text' => 'Belum Lunas', 'icon' => 'bi-exclamation-circle', 'count' => $countBelumLunas, 'color' => 'text-warning-emphasis'),
                        array('text' => 'Lunas', 'icon' => 'bi-check-circle-fill', 'count' => $countLunas, 'color' => 'text-success'),
                        array('text' => 'Ditolak', 'icon' => 'bi-dash-circle', 'count' => $countDitolak, 'color' => 'text-danger'),
                    ],
                    'color' => 'text-warning-emphasis',
                    'url' => $user->hasRole('Pelanggan') ? route('permohonan.pembayaran') : route('staff.keuangan')
                ])->render();
                break;
            case 'kontrak':
                $countsQuery = Kontrak::query()
                    ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                        $q->whereHas('pelanggan', function ($q) use ($user) {
                            $q->where('id_perusahaan', $user->id_perusahaan);
                        });
                    });
                $applyFilter($countsQuery);
                $counts = $countsQuery->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $countBerjalan = $counts->get(1, 0);
                $countSelesai = $counts->get(2, 0);
                $countDeactive = $counts->get(99, 0);

                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-briefcase',
                    'text' => 'Kontrak',
                    'type' => 'list',
                    'count' => [
                        array('text' => 'Berjalan', 'icon' => 'bi-hourglass-split', 'count' => $countBerjalan, 'color' => 'text-info'),
                        array('text' => 'Selesai', 'icon' => 'bi-check-circle-fill', 'count' => $countSelesai, 'color' => 'text-success'),
                    ],
                    'color' => 'text-secondary',
                    'url' => route('permohonan.kontrak')
                ])->render();
                break;
            case 'tld':
                $count = Master_tld::query()
                    ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                        $q->where('kepemilikan', $user->id_perusahaan);
                    }, function ($q) {
                        $q->whereNull('kepemilikan');
                    })
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $countTersedia = $count->get(0, 0);
                $countTerpakai = $count->get(1, 0);

                $countTld = Master_tld::query()
                    ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                        $q->where('kepemilikan', $user->id_perusahaan);
                    }, function ($q) {
                        $q->whereNull('kepemilikan');
                    })
                    ->selectRaw('jenis, count(*) as total')
                    ->groupBy('jenis')
                    ->pluck('total', 'jenis');

                $countTldPengguna = $countTld->get('pengguna', 0);
                $countTldKontrol = $countTld->get('kontrol', 0);

                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-motherboard',
                    'text' => 'TLD',
                    'type' => 'list',
                    'count' => [
                        array('text' => 'Tersedia', 'icon' => 'bi-check-circle-fill', 'count' => $countTersedia, 'color' => 'text-success'),
                        array('text' => 'Terpakai', 'icon' => 'bi-x-circle', 'count' => $countTerpakai, 'color' => 'text-danger'),
                        array('text' => 'Kontrol', 'icon' => 'bi-hourglass-split', 'count' => $countTldKontrol, 'color' => 'text-info'),
                        array('text' => 'Pengguna', 'icon' => 'bi-people-fill', 'count' => $countTldPengguna, 'color' => 'text-warning-emphasis'),
                    ],
                    'color' => 'text-secondary',
                    'url' => url('/management/tld')
                ])->render();
                break;
            case 'penyelia':
                $countsQuery = Penyelia::query()
                    ->with('permohonan')
                    ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                        $q->whereHas('permohonan', function ($q) use ($user) {
                            $q->where('created_by', $user->id);
                        });
                    });
                $applyFilter($countsQuery);
                $counts = $countsQuery->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $countBaru = $counts->get(1, 0);
                $countTTD = $counts->get(2, 0);
                $countProsesLab = $counts->get(10, 0);
                $countSelesai = $counts->get(3, 0);
                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-people-fill',
                    'text' => 'Penyeliaan',
                    'type' => 'list',
                    'count' => [
                        array('text' => 'Baru', 'icon' => 'bi-person-plus-fill', 'count' => $countBaru, 'color' => 'text-primary'),
                        array('text' => 'TTD', 'icon' => 'bi-pencil-square', 'count' => $countTTD, 'color' => 'text-warning-emphasis'),
                        array('text' => 'Proses Lab', 'icon' => 'bi-hourglass-split', 'count' => $countProsesLab, 'color' => 'text-info'),
                        array('text' => 'Selesai', 'icon' => 'bi-check-circle-fill', 'count' => $countSelesai, 'color' => 'text-success'),
                    ],
                    'color' => 'text-secondary',
                    'url' => route('staff.penyelia')
                ])->render();
                break;
            case 'petugaslab':
                // Ambil semua ID petugas lab yang relevan
                $petugasQuery = User::query()
                    ->when(!$user->hasRole('Super Admin'), function ($query) use ($user) {
                        // Kelompokkan kondisi OR dalam satu closure
                        $query->where(function ($q) use ($user) {
                            foreach ($user->satuankerja_id as $satuanId) {
                                $q->orWhereJsonContains('satuankerja_id', $satuanId);
                            }
                        });
                    });

                $totalPetugas = $petugasQuery->count();

                // Hitung petugas yang sedang bertugas
                // Petugas dianggap bertugas jika terhubung ke penyelia_map dengan status 2
                $petugasBertugas = $petugasQuery->clone()->whereHas('penyelia_petugas.penyelia_map', function ($q) {
                    $q->whereNot('status', 2);
                })->count();

                $petugasTersedia = $totalPetugas - $petugasBertugas;

                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-flask',
                    'text' => 'Petugas Lab',
                    'type' => 'list',
                    'count' => [
                        ['text' => 'Tersedia', 'icon' => 'bi-check-circle-fill', 'count' => $petugasTersedia, 'color' => 'text-success'],
                        ['text' => 'Bertugas', 'icon' => 'bi-hourglass-split', 'count' => $petugasBertugas, 'color' => 'text-info'],
                    ],
                    'color' => 'text-secondary',
                    'url' => route('staff.lhu.petugas')
                ])->render();
                break;
            case 'invoice':
                $countsQuery = Keuangan::query() // Menghapus eager loading yang tidak perlu
                    ->whereIn('status', [7, 4, 3, 5, 90]);
                $applyFilter($countsQuery);

                $counts = $countsQuery->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $pricesQuery = Keuangan::query()
                    ->whereIn('status', [7, 4, 3, 5, 90])
                    ->with('diskon')
                    ->select('id_keuangan', 'total_harga', 'pph', 'ppn', 'status');
                $applyFilter($pricesQuery);

                $prices = $pricesQuery->get();

                $priceSums = [3 => 0, 4 => 0, 5 => 0, 7 => 0, 90 => 0];

                foreach ($prices as $p) {
                    $calc = calculateInvoice($p->total_harga, $p->diskon, $p->ppn, $p->pph);
                    $priceSums[$p->status] += $calc['subTotal'];
                }

                $priceBelumLunas = formatCurrency($priceSums[3]);
                $priceVerifikasi = formatCurrency($priceSums[4]);
                $priceLunas = formatCurrency($priceSums[5]);
                $priceFaktur = formatCurrency($priceSums[7]);
                $priceDitolak = formatCurrency($priceSums[90]);

                $countBelumLunas = $counts->get(3, 0);
                $countVerifikasi = $counts->get(4, 0);
                $countLunas = $counts->get(5, 0);
                $countFaktur = $counts->get(7, 0);
                $countDitolak = $counts->get(90, 0);

                // cek apakah semua data kosong
                $isEmpty = $countBelumLunas === 0 && $countVerifikasi === 0 && $countLunas === 0 && $countFaktur === 0 && $countDitolak === 0;

                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-receipt',
                    'text' => 'Status Invoice Aktif',
                    'type' => 'list',
                    'color' => 'text-primary',
                    'url' => 'javascript:void(0)',
                    'isEmpty' => $isEmpty,
                    'withFilter' => false,
                    'filterDefault' => $dateFilter,
                    'idWidget' => 'widget-summary-invoice',
                    'count' => [
                        ['text' => 'Faktur', 'icon' => 'bi-file-earmark-text-fill', 'count' => $countFaktur, 'price' => $priceFaktur, 'color' => 'text-secondary'],
                        ['text' => 'Menunggu Bayar', 'icon' => 'bi-clock-fill', 'count' => $countBelumLunas, 'price' => $priceBelumLunas, 'color' => 'text-warning'],
                        ['text' => 'Perlu Verifikasi', 'icon' => 'bi-shield-check', 'count' => $countVerifikasi, 'price' => $priceVerifikasi, 'color' => 'text-info'],
                        ['text' => 'Selesai', 'icon' => 'bi-check-circle-fill', 'count' => $countLunas, 'price' => $priceLunas, 'color' => 'text-success'],
                        ['text' => 'Ditolak', 'icon' => 'bi-x-circle-fill', 'count' => $countDitolak, 'price' => $priceDitolak, 'color' => 'text-danger'],
                    ]
                ])->render();
                break;
            default:
                # code...
                break;
        }

        return response()->json([
            'html' => $html
        ]);
    }

    public function expeditionStats(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'monthly');
        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        $ekspedisi = Master_ekspedisi::withCount(['pengiriman' => function ($query) use ($applyFilter) {
            $applyFilter($query);
        }])->get()->pluck('pengiriman_count', 'name')->toArray();

        $category = array_keys($ekspedisi);
        $value = array_values($ekspedisi);

        $barChart = [
            'category' => $category,
            'value' => $value
        ];
        $isEmpty = array_sum($value) === 0;

        $charts = [];
        if (!$isEmpty) {
            $charts[] = [
                'id_chart' => 'expeditionChart',
                'type' => 'line',
                'series_name' => 'Pengiriman',
                'yaxis_title' => 'Jumlah Pengiriman',
                'stacked' => true,
                'distributed' => true,
                'data' => $barChart
            ];
        }

        return response()->json([
            'html' => view('components.dashboard.service-chart', [
                'charts' => $charts,
                'isEmpty' => $isEmpty,
                'title' => 'Statistik Ekspedisi',
                'icon' => 'bar-chart-line-fill',
                'withFilter' => true,
                'filterDefault' => $dateFilter,
                'idWidget' => 'widget-expedisi-stats'
            ])->render()
        ]);
    }
    public function statisticsLayanan(Request $request)
    {
        $user = Auth::user();
        $dateFilter = $request->input('date_filter', 'monthly');
        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        $layanan = Master_layanan_jasa::where('status', 1)->get();
        $layananNow = null;

        if ($request->has('jenis_layanan')) {
            $idLayanan = decryptor($request->jenis_layanan);
            // Find the requested service from the collection we already fetched.
            $layananNow = $layanan->firstWhere('id_layanan', $idLayanan);
        }

        // If no service was requested or the requested one wasn't found, default to the first active service.
        if (!$layananNow) {
            $layananNow = $layanan->first();
        }

        $category = [];
        $value = [];

        if ($layananNow->nama_layanan === 'TLD') {
            $statistik = Master_jenistld::withCount(['kontrak' => function ($query) use ($user, $layananNow, $applyFilter) {
                $query->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                    $q->whereHas('pelanggan', function ($q) use ($user) {
                        $q->where('id_perusahaan', $user->id_perusahaan);
                    });
                })
                    ->where('id_layanan', $layananNow->id_layanan);
                $applyFilter($query);
            }])->get()
                ->pluck('kontrak_count', 'name');

            $category = $statistik->keys();
            $value = $statistik->values();
        }

        $chart_1 = array(
            "category" => $category->toArray(),
            "value" => array_map('intval', $value->toArray())
        );

        // Chart 2
        $statistik_2 = Master_jenisLayanan::whereNull('parent')
            ->where('status', 1)
            ->withCount(['kontrak' => function ($query) use ($user, $applyFilter) {
                $query->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                    $q->whereHas('pelanggan', function ($q) use ($user) {
                        $q->where('id_perusahaan', $user->id_perusahaan);
                    });
                });
                $applyFilter($query);
            }])->get()
            ->pluck('kontrak_count', 'name');

        $chart_2 = array(
            "category" => $statistik_2->keys()->toArray(),
            "value" => array_map('intval', $statistik_2->values()->toArray())
        );

        // cek apakah semua data kosong
        $isEmpty = $value->sum() === 0 && $statistik_2->values()->sum() === 0;

        $charts = [];
        if (!$isEmpty) {
            if (array_sum($chart_1['value']) > 0 || $layananNow->nama_layanan === 'TLD') {
                $charts[] = [
                    'id_chart' => 'chart_layanan_1',
                    'type' => 'bar',
                    'series_name' => 'Kontrak',
                    'yaxis_title' => 'Jumlah Kontrak',
                    'stacked' => true,
                    'distributed' => true,
                    'data' => $chart_1
                ];
            }
            if (array_sum($chart_2['value']) > 0) {
                $charts[] = [
                    'id_chart' => 'chart_layanan_2',
                    'type' => 'donut',
                    'tooltip_suffix' => 'Pengajuan',
                    'data' => $chart_2
                ];
            }
        }

        // count layanan
        return response()->json([
            'html' => view('components.dashboard.service-chart', [
                'charts' => $charts,
                'data_layanan' => $layanan,
                'isEmpty' => $isEmpty,
                'title' => 'Statistik Layanan',
                'icon' => 'pie-chart-fill',
                'withFilter' => true,
                'filterDefault' => $dateFilter,
                'idWidget' => 'widget-statistics'
            ])->render()
        ]);
    }

    public function deliveryStats(Request $request)
    {
        $user = Auth::user();
        $dateFilter = $request->input('date_filter', 'monthly');
        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        $countQuery = Pengiriman::query()
            ->with('permohonan')
            ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                $q->whereHas('permohonan', function ($q) use ($user) {
                    $q->whereHas('pelanggan', function ($q) use ($user) {
                        $q->where('id_perusahaan', $user->id_perusahaan);
                    });
                });
            });
        $applyFilter($countQuery);

        $count = $countQuery->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $ops = $count->get(3) ?? 0;
        $shipping = $count->get(1) ?? 0;
        $arrived = $count->get(2) ?? 0;

        $statistics = [];
        $url = route('permohonan.pengiriman');

        if (!$user->hasRole('Pelanggan')) {
            $statistics[] = array(
                "status" => "ops",
                "count" => $ops,
                "name" => "On Proses",
                "color" => "primary",
                "icon" => "bi-arrow-repeat"
            );

            $url = route('staff.pengiriman');
        }

        $statistics[] = array(
            "status" => "shipping",
            "count" => $shipping,
            "name" => "Sedang Jalan",
            "color" => "info",
            "icon" => "bi-truck",
        );

        $statistics[] = array(
            "status" => "arrived",
            "count" => $arrived,
            "name" => "Sampai Tujuan",
            "color" => "success",
            "icon" => "bi-check-circle",
        );

        return response()->json([
            'html' => view('components.dashboard.delivery-stats', [
                'dataStatistics' => $statistics,
                'url' => $url
            ])->render()
        ]);
    }

    public function trackSearch(Request $request)
    {
        $keyword = $request->keyword;
        $user = Auth::user();

        // 1. Cari Data
        $pengiriman = Pengiriman::with(
            'detail',
            'tujuan_pengiriman:id,name',
            'alamat_pengiriman:id_alamat,alamat',
            'ekspedisi',
            'kontrak:id_kontrak,no_kontrak',
            'permohonan'
        )
            ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                $q->where(function ($subQ) use ($user) {
                    $subQ->whereHas('permohonan', function ($q) use ($user) {
                        $q->whereHas('pelanggan', function ($q) use ($user) {
                            $q->where('id_perusahaan', $user->id_perusahaan);
                        });
                    })->orWhereHas('kontrak', function ($q) use ($user) {
                        $q->whereHas('pelanggan', function ($q) use ($user) {
                            $q->where('id_perusahaan', $user->id_perusahaan);
                        });
                    });
                });
            })->where('no_resi', $keyword)->first();

        if ($pengiriman) {
            // get media bukti pengiriman
            $pengiriman->bukti_pengiriman && $pengiriman->media_pengiriman = Master_media::whereIn('id', $pengiriman->bukti_pengiriman)->get();

            // get media bukti penerima
            $pengiriman->bukti_penerima && $pengiriman->media_penerima = Master_media::whereIn('id', $pengiriman->bukti_penerima)->get();


            $data = (object) array(
                'nomor_resi' => $pengiriman->no_resi,
                'kurir' => $pengiriman->ekspedisi->name,
                'send_at' => $pengiriman->send_at,
                'status' => $pengiriman->status,
                'nama_penerima' => $pengiriman->tujuan_pengiriman->name,
                'alamat_tujuan' => $pengiriman->alamat_pengiriman->alamat,
                'isi_paket' => $pengiriman->detail->pluck('jenis')->implode(', '),
                'nomor_kontrak' => $pengiriman->kontrak->no_kontrak,
                'bukti_pengiriman' => $pengiriman->media_pengiriman ?? [],
                'bukti_penerima' => $pengiriman->media_penerima ?? [],
                'histories' => []
            );
        } else {
            $data = null;
        }

        // Simulasi Data Dummy Ditemukan
        // if ($keyword == 'JP888' || str_contains(strtolower($keyword), 's-001')) {
        //     $data = (object) [
        //         'nomor_resi' => 'JP-88219322',
        //         'kurir' => 'Kurir Internal',
        //         'estimasi_sampai' => '23 Nov 2025',
        //         'status' => 'shipping',
        //         'nama_penerima' => 'Bpk. Hartono (Security)',
        //         'alamat_tujuan' => 'PT. Nusa Lestari, Gedung A Lt. 1',
        //         'isi_paket' => 'Dokumen LHU & Invoice',
        //         'nomor_kontrak' => '#S-001/JKRL/2025',
        //         'histories' => [
        //             (object)['status_text' => 'Sedang diantar ke alamat tujuan', 'created_at' => now(), 'lokasi' => 'Jakarta Selatan'],
        //             (object)['status_text' => 'Paket keluar dari Hub Pusat', 'created_at' => now()->subHours(2), 'lokasi' => 'Gudang Cakung'],
        //             (object)['status_text' => 'Paket dijemput oleh kurir', 'created_at' => now()->subHours(5), 'lokasi' => 'Kantor Koperasi LAB'],
        //             (object)['status_text' => 'Paket diantar ke alamat tujuan', 'created_at' => now()->subHours(10), 'lokasi' => 'Jakarta Selatan'],
        //             (object)['status_text' => 'Paket sampai tujuan', 'created_at' => now()->subHours(12), 'lokasi' => 'Jakarta Selatan'],
        //         ]
        //     ];
        // } else {
        //     $data = null; // Tidak ketemu
        // }

        // 2. Render View Modal Body
        $html = view('components.modal.result.quick-track-result', compact('data'))->render();

        return response()->json(['html' => $html]);
    }

    public function contractSearch(Request $request)
    {
        $id = $request->input('id');
        $keyword = $request->input('keyword');

        if (empty($id) && empty($keyword)) {
            return response()->json([
                'html' => '<div class="text-center text-muted py-4"><i class="bi bi-exclamation-circle text-warning fs-3 mb-2 d-block"></i> Kata kunci atau ID tidak boleh kosong.</div>'
            ]);
        }

        $query = Kontrak::with([
            'pengguna',
            'periode' => function ($q) {
                $q->whereIn('status', [1, 2])->orderBy('periode', 'asc');
            },
            'periode.permohonan',
            'periode.permohonan.jenis_layanan',
            'periode.permohonan.jenis_layanan_parent',
            'periode.permohonan.file_lhu',
            'periode.permohonan.invoice',
            'periode.permohonan.lhu',
            'periode.permohonan.lhu.penyelia_map',
            'periode.permohonan.lhu.penyelia_map.jobs',
            'periode.penyelia',
            'periode.penyelia.penyelia_map',
            'periode.penyelia.penyelia_map.jobs',
            'layanan_jasa',
            'jenisTld',
            'jenis_layanan',
            'jenis_layanan_parent',
            'pelanggan.perusahaan',
            'pengiriman.detail',
            'pengiriman.permohonan',
            'tld_aktif',
            'kontrak_detail',
            'kontrak_detail.tld_1',
            'kontrak_detail.tld_2',
            'rincian_list_tld' => function ($q) {
                $q->whereIn('status', [5, 6]);
            }
        ]);

        if ($id) {
            $idKontrak = decryptor($id);
            $query->where('id_kontrak', $idKontrak);
        } else {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('pelanggan.perusahaan', function ($compQ) use ($keyword) {
                    $compQ->where('nama_perusahaan', 'like', '%' . $keyword . '%');
                })
                ->orWhere('no_kontrak', 'like', '%' . $keyword . '%');
            });
        }

        $contracts = $query->orderBy('created_at', 'desc')->limit(10)->get();

        $html = view('components.dashboard.contract-search-results', compact('contracts'))->render();

        return response()->json([
            'html' => $html
        ]);
    }

    public function contractSearchOptions(Request $request)
    {
        $keyword = $request->input('keyword');
        if (empty($keyword)) {
            return response()->json(['options' => []]);
        }

        $contracts = Kontrak::with(['pelanggan.perusahaan'])
            ->where(function ($query) use ($keyword) {
                $query->whereHas('pelanggan.perusahaan', function ($q) use ($keyword) {
                    $q->where('nama_perusahaan', 'like', '%' . $keyword . '%');
                })
                ->orWhere('no_kontrak', 'like', '%' . $keyword . '%');
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $options = $contracts->map(function ($contract) {
            $companyName = $contract->pelanggan?->perusahaan?->nama_perusahaan ?? 'Perusahaan tidak diketahui';
            return [
                'id' => $contract->kontrak_hash,
                'text' => $companyName . ' (' . $contract->no_kontrak . ')'
            ];
        });

        return response()->json([
            'options' => $options
        ]);
    }

    public function jobsPenyelia(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'monthly');
        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        $jobsQuery = Penyelia::with([
            'dokumenSuratTugas:id_dokumen,id_permohonan,nomer',
            'permohonan.pelanggan.perusahaan:id_perusahaan,nama_perusahaan',
            'permohonan.kontrak:id_kontrak,no_kontrak',
            'penyelia_map.jobs:id_jobs,name'
        ])
            ->whereNot('status', 1);
        
        $applyFilter($jobsQuery);

        $jobs = $jobsQuery->latest() // Urutkan berdasarkan data terbaru
            ->limit(5)
            ->get();

        $tasks = $jobs->map(function ($job) {
            $mainStep = $job->penyelia_map->whereNull('point_jobs');
            $paralelStep = $job->penyelia_map->whereNotNull('point_jobs')->whereIn('status', [1, 2]);

            $stepNow = $mainStep->where('status', 1)->first();

            $stepName = 'Selesai'; // Default value
            if ($job->status == 2) {
                $stepName = 'TTD Manager';
            } elseif ($stepNow) {
                $stepName = $stepNow->jobs->name;
            }

            return [
                'id' => $job->id,
                'nomor_surat' => $job->dokumenSuratTugas?->nomer,
                'nomor_referensi' => $job->permohonan?->kontrak?->no_kontrak,
                'nama_perusahaan' => $job->permohonan?->pelanggan?->perusahaan?->nama_perusahaan,
                'nama_petugas' => $job->permohonan?->pelanggan?->name,
                'periode' => $job->periode === 0 ? 'Zero Check' : "Periode {$job->periode}",
                'current_step' => $stepNow?->order ?? 0,
                'total_step' => $mainStep->count(),
                'step_name' => $stepName,
                'paralel' => $paralelStep
            ];
        });

        $html = view('components.dashboard.jobs-chart', compact('tasks'))->render();

        return response()->json(['html' => $html]);
    }

    public function monitorPenyeliaan(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'monthly');
        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        $jobs = Master_jobs::query()
            ->withCount([
                'penyelia_map' => function ($q) use ($applyFilter) {
                    $q->where('status', 1);
                    $applyFilter($q);
                }
            ])->get();

        // Hasilkan warna acak untuk setiap data
        // $colors = $jobs->map(function ($job) {
        //     // Membuat kode warna hex acak, contoh: #A8D8F7
        //     return sprintf('#%06x', mt_rand(0, 0xFFFFFF));
        // });
        $chartData = array(
            'category' => $jobs->pluck('name'),
            'value' => $jobs->pluck('penyelia_map_count'),
            'color' => $jobs->pluck('color'),
        );

        // cek apakah semua data kosong
        $isEmpty = $chartData['value']->sum() === 0;
        $charts = [];
        if (!$isEmpty) {
            $charts[] = [
                'title' => 'Penyeliaan',
                'id_chart' => 'jobsChart',
                'type' => 'bar',
                'series_name' => 'Penyeliaan',
                'yaxis_title' => 'Jumlah Penyeliaan',
                'height' => 320,
                'stacked' => true,
                'distributed' => true,
                'horizontal' => true,
                'data' => $chartData
            ];
        }

        $html = view('components.dashboard.service-chart', [
            'charts' => $charts,
            'isEmpty' => $isEmpty,
            'title' => 'Monitor',
            'icon' => 'list-check',
            'withFilter' => true,
            'filterDefault' => $dateFilter,
            'idWidget' => 'widget-monitor-penyelia'
        ])->render();
        // $chartData['isEmpty'] = $isEmpty;

        // $html = view('components.dashboard.jobs-chart', [
        //     'chartData' => $chartData
        // ])->render();

        return response()->json(['html' => $html]);
    }

    public function myJobsList(Request $request)
    {
        $user = Auth::user();
        if ($user->hasRole('Staff Penyelia')) {
            $statusJob = [4];
        } else {
            $statusJob = $user->jobs;
        }
        $jobsActive = Penyelia::with([
            'permohonan',
            'permohonan.kontrak',
            'permohonan.pelanggan',
            'penyelia_map',
            'penyelia_map.jobs',
            'penyelia_map.jobs_paralel',
            'petugas',
            'dokumenSuratTugas'
        ])->whereHas('penyelia_map', function ($q) use ($statusJob, $user) {
            $q->whereIn('id_jobs', $statusJob)->where('status', 1)->whereHas('petugas', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            });
        })
            ->whereHas('permohonan.layanan_jasa', function ($q) use ($user) {
                $q->whereIn('satuankerja_id', $user->satuankerja_id ? $user->satuankerja_id : [0]);
            })
            ->limit(5)
            ->get();

        $tasks = $jobsActive->map(function ($job) use ($user) {
            $mainStep = $job->penyelia_map->where('status', 1)->first();
            return [
                'id' => $job->id,
                'id_penyelia' => $job->penyelia_hash,
                'nomor_surat' => $job->dokumenSuratTugas?->nomer,
                'nomor_referensi' => $job->permohonan?->kontrak?->no_kontrak,
                'nama_perusahaan' => $job->permohonan?->pelanggan?->perusahaan?->nama_perusahaan,
                'nama_petugas' => $job->permohonan?->pelanggan?->name,
                'periode' => $job->periode === 0 ? 'Zero Check' : "Periode {$job->periode}",
                'current_step' => $mainStep?->order ?? 0,
                'deadline' => $job->end_date,
                'current_step_name' => $mainStep->jobs ? $mainStep->jobs->name : $mainStep->jobs_paralel->name,
                'status' => 'active'
            ];
        });

        $html = view('components.dashboard.my-jobs', [
            'jobs' => $tasks
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function financeCharts(Request $request)
    {
        $user = Auth::user();
        $dateFilter = $request->input('date_filter', 'monthly');

        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        // --- 1. DATA CASH FLOW (Stacked Bar) ---
        // Logika: Membandingkan total nominal Lunas vs Belum Lunas per Bulan
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $year = date('Y');

        $q = Keuangan::whereYear('created_at', $year)
            ->whereIn('status', [3, 4, 5, 90]);
        $applyFilter($q);

        $query = $q->selectRaw('MONTH(created_at) as month, status, SUM(total_harga) as total')
            ->groupBy('month', 'status')
            ->get();

        $lunasByMonth = $query->where('status', 5)->pluck('total', 'month');

        // Gabungkan total untuk status belum lunas (3, 4, 90) per bulan
        $piutangByMonth = $query->whereIn('status', [3, 4, 90])
            ->groupBy('month')
            ->map(function ($group) {
                return $group->sum('total');
            });

        $lunas = [];
        $piutang = [];

        for ($i = 1; $i <= 12; $i++) {
            // Mengambil data per bulan dan membaginya dengan 1 juta untuk grafik
            $lunas[] = $lunasByMonth->get($i, 0); //( / 1000000);
            $piutang[] = $piutangByMonth->get($i, 0); //( / 1000000);
        }

        $chartData = array(
            'category' => $monthNames,
            'value' => $lunas,
        );

        $chartData2 = array(
            'category' => $monthNames,
            'value' => $piutang,
        );

        $charts = [];
        if (!$query->isEmpty()) {
            $charts[] = [
                'title' => 'Cash Flow',
                'id_chart' => 'cashFlowChart',
                'type' => 'line',
                'series_name' => ['Lunas', 'Piutang'],
                'yaxis_title' => 'Jumlah Lunas',
                'stacked' => true,
                'distributed' => true,
                'format' => 'currency',
                'data' => [$chartData, $chartData2],
            ];
        }

        // $html = view('components.dashboard.finance-charts-panel', compact('cashFlowData'))->render();
        $html = view('components.dashboard.service-chart', [
            'charts' => $charts,
            'isEmpty' => $query->isEmpty(),
            'title' => 'Cash Flow',
            'icon' => 'cash',
            'withFilter' => true,
            'filterDefault' => $dateFilter,
            'idWidget' => 'widget-finance-charts'
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function financeInvActive(Request $request)
    {
        $user = Auth::user();
        $dateFilter = $request->input('date_filter', 'monthly');
        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        // --- 3. DATA STATUS INVOICE (Funnel / Bar Horizontal) ---
        $countsQuery = Keuangan::query() // Menghapus eager loading yang tidak perlu
            ->whereIn('status', [7, 4, 3, 5, 90]);
        $applyFilter($countsQuery);

        $counts = $countsQuery->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $countBelumLunas = $counts->get(3, 0);
        $countVerifikasi = $counts->get(4, 0);
        $countLunas = $counts->get(5, 0);
        $countFaktur = $counts->get(7, 0);
        $countDitolak = $counts->get(90, 0);

        // cek apakah semua data kosong
        $isEmpty = $countBelumLunas === 0 && $countVerifikasi === 0 && $countLunas === 0 && $countFaktur === 0 && $countDitolak === 0;

        // Logika: Menghitung jumlah invoice di setiap tahapan (Faktur -> Bayar -> Verif -> Selesai)
        $funnelData = [
            'categories' => ['Faktur Belum Terbit', 'Menunggu Bayar', 'Perlu Verifikasi', 'Selesai/Lunas', 'Ditolak'],
            'data' => [
                $countFaktur, // Total Faktur
                $countBelumLunas, // Yang belum bayar
                $countVerifikasi,  // Yang sudah bayar tapi belum dicek admin
                $countLunas,  // Yang sudah beres
                $countDitolak // Ditolak
            ],
            'isEmpty' => $isEmpty
        ];

        $html = view('components.dashboard.finance-inv-active', compact('funnelData'))->render();

        return response()->json(['html' => $html]);
    }

    public function financeChartService(Request $request)
    {
        $user = Auth::user();
        $dateFilter = $request->input('date_filter', 'monthly');
        $applyFilter = function ($query, $column = 'created_at') use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, \Carbon\Carbon::today());
            } elseif ($dateFilter === 'weekly') {
                $query->whereBetween($column, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'monthly') {
                $query->whereMonth($column, \Carbon\Carbon::now()->month)->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif ($dateFilter === 'yearly') {
                $query->whereYear($column, \Carbon\Carbon::now()->year);
            } elseif (str_contains($dateFilter, ' to ')) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween($column, [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        };

        $layanan = Master_layanan_jasa::where('status', 1)->get();
        $layananNow = null;

        if ($request->has('jenis_layanan')) {
            $idLayanan = decryptor($request->jenis_layanan);
            // Find the requested service from the collection we already fetched.
            $layananNow = $layanan->firstWhere('id_layanan', $idLayanan);
        }

        // If no service was requested or the requested one wasn't found, default to the first active service.
        if (!$layananNow) {
            $layananNow = $layanan->first();
        }

        $statistik = Master_jenistld::withCount(['permohonan' => function ($query) use ($layananNow, $applyFilter) {
            $query->where('id_layanan', $layananNow->id_layanan)
                ->whereHas('invoice'); // Hanya hitung permohonan yang punya data di tabel keuangan
            $applyFilter($query);
        }])->get()
            ->pluck('permohonan_count', 'name');

        // --- 2. DATA KOMPOSISI LAYANAN (Donut Chart) ---
        // Logika: Total pendapatan berdasarkan jenis TLD
        $serviceData = [
            'labels' => $statistik->keys()->toArray(),
            'series' => array_map('intval', $statistik->values()->toArray()),
        ];

        // apakah semua data kosong
        $isEmpty = $statistik->values()->sum() === 0;
        $serviceData['isEmpty'] = $isEmpty;

        $html = view('components.dashboard.finance-chart-services', compact('serviceData'))->render();

        return response()->json(['html' => $html]);
    }

    public function financeSide()
    {
        // 1. DATA TOP DEBTORS (Siapa yang paling banyak hutang?)
        // Query: Group by perusahaan, Sum nominal where status = unpaid
        $topDebtors = collect([
            (object) [
                'nama_perusahaan' => 'PT. Nusa Lestari Tbk',
                'total_invoice' => 3,
                'total_hutang' => 45000000,
                'persentase' => 80, // Persen dari limit kredit atau max hutang
                'no_hp' => '62812345678'
            ],
            (object) [
                'nama_perusahaan' => 'CV. Beton Perkasa',
                'total_invoice' => 1,
                'total_hutang' => 12500000,
                'persentase' => 40,
                'no_hp' => '62898765432'
            ]
        ]);

        $html = view('components.dashboard.finance-side', compact('topDebtors'))->render();

        return response()->json(['html' => $html]);
    }
}
