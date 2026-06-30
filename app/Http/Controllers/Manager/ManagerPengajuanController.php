<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Master_jobs;
use App\Models\Satuan_kerja;
use App\Models\User;
use App\Models\Penyelia_petugas;

use Auth;

class ManagerPengajuanController extends Controller
{
    // index action
    public function index()
    {
        $data = [
            'title' => 'Invoice',
            'module' => 'manager-pengajuan'
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
                $q->whereIn('status', [1, 2]); // 1 = dikerjakan, 2 = selesai
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
                    'total_dikerjakan' => 0,
                    'total_selesai'    => 0,
                    'total'            => 0,
                    'jobs'             => [],
                    'user_jobs'        => $userJobs,
                ];
                foreach ($userJobs as $ujId) {
                    $pivoted[$uid]['jobs'][$ujId] = [
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

            if ($status === 1) {
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
                'total_dikerjakan' => $p['total_dikerjakan'],
                'total_selesai'    => $p['total_selesai'],
                'total'            => $p['total'],
                'jobs'             => $userJobs,
            ];
            foreach ($userJobs as $job) {
                $jobId = $job;
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
}
