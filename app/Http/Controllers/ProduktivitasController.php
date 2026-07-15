<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExportService;
use App\Models\Permohonan_tld;
use App\Models\Keuangan;

class ProduktivitasController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Halaman index petugas
     */
    public function indexPetugas()
    {
        // View untuk halaman produktivitas petugas
        return view('produktivitas.petugas');
    }

    /**
     * Halaman index keuangan
     */
    public function indexKeuangan()
    {
        // View untuk halaman produktivitas keuangan
        return view('produktivitas.keuangan');
    }

    /**
     * Export Produktivitas Petugas ke Excel.
     */
    public function exportPetugas(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $dateRange = ($startDate && $endDate) ? [$startDate, $endDate] : null;
        $satuanKerjaId = $request->input('satuan_kerja') ? decryptor($request->input('satuan_kerja')) : null;
        $pencarian     = $request->input('pencarian');

        $rows = \App\Models\Penyelia_petugas::whereHas('user', function ($q) use ($pencarian, $satuanKerjaId) {
            $q->whereNull('deleted_at');
            if ($satuanKerjaId) {
                $q->whereJsonContains('satuankerja_id', (int) $satuanKerjaId);
            }
            if ($pencarian) {
                $q->where('name', 'like', "%$pencarian%");
            }
        })
            ->whereHas('penyelia_map', function ($q) use ($dateRange) {
                $q->whereIn('status', [0, 1, 2]); // 0 = ditugaskan, 1 = dikerjakan, 2 = selesai
                if ($dateRange) {
                    $q->whereBetween('created_at', [
                        \Carbon\Carbon::parse($dateRange[0])->startOfDay(),
                        \Carbon\Carbon::parse($dateRange[1])->endOfDay(),
                    ]);
                }
            })
            ->with(['user', 'penyelia_map.jobs', 'penyelia_map.penyelia.permohonan'])
            ->get();

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
            $jobId = $job->id_jobs;

            if (!in_array($jobId, $userJobs)) {
                continue;
            }

            if (!isset($pivoted[$uid])) {
                $satuanKerjaNames = $user->satuankerja->pluck('name')->implode(', ');
                $pivoted[$uid] = [
                    'nama_petugas'     => $user->name,
                    'satuan_kerja'     => $satuanKerjaNames,
                    'nama_jobs'        => [],
                    'total_ditugaskan' => 0,
                    'total_dikerjakan' => 0,
                    'total_selesai'    => 0,
                    'total'            => 0,
                ];
            }

            $jobName = $job->name ?? "Job $jobId";
            if (!in_array($jobName, $pivoted[$uid]['nama_jobs'])) {
                $pivoted[$uid]['nama_jobs'][] = $jobName;
            }

            $status = (int) $map->status;
            $penyelia = $map->penyelia;
            $permohonan = $penyelia ? $penyelia->permohonan : null;
            $tldCount = 1;

            if ($permohonan) {
                $tldCount = ((int) $permohonan->jumlah_pengguna) + ((int) $permohonan->jumlah_kontrol);
                if ($tldCount <= 0) $tldCount = 1;
            }

            if ($status === 0) {
                $pivoted[$uid]['total_ditugaskan'] += $tldCount;
                $pivoted[$uid]['total'] += $tldCount;
            } elseif ($status === 1) {
                $pivoted[$uid]['total_dikerjakan'] += $tldCount;
                $pivoted[$uid]['total'] += $tldCount;
            } elseif ($status === 2) {
                if ($map->done_by === $user->id) {
                    $pivoted[$uid]['total_selesai'] += $tldCount;
                    $pivoted[$uid]['total'] += $tldCount;
                }
            }
        }

        $pivoted = array_filter($pivoted, function ($p) {
            return $p['total'] > 0;
        });

        $collection = collect(array_values($pivoted))->sortBy('nama_petugas');

        $headings = [
            'Nama Petugas',
            'Satuan Kerja',
            'Nama Jobs',
            'Jumlah Ditugaskan',
            'Jumlah Proses',
            'Jumlah Selesai',
            'Jumlah Total'
        ];

        // Memetakan struktur setiap baris ke Excel
        $mapRow = function ($row) {
            return [
                $row['nama_petugas'],
                $row['satuan_kerja'],
                implode(', ', $row['nama_jobs']),
                $row['total_ditugaskan'],
                $row['total_dikerjakan'],
                $row['total_selesai'],
                $row['total'],
            ];
        };

        return $this->exportService->download(
            'Produktivitas_Petugas.xlsx',
            $collection,
            $headings,
            'Data Petugas',
            $mapRow
        );
    }

    /**
     * Export Produktivitas Keuangan ke Excel.
     */
    public function exportKeuangan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Contoh: Mengambil data Keuangan dengan relasi
        $query = Keuangan::with(['permohonan'])->orderBy('created_at', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($startDate)->startOfDay(),
                \Carbon\Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        $headings = [
            'No Invoice',
            'ID Permohonan',
            'PPN',
            'PPH',
            'Total Harga',
            'Status Pembayaran',
            'Tanggal Bayar'
        ];

        // Memetakan struktur setiap baris
        $mapRow = function ($row) {
            $status = $row->status == 1 ? 'Lunas' : 'Belum Lunas';
            
            return [
                $row->no_invoice ?? '-',
                $row->id_permohonan ?? '-',
                $row->ppn ?? 0,
                $row->pph ?? 0,
                $row->total_harga ?? 0,
                $status,
                $row->paid_at ?? '-',
            ];
        };

        return $this->exportService->download(
            'Produktivitas_Keuangan.xlsx',
            $query,
            $headings,
            'Data Keuangan',
            $mapRow
        );
    }
}
