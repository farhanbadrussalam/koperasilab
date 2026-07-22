<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Master_jobs;
use App\Models\Satuan_kerja;
use App\Models\User;
use App\Models\Penyelia_petugas;
use App\Models\Keuangan;
use App\Models\Pengiriman;
use App\Models\Master_ekspedisi;
use Carbon\Carbon;

use Auth;

class ManagerPengajuanController extends Controller
{
    // index action
    public function index()
    {
        $data = [
            'title' => 'Invoice',
            'module' => 'manager-pengajuan',
            'users' => \App\Models\User::where('status', 1)->get()
        ];
        Auth::user()->unreadNotifications()->where('data->event', 'Keuangan')->update(['read_at' => now()]);
        return view('pages.manager.pengajuan.index', $data);
    }

    public function indexSuratTugas()
    {
        $data = [
            'title' => 'Surat tugas',
            'module' => 'manager-suratTugas'
        ];

        Auth::user()->unreadNotifications()->where('data->event', 'SuratTugas')->update(['read_at' => now()]);

        return view('pages.manager.suratTugas', $data);
    }

    public function indexSurpeng()
    {
        $data = [
            'title' => 'Surat Pengantar',
            'module' => 'manager-surpeng'
        ];

        Auth::user()->unreadNotifications()->where('data->event', 'Surpeng')->update(['read_at' => now()]);

        return view('pages.manager.surpeng', $data);
    }

