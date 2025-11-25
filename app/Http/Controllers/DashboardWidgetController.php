<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\Keuangan;
use App\Models\Kontrak;
use App\Models\Master_tld;
use App\Models\Penyelia;

use Auth;

class DashboardWidgetController extends Controller
{
    // Widget Summary Card
    public function summaryCards(Request $request){
        $jenisCard = $request->has('jenis') ? $request->jenis : null;
        $user = Auth::user();

        switch ($jenisCard) {
            case 'permohonan':
                $counts = Permohonan::query()
                    ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                        $q->where('created_by', $user->id);
                    })
                    ->whereIn('status', [1, 2, 3, 4, 5, 90])
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
                        ['text' => 'Verifikasi', 'icon' => 'bi-shield-check', 'count' => $countVerifikasi, 'color' => 'text-warning-emphasis'],
                        ['text' => 'Ditolak', 'icon' => 'bi-x-circle', 'count' => $countDitolak, 'color' => 'text-danger'],
                    ],
                    'color' => 'text-info',
                ])->render();
                break;
            case 'pembayaran':
                $counts = Keuangan::query()
                            ->with('permohonan')
                            ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                                $q->whereHas('permohonan', function ($q) use ($user) {
                                    $q->where('created_by', $user->id);
                                });
                            })
                            ->whereIn('status', [3, 5, 90])
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
                        array('text' => 'Belum Lunas', 'icon' => 'bi-exclamation-circle', 'count' => $countBelumLunas , 'color' => 'text-warning-emphasis'),
                        array('text' => 'Lunas', 'icon' => 'bi-check-circle-fill', 'count' => $countLunas, 'color' => 'text-success'),
                        array('text' => 'Ditolak', 'icon' => 'bi-dash-circle', 'count' => $countDitolak, 'color' => 'text-danger'),
                    ],
                    'color'=>'text-warning-emphasis'
                ])->render();
                break;
            case 'kontrak':
                $counts = Kontrak::query()
                            ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                                $q->where('id_pelanggan', $user->id);
                            })
                            ->selectRaw('status, count(*) as total')
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
                    'color' => 'text-secondary'
                ])->render();
                break;
            case 'tld':
                $count = Master_tld::query()
                        ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                            $q->where('kepemilikan', $user->id_perusahaan);
                        })
                        ->selectRaw('status, count(*) as total')
                        ->groupBy('status')
                        ->pluck('total', 'status');

                $countTersedia = $count->get(0, 0);
                $countTerpakai = $count->get(1, 0);

                $countTld = Master_tld::query()
                        ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                            $q->where('kepemilikan', $user->id_perusahaan);
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
                    'color' => 'text-secondary'
                ])->render();
                break;
            case 'penyelia':
                $counts = Penyelia::query()
                            ->with('permohonan')
                            ->when($user->hasRole('Pelanggan'), function ($q) use ($user) {
                                $q->whereHas('permohonan', function ($q) use ($user) {
                                    $q->where('created_by', $user->id);
                                });
                            })
                            ->selectRaw('status, count(*) as total')
                            ->groupBy('status')
                            ->pluck('total', 'status');

                $countBaru = $counts->get(1, 0);
                $countTTD = $counts->get(2, 0);
                $countProsesLab = $counts->get(10, 0);
                $countSelesai = $counts->get(3, 0);
                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-people-fill',
                    'text' => 'Penyelia',
                    'type' => 'list',
                    'count' => [
                        array('text' => 'Baru', 'icon' => 'bi-person-plus-fill', 'count' => $countBaru, 'color' => 'text-primary'),
                        array('text' => 'TTD', 'icon' => 'bi-pencil-square', 'count' => $countTTD, 'color' => 'text-warning-emphasis'),
                        array('text' => 'Proses Lab', 'icon' => 'bi-hourglass-split', 'count' => $countProsesLab, 'color' => 'text-info'),
                        array('text' => 'Selesai', 'icon' => 'bi-check-circle-fill', 'count' => $countSelesai, 'color' => 'text-success'),
                    ],
                    'color' => 'text-secondary'
                ])->render();
                break;
            case 'petugaslab':
                $html = view('components.dashboard.summary-cards', [
                    'icon' => 'bi-flask',
                    'text' => 'Petugas Lab',
                    'type' => 'list',
                    'count' => [
                        array('text' => 'Tersedia', 'icon' => 'bi-check-circle-fill', 'count' => 0, 'color' => 'text-success'),
                        array('text' => 'Bertugas', 'icon' => 'bi-hourglass-split', 'count' => 0, 'color' => 'text-info'),
                    ],
                    'color' => 'text-secondary'
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
}
