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

        $fileName = 'Produktivitas_Petugas';
        if ($startDate && $endDate) {
            if ($startDate === $endDate) {
                $fileName .= '_' . \Carbon\Carbon::parse($startDate)->format('d-m-Y');
            } else {
                $fileName .= '_' . \Carbon\Carbon::parse($startDate)->format('d-m-Y') . '_sd_' . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
            }
        } else {
            $fileName .= '_' . now()->format('d-m-Y');
        }
        $fileName .= '.xlsx';

        return $this->exportService->download(
            $fileName,
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
        $petugasId = $request->input('petugas_id');
        $statusFilter = $request->input('status_invoice');

        // Contoh: Mengambil data Keuangan dengan relasi
        $query = Keuangan::with([
            'permohonan.pelanggan.perusahaan',
            'permohonan.jenisTld',
            'permohonan.kontrak',
            'usersig',
            'diskon'
        ])->orderBy('created_at', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($startDate)->startOfDay(),
                \Carbon\Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        if ($petugasId) {
            $query->where('created_by', $petugasId);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $headings = [
            'No Invoice',
            'No Kontrak',
            'Tipe Pengajuan',
            'Jenis Layanan',
            'Jenis TLD',
            'Jumlah TLD',
            'Periode Pemakaian',
            'Total Harga',
            'Diskon',
            'Harga PPN',
            'Harga PPH',
            'Grand Total',
            'Status Pembayaran',
            'Pelanggan',
            'Perusahaan',
            'TTD By',
            'Tanggal Dibuat',
            'Tanggal Diverifikasi',
            'Tanggal Dibayar'
        ];

        // Memetakan struktur setiap baris
        $mapRow = function ($row) {
            $permohonan = $row->permohonan;
            $pelanggan = $permohonan ? $permohonan->pelanggan : null;
            $perusahaan = $pelanggan ? $pelanggan->perusahaan : null;

            $jumlahTld = ($permohonan->jumlah_pengguna ?? 0) + ($permohonan->jumlah_kontrol ?? 0);

            $periodePemakaian = '-';
            if ($permohonan && is_array($permohonan->periode_pemakaian) && count($permohonan->periode_pemakaian) > 0) {
                $periods = $permohonan->periode_pemakaian;
                $firstPeriod = $periods[0];
                $lastPeriod = end($periods);

                $start = '';
                if (is_array($firstPeriod) && isset($firstPeriod['start_date'])) {
                    $start = convert_date($firstPeriod['start_date'], 2);
                } elseif (is_string($firstPeriod)) {
                    $start = $firstPeriod;
                }

                $end = '';
                if (is_array($lastPeriod) && isset($lastPeriod['end_date'])) {
                    $end = convert_date($lastPeriod['end_date'], 2);
                } elseif (is_string($lastPeriod)) {
                    $end = $lastPeriod;
                } else {
                    $end = convert_date($firstPeriod['end_date'], 2);
                }

                if ($start && $end) {
                    $periodePemakaian = $start . ' sd ' . $end;
                } elseif ($start) {
                    $periodePemakaian = $start;
                }
            }

            $calc = calculateInvoice($row->total_harga ?? 0, $row->diskon, $row->ppn ?? 0, $row->pph ?? 0);

            $statusPembayaran = 'Belum Lunas';
            if ($row->status == 5) {
                $statusPembayaran = 'Lunas';
            } elseif ($row->status == 4) {
                $statusPembayaran = 'Proses';
            } elseif ($row->status == 3) {
                $statusPembayaran = 'Menunggu Bayar';
            } elseif ($row->status == 1) {
                $statusPembayaran = 'Draft';
            } elseif ($row->status == 90) {
                $statusPembayaran = 'Ditolak';
            }

            return [
                $row->no_invoice ?? '-',
                $permohonan->kontrak->no_kontrak ?? '-',
                ($permohonan && stripos($permohonan->tipe_kontrak, 'adendum') !== false) ? 'Adendum' : '',
                $permohonan->layanan ?? '-',
                $permohonan && $permohonan->jenisTld ? $permohonan->jenisTld->name : '-',
                $jumlahTld,
                $periodePemakaian,
                $row->total_harga ?? 0,
                $calc['total_diskon'] ?? 0,
                $calc['jumPpn'] ?? 0,
                $calc['jumPph'] ?? 0,
                $calc['grand_total'] ?? 0,
                $statusPembayaran,
                $pelanggan->name ?? '-',
                $perusahaan->nama_perusahaan ?? '-',
                $row->usersig->name ?? '-',
                $row->created_at ? convert_date($row->created_at, 2) : '-',
                $row->verif_at ? convert_date($row->verif_at, 2) : '-',
                $row->paid_at ? convert_date($row->paid_at, 2) : '-'
            ];
        };

        $fileName = 'Produktivitas_Keuangan';
        if ($startDate && $endDate) {
            if ($startDate === $endDate) {
                $fileName .= '_' . convert_date($startDate, 5);
            } else {
                $fileName .= '_' . convert_date($startDate, 5) . '_sd_' . convert_date($endDate, 5);
            }
        } else {
            $fileName .= '_' . now()->format('d-m-Y');
        }
        $fileName .= '.xlsx';

        $columnFormats = [
            'H' => '"Rp "* #,##0',
            'I' => '"Rp "* #,##0',
            'J' => '"Rp "* #,##0',
            'K' => '"Rp "* #,##0',
            'L' => '"Rp "* #,##0',
        ];

        return $this->exportService->download(
            $fileName,
            $query,
            $headings,
            'Data Keuangan',
            $mapRow,
            \Maatwebsite\Excel\Excel::XLSX,
            $columnFormats
        );
    }
}