    /**
     * Menampilkan halaman Produktivitas Petugas
     */
    public function indexProduktivitas()
    {
        $masterJobs  = Master_jobs::orderBy('order')->get();
        $satuanKerja = Satuan_kerja::orderBy('name')->get();
        $petugas     = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Staff LHU', 'Staff Penyelia']);
        })->orderBy('name')->get(['id', 'name']);

        $data = [
            'title'       => 'Produktivitas Petugas',
            'module'      => 'manager-produktivitas',
            'masterJobs'  => $masterJobs,
            'satuanKerja' => $satuanKerja,
            'petugas'     => $petugas,
        ];

        return view('pages.manager.produktivitas', $data);
    }

    /**
     * Endpoint AJAX untuk DataTables Produktivitas Petugas.
     * Mengembalikan data agregasi: jumlah job per petugas dipisah per status
     * (1 = sedang dikerjakan, 2 = selesai).
     */
    public function getDataProduktivitas(Request $request)
    {
        // Parameter DataTables
        $draw   = $request->input('draw', 1);
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');

        // Filter tambahan
        $dateRange     = $request->input('date_range');
        $satuanKerjaId = $request->input('satuan_kerja') ? decryptor($request->input('satuan_kerja')) : null;
        $pencarian     = $request->input('pencarian');
        $filterJobId   = $request->input('job_id');

        // Ambil semua jenis pekerjaan aktif untuk pivot
        $masterJobs = Master_jobs::orderBy('order')->get();

        // Query utama: ambil data penugasan menggunakan Eloquent Relations
        $rows = Penyelia_petugas::whereHas('user', function ($q) use ($pencarian, $satuanKerjaId) {
            $q->whereNull('deleted_at');
            if ($satuanKerjaId) {
                $q->whereJsonContains('satuankerja_id', (int) $satuanKerjaId);
            }
            if ($pencarian) {
                $q->where('name', 'like', "%$pencarian%");
            }
        })
            ->whereHas('penyelia_map', function ($q) use ($dateRange, $filterJobId) {
                $q->whereIn('status', [0, 1, 2]); // 0 = ditugaskan, 1 = dikerjakan, 2 = selesai
                if ($dateRange) {
                    $q->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
                }
                if ($filterJobId) {
                    $q->where('id_jobs', $filterJobId);
                }
            })
            ->with(['user', 'penyelia_map.jobs', 'penyelia_map.penyelia.permohonan'])
            ->get();

        // Pivot: kelompokkan per petugas, pisahkan status dikerjakan vs selesai
        $pivoted = [];
        foreach ($rows as $row) {
            $user = $row->user;
            $map  = $row->penyelia_map;
            $job  = $map ? $map->jobs : null;

            if (!$user || !$map || !$job) {
                continue;
            }

            $uid = $user->id;
            $userJobs = is_array($user->jobs) ? $user->jobs : [];

            // Pastikan pekerjaan yang dikerjakan termasuk dalam jobs yang ditugaskan ke petugas
            $jobId = $job->id_jobs;
            if (!in_array($jobId, $userJobs)) {
                continue;
            }

            if ($filterJobId && $jobId != $filterJobId) {
                continue;
            }

            if (!isset($pivoted[$uid])) {
                $pivoted[$uid] = [
                    'user_id'          => $uid,
                    'nama_petugas'     => $user->name,
                    'jabatan'          => $user->jabatan ?? '-',
                    'total_ditugaskan' => 0,
                    'total_dikerjakan' => 0,
                    'total_selesai'    => 0,
                    'total'            => 0,
                    'jobs'             => [],
                    'user_jobs'        => $userJobs,
                ];
                foreach ($userJobs as $ujId) {
                    $pivoted[$uid]['jobs'][$ujId] = [
                        'ditugaskan' => 0,
                        'dikerjakan' => 0,
                        'selesai'    => 0,
                    ];
                }
            }

            $status = (int) $map->status;

            // Jika permohonan memiliki TLD (is_have_tld == 1), hitung berdasarkan jumlah TLD
            $penyelia = $map->penyelia;
            $permohonan = $penyelia ? $penyelia->permohonan : null;
            $tldCount = 1;

            if ($permohonan) {
                $tldCount = ((int) $permohonan->jumlah_pengguna) + ((int) $permohonan->jumlah_kontrol);
                if ($tldCount <= 0) $tldCount = 1;
            }

            if ($status === 0) {
                $pivoted[$uid]['jobs'][$jobId]['ditugaskan'] += $tldCount;
                $pivoted[$uid]['total_ditugaskan'] += $tldCount;
                $pivoted[$uid]['total'] += $tldCount;
            } elseif ($status === 1) {
                $pivoted[$uid]['jobs'][$jobId]['dikerjakan'] += $tldCount;
                $pivoted[$uid]['total_dikerjakan'] += $tldCount;
                $pivoted[$uid]['total'] += $tldCount;
            } elseif ($status === 2) {
                if ($map->done_by === $user->id) {
                    $pivoted[$uid]['jobs'][$jobId]['selesai'] += $tldCount;
                    $pivoted[$uid]['total_selesai'] += $tldCount;
                    $pivoted[$uid]['total'] += $tldCount;
                }
            }
        }

        $pivoted = array_values($pivoted);

        // Abaikan petugas yang memiliki total == 0
        $pivoted = array_filter($pivoted, function ($p) {
            return $p['total'] > 0;
        });
        $pivoted = array_values($pivoted);

        // Filter pencarian dari DataTables search box
        if ($search) {
            $pivoted = array_filter($pivoted, function ($p) use ($search) {
                return stripos($p['nama_petugas'], $search) !== false
                    || stripos($p['jabatan'], $search) !== false;
            });
            $pivoted = array_values($pivoted);
        }

        $recordsTotal    = count($pivoted);
        $recordsFiltered = $recordsTotal;

        $pagedData = array_slice($pivoted, $start, $length);
        // Format DataTables — setiap job punya _s (selesai) dan _d (dikerjakan)
        $tableData = [];
        foreach ($pagedData as $p) {
            $userJobs = $p['user_jobs'] ?? [];
            $row = [
                'avatar'           => renderUserAvatar($p['user_id'], false),
                'nama_petugas'     => $p['nama_petugas'],
                'jabatan'          => $p['jabatan'],
                'total_ditugaskan' => $p['total_ditugaskan'],
                'total_dikerjakan' => $p['total_dikerjakan'],
                'total_selesai'    => $p['total_selesai'],
                'total'            => $p['total'],
                'jobs'             => $userJobs,
            ];
            foreach ($userJobs as $job) {
                $jobId = $job;
                $row['job_' . $jobId . '_t'] = $p['jobs'][$jobId]['ditugaskan'] ?? 0;
                $row['job_' . $jobId . '_s'] = $p['jobs'][$jobId]['selesai'] ?? 0;
                $row['job_' . $jobId . '_d'] = $p['jobs'][$jobId]['dikerjakan'] ?? 0;
            }
            $tableData[] = $row;
        }

        // Summary stats
        $totalSelesai    = array_sum(array_column($pivoted, 'total_selesai'));
        $totalDikerjakan = array_sum(array_column($pivoted, 'total_dikerjakan'));
        $totalPetugas    = count($pivoted);
        $avgPerPetugas   = $totalPetugas > 0 ? round($totalSelesai / $totalPetugas, 1) : 0;
        $topPerformer    = $totalPetugas > 0
            ? collect($pivoted)->sortByDesc('total_selesai')->first()
            : null;

        // Chart top 10 berdasarkan total selesai (grouped bar: selesai + dikerjakan)
        $top10           = collect($pivoted)->sortByDesc('total_selesai')->take(10);
        $chartLabels     = $top10->pluck('nama_petugas')->values()->toArray();
        $chartSelesai    = $top10->pluck('total_selesai')->values()->toArray();
        $chartDikerjakan = $top10->pluck('total_dikerjakan')->values()->toArray();

        return response()->json([
            'draw'            => (int) $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $tableData,
            'summary'         => [
                'total_petugas'    => $totalPetugas,
                'total_selesai'    => $totalSelesai,
                'total_dikerjakan' => $totalDikerjakan,
                'avg_per_petugas'  => $avgPerPetugas,
                'top_performer'    => $topPerformer ? $topPerformer['nama_petugas'] : '-',
                'top_total'        => $topPerformer ? $topPerformer['total_selesai'] : 0,
            ],
            'chart' => [
                'labels'      => $chartLabels,
                'values'      => $chartSelesai,
                'values_prog' => $chartDikerjakan,
            ],
        ]);
    }

    /**
     * Menampilkan halaman Produktivitas Keuangan (Invoice & Pengiriman)
     */
    public function indexProduktivitasKeuangan()
    {
        $data = [
            'title'           => 'Produktivitas Keuangan',
            'module'          => 'manager-produktivitas-keuangan',
            'dataUrl'         => route('manager.produktivitas.keuangan.getData'),
        ];

        return view('pages.manager.produktivitas-keuangan', $data);
    }

    /**
     * AJAX endpoint untuk data Produktivitas Keuangan.
     * Mengembalikan: summary cards, tren bulanan, breakdown status invoice,
     * breakdown per ekspedisi, dan tabel invoice (dengan pagination).
     */
    public function getDataProduktivitasKeuangan(Request $request)
    {
        $draw       = $request->input('draw', 1);
        $start      = (int) $request->input('start', 0);
        $length     = (int) $request->input('length', 10);
        $dateRange  = $request->input('date_range');        // [tgl_awal, tgl_akhir]
        $petugasId  = $request->input('petugas_id');        // ID user petugas keuangan
        $statusFilter = $request->input('status_invoice');  // filter status invoice

        // ── Helper: terapkan filter tanggal ke query ──────────────────────────
        $applyDateRange = function ($query, $col = 'created_at') use ($dateRange) {
            if ($dateRange && is_array($dateRange) && count($dateRange) === 2) {
                $query->whereBetween($col, [
                    Carbon::parse($dateRange[0])->startOfDay(),
                    Carbon::parse($dateRange[1])->endOfDay(),
                ]);
            }
        };

        // ── 1. Query Invoice ──────────────────────────────────────────────────
        $invoiceQuery = Keuangan::query()
            ->when($petugasId, fn($q) => $q->where('created_by', $petugasId))
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter));
        $applyDateRange($invoiceQuery);

        // Summary invoice
        $invoiceStats = (clone $invoiceQuery)
            ->selectRaw('status, count(*) as total, coalesce(sum(total_harga),0) as nilai')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalInvoice   = $invoiceStats->sum('total');
        $invoiceLunas   = (int) ($invoiceStats->get(5)?->total ?? 0);
        $invoiceDraft   = (int) ($invoiceStats->get(1)?->total ?? 0);
        $invoiceMenunggu = (int) ($invoiceStats->get(3)?->total ?? 0);
        $invoiceDitolak = (int) ($invoiceStats->get(90)?->total ?? 0);
        $nilaiTotal     = $invoiceStats->sum('nilai');
        $nilaiLunas     = (int) ($invoiceStats->get(5)?->nilai ?? 0);

        // ── 2. Query Pengiriman ───────────────────────────────────────────────
        $pengirimanQuery = Pengiriman::query();
        $applyDateRange($pengirimanQuery, 'send_at');

        $pengirimanStats = (clone $pengirimanQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalPengiriman    = $pengirimanStats->sum('total');
        $pengirimanDikirim  = (int) ($pengirimanStats->get(1)?->total ?? 0);
        $pengirimanDiterima = (int) ($pengirimanStats->get(3)?->total ?? 0);

        // ── 3. Tren 12 bulan — Invoice ────────────────────────────────────────
        $tren12 = collect();
        for ($i = 11; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $tren12->push([
                'label'      => $bulan->translatedFormat('M Y'),
                'bulan'      => $bulan->month,
                'tahun'      => $bulan->year,
                'invoice'    => 0,
                'pengiriman' => 0,
            ]);
        }

        $invoiceTren = Keuangan::query()
            ->when($petugasId, fn($q) => $q->where('created_by', $petugasId))
            ->whereBetween('created_at', [Carbon::now()->subMonths(11)->startOfMonth(), Carbon::now()->endOfMonth()])
            ->selectRaw('MONTH(created_at) as bulan, YEAR(created_at) as tahun, count(*) as total')
            ->groupBy('bulan', 'tahun')
            ->get()
            ->keyBy(fn($r) => $r->tahun . '-' . $r->bulan);

        $pengirimanTren = Pengiriman::query()
            ->whereBetween('send_at', [Carbon::now()->subMonths(11)->startOfMonth(), Carbon::now()->endOfMonth()])
            ->selectRaw('MONTH(send_at) as bulan, YEAR(send_at) as tahun, count(*) as total')
            ->groupBy('bulan', 'tahun')
            ->get()
            ->keyBy(fn($r) => $r->tahun . '-' . $r->bulan);

        $trenLabels      = [];
        $trenInvoice     = [];
        $trenPengiriman  = [];
        foreach ($tren12 as $t) {
            $key = $t['tahun'] . '-' . $t['bulan'];
            $trenLabels[]     = $t['label'];
            $trenInvoice[]    = (int) ($invoiceTren->get($key)?->total ?? 0);
            $trenPengiriman[] = (int) ($pengirimanTren->get($key)?->total ?? 0);
        }

        // ── 4. Breakdown Pengiriman per Ekspedisi ─────────────────────────────
        $ekspedisiQuery = Pengiriman::query()
            ->with('ekspedisi')
            ->selectRaw('id_ekspedisi, status, count(*) as total')
            ->groupBy('id_ekspedisi', 'status');
        $applyDateRange($ekspedisiQuery, 'send_at');

        $ekspedisiBreakdown = $ekspedisiQuery->get()
            ->groupBy('id_ekspedisi')
            ->map(function ($rows) {
                $ekspedisi = $rows->first()->ekspedisi;
                return [
                    'nama'      => $ekspedisi?->name ?? 'Tidak Diketahui',
                    'total'     => $rows->sum('total'),
                    'dikirim'   => (int) ($rows->firstWhere('status', 1)?->total ?? 0),
                    'diterima'  => (int) ($rows->firstWhere('status', 3)?->total ?? 0),
                ];
            })
            ->sortByDesc('total')
            ->values();

        // ── 5. Tabel Invoice (DataTables server-side) ─────────────────────────
        $tableQuery = Keuangan::query()
            ->when($petugasId, fn($q) => $q->where('created_by', $petugasId))
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter));
        $applyDateRange($tableQuery);

        $recordsTotal    = (clone $tableQuery)->count();
        $recordsFiltered = $recordsTotal;

        $invoiceRows = (clone $tableQuery)
            ->with(['permohonan.layanan_jasa', 'permohonan.pelanggan', 'metode_pembayaran'])
            ->latest('created_at')
            ->skip($start)
            ->take($length)
            ->get();

        $statusLabel = [
            1  => ['label' => 'Draft',             'color' => 'secondary'],
            2  => ['label' => 'Proses',             'color' => 'info'],
            3  => ['label' => 'Menunggu Bayar',     'color' => 'warning'],
            4  => ['label' => 'Verifikasi Bayar',   'color' => 'primary'],
            5  => ['label' => 'Lunas',              'color' => 'success'],
            90 => ['label' => 'Ditolak',            'color' => 'danger'],
        ];

        $tableData = $invoiceRows->map(function ($inv) use ($statusLabel) {
            $status = (int) $inv->status;
            $sl     = $statusLabel[$status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
            $permohonan = $inv->permohonan;

            return [
                'no_invoice'     => $inv->no_invoice ?? '-',
                'pelanggan'      => $permohonan?->pelanggan?->name ?? '-',
                'layanan'        => $permohonan?->layanan_jasa?->nama_layanan ?? '-',
                'total_harga'    => $inv->total_harga ?? 0,
                'status'         => $status,
                'status_label'   => $sl['label'],
                'status_color'   => $sl['color'],
                'metode'         => $inv->metode_pembayaran?->name ?? '-',
                'tanggal'        => $inv->created_at?->format('d/m/Y') ?? '-',
                'paid_at'        => $inv->paid_at ? Carbon::parse($inv->paid_at)->format('d/m/Y') : '-',
            ];
        })->values()->toArray();

        return response()->json([
            'draw'            => (int) $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $tableData,
            'summary' => [
                'total_invoice'      => $totalInvoice,
                'invoice_lunas'      => $invoiceLunas,
                'invoice_draft'      => $invoiceDraft,
                'invoice_menunggu'   => $invoiceMenunggu,
                'invoice_ditolak'    => $invoiceDitolak,
                'nilai_total'        => $nilaiTotal,
                'nilai_lunas'        => $nilaiLunas,
                'total_pengiriman'   => $totalPengiriman,
                'pengiriman_dikirim' => $pengirimanDikirim,
                'pengiriman_diterima' => $pengirimanDiterima,
            ],
            'chart' => [
                'labels'      => $trenLabels,
                'invoice'     => $trenInvoice,
                'pengiriman'  => $trenPengiriman,
            ],
            'ekspedisi_breakdown' => $ekspedisiBreakdown,
            'invoice_breakdown' => [
                ['label' => 'Draft',           'total' => $invoiceDraft,    'color' => '#6c757d'],
                ['label' => 'Menunggu Bayar',  'total' => $invoiceMenunggu, 'color' => '#f6c23e'],
                ['label' => 'Lunas',           'total' => $invoiceLunas,    'color' => '#1cc88a'],
                ['label' => 'Ditolak',         'total' => $invoiceDitolak,  'color' => '#e74a3b'],
            ],
        ]);
    }
}
