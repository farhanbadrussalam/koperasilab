@extends('layouts.main')
@php
    $dashboardActive = true;
    $user = auth()->user();
    $newPic = null;

    $rolePelanggan = $user->hasRole('Pelanggan');
    // status 2 = PIC tidak aktif
    if ($rolePelanggan && $user->status == 2) {
        $dashboardActive = false;
        // Cari PIC baru yang aktif (status 1) di perusahaan yang sama.
        // Pastikan relasi 'perusahaan' dan 'pengguna' sudah terdefinisi di model User.
        if ($user->perusahaan) {
            $newPic = $user->perusahaan->pic->name;
        }
    }
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Selamat Datang, {{ auth()->user()->name }}</h1>
    </div>

    @if($dashboardActive)
    <div class="row">
        <div class="col-lg-8">
            <div class="row g-2 mb-3">
                @canany(['Permohonan/pengajuan', 'Staff/permohonan', 'Manager/pengajuan'])
                <div id="widget-summary-permohonan"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="permohonan">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Staff/keuangan','Permohonan/pengajuan'])
                <div id="widget-summary-pembayaran"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="pembayaran">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Kontrak'])
                <div id="widget-summary-kontrak"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="kontrak">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Tld'])
                <div id="widget-summary-tld"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="tld">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Staff/penyelia'])
                <div id="widget-summary-penyelia"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="penyelia">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Staff/lhu/petugas'])
                <div id="widget-summary-petugaslab"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="petugaslab">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan
            </div>
            @canany(['Permohonan/pengajuan', 'Staff/permohonan', 'Manager/pengajuan'])
            {{-- <x-dashboard.pending-request :requests="[
                array(
                    'id' => 1,
                    'no_tiket' => '#REQ-2025-001',
                    'jenis_layanan' => 'TLD',
                    'tanggal_pengajuan' => '2025-11-20',
                    'status' => 'pending_admin' // Status Menunggu
                ),
                array(
                    'id' => 2,
                    'no_tiket' => '#REQ-2025-002',
                    'jenis_layanan' => 'KOP',
                    'tanggal_pengajuan' => '2025-11-18',
                    'status' => 'revisi' // Status Revisi
                ),
                array(
                    'id' => 3,
                    'no_tiket' => '#REQ-2025-003',
                    'jenis_layanan' => 'TLD',
                    'tanggal_pengajuan' => '2025-11-15',
                    'status' => 'pending_verification' // Status Menunggu Verifikasi
                ),
                array(
                    'id' => 4,
                    'no_tiket' => '#REQ-2025-004',
                    'jenis_layanan' => 'KOP',
                    'tanggal_pengajuan' => '2025-11-10',
                    'status' => 'pending_admin' // Status Menunggu
                )
            ]" /> --}}
            <div id="widget-statistics"
                class="ajax-widget"
                data-url="dashboard/widgets/statistics-layanan"
                data-jenis="statisticsLayanan">

                <x-dashboard.skeleton.service-chart-skeleton />
            </div>
            @endcan
            @canany(['Kontrak'])
                {{-- <x-dashboard.active-contracts :contracts="[
                    array(
                        'id' => 1,
                        'nomor_kontrak' => 'C00123',
                        'jenis_layanan' => 'Uji Emisi Udara (TLD)',
                        'nama_perusahaan' => 'PT. Maju Mundur Cantik',
                        'tgl_mulai' => '2025-01-01',
                        'tgl_selesai' => '2025-12-31',
                        'periode_berjalan' => 2,
                        'total_periode' => 4, // 50% Progress
                        'status_bayar' => 'unpaid', // Akan muncul tombol bayar & badge kuning
                        'status_lab' => 'Sampling Selesai'
                    ),
                    array(
                        'id' => 2,
                        'nomor_kontrak' => 'C00124',
                        'jenis_layanan' => 'Uji Kebisingan (KOP)',
                        'nama_perusahaan' => 'CV. Sejahtera Selalu',
                        'tgl_mulai' => '2025-03-15',
                        'tgl_selesai' => '2026-03-14',
                        'periode_berjalan' => 1,
                        'total_periode' => 2, // 50% Progress
                        'status_bayar' => 'paid', // Tidak muncul tombol bayar & badge hijau
                        'status_lab' => 'Dalam Proses Lab'
                    ),
                    array(
                        'id' => 3,
                        'nomor_kontrak' => 'C00125',
                        'jenis_layanan' => 'Uji Kebisingan (KOP)',
                        'nama_perusahaan' => 'CV. Sejahtera Selalu',
                        'tgl_mulai' => '2025-03-15',
                        'tgl_selesai' => '2026-03-14',
                        'periode_berjalan' => 1,
                        'total_periode' => 2, // 50% Progress
                        'status_bayar' => 'paid', // Tidak muncul tombol bayar & badge hijau
                        'status_lab' => 'Dalam Proses Lab'
                    ),
                    array(
                        'id' => 4,
                        'nomor_kontrak' => 'C00126',
                        'jenis_layanan' => 'Uji Kebisingan (KOP)',
                        'nama_perusahaan' => 'CV. Sejahtera Selalu',
                        'tgl_mulai' => '2025-03-15',
                        'tgl_selesai' => '2026-03-14',
                        'periode_berjalan' => 1,
                        'total_periode' => 2, // 50% Progress
                        'status_bayar' => 'paid', // Tidak muncul tombol bayar & badge hijau
                        'status_lab' => 'Dalam Proses Lab'
                    )
                ]" /> --}}
            @endcan


            @canany(['Staff/penyelia', 'Staff/lhu'])
            <div id="widget-monitor-penyelia"
                class="ajax-widget"
                data-url="dashboard/widgets/monitorPenyeliaan">

                <x-dashboard.skeleton.service-chart-skeleton />
            </div>
            @endcan
        </div>
        <div class="col-lg-4">
            <div class="sticky-sidebar">
                <x-dashboard.time-cards />
                @canany(['Permohonan/pengajuan', 'Staff/pengiriman'])
                <div id="widget-delivery-stats"
                    class="ajax-widget"
                    data-url="dashboard/widgets/delivery-stats"
                >
                    <x-dashboard.skeleton.delivery-stats-skeleton />
                </div>
                @endcan
                {{-- <x-dashboard.monitor-petugas :stafflist="[
                    array(
                        'name' => 'Budi Santoso',
                        'active_jobs_count' => 0, // Kosong
                        'color' => '#1cc88a', // Hijau (Avatar)
                        'badge_class' => 'bg-success',
                        'workload_class' => 'text-success',
                        'status_text' => '● Tersedia'
                    ),
                    array(
                        'name' => 'Siti Aminah',
                        'active_jobs_count' => 2, // Normal
                        'color' => '#4e73df', // Biru
                        'badge_class' => 'bg-primary',
                        'workload_class' => 'text-primary',
                        'status_text' => '● Sedang Bertugas'
                    ),
                    array(
                        'name' => 'Andi Wijaya',
                        'active_jobs_count' => 5, // Sibuk
                        'color' => '#f6c23e', // Kuning
                        'badge_class' => 'bg-warning text-dark',
                        'workload_class' => 'text-warning-emphasis',
                        'status_text' => '● Sibuk'
                    ),
                ]" /> --}}

                @canany(['Staff/penyelia', 'Staff/lhu'])
                <div id="widget-my-jobs"
                    class="ajax-widget"
                    data-url="dashboard/widgets/myJobsList">

                    <x-dashboard.skeleton.my-jobs-skeleton />
                </div>
                @endcan
                {{-- <x-dashboard.monitor-activities :activities="[
                    array(
                        'type' => 'approve',
                        'message' => 'Manager menyetujui Surat Tugas #045/LHU',
                        'time_ago' => '5 menit yang lalu'
                    ),
                    array(
                        'type' => 'job_done',
                        'message' => 'Petugas Lab menyelesaikan tugas untuk Surat Tugas #044/LHU',
                        'time_ago' => '30 menit yang lalu'
                    ),
                    array(
                        'type' => 'info',
                        'message' => 'Surat Tugas #043/LHU telah dikirim ke klien',
                        'time_ago' => '1 jam yang lalu'
                    ),
                ]" /> --}}


            </div>
        </div>
    </div>
    @else
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body text-center p-lg-5">
                <i class="fas fa-user-tie fa-4x text-primary mb-4"></i>
                <h4 class="card-title">Pergantian PIC Terdeteksi</h4>
                <p class="card-text text-muted">
                    Anda sudah tidak terdaftar sebagai PIC untuk
                    <strong>{{ $user->perusahaan?->nama_perusahaan ?? 'perusahaan ini' }}</strong>.
                    <br>
                    Saat ini PIC yang aktif adalah
                    <strong>{{ $newPic ?? 'pengguna lain' }}</strong>.
                </p>
                <p class="card-text text-muted small mt-4">
                    Dasbor dan fitur terkait tidak dapat diakses. Silakan hubungi administrator jika Anda merasa ini
                    adalah sebuah kesalahan.
                </p>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
    @if ($dashboardActive)
        <script>
            $(document).ready(function() {
                loadAllWidgets();
            });

            function loadAllWidgets() {
                const widgets = document.querySelectorAll('.ajax-widget');

                widgets.forEach(widget => {
                    load(widget);
                });
            }

            function load(widget) {
                const url = widget.getAttribute('data-url');
                const jenis = widget.getAttribute('data-jenis');
                let params = {};
                jenis && (params.jenis = jenis);

                ajaxGet(url, params, result => {
                    widget.style.opacity = 0;
                    widget.innerHTML = result.html;

                    executeScripts(widget);

                    setTimeout(() => {
                        widget.style.transition = 'opacity 0.5s ease';
                        widget.style.opacity = 1;
                    }, 50);
                }, error => {
                    // Tampilkan pesan error user friendly
                    widget.innerHTML = `
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center text-muted">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i>
                                <p class="small mb-2">Gagal memuat data</p>
                                <button onclick="refreshData('${widget.id}')" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-sync-alt me-1"></i> Refresh
                                </button>
                            </div>
                        </div>
                    `;
                }, {
                    onErrorPopup: false
                });
            }

            function refreshData(idELement) {
                const widget = document.getElementById(idELement);
                load(widget);
            }
        </script>
    @endif
@endpush
