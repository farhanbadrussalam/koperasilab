@extends('layouts.main')
@php
    $dashboardActive = true;
    $user = Auth::user();
    $newPic = null;
    $changePIC = false;
    $verifyUser = false;

    $rolePelanggan = $user->hasRole('Pelanggan');
    // status 2 = PIC tidak aktif
    if ($rolePelanggan && $user->status == 2) {
        $dashboardActive = false;
        $changePIC = true;
        // Cari PIC baru yang aktif (status 1) di perusahaan yang sama.
        // Pastikan relasi 'perusahaan' dan 'pengguna' sudah terdefinisi di model User.
        if ($user->perusahaan) {
            $newPic = $user->perusahaan->pic->name;
        }
    }

    if ($rolePelanggan && $user->id_perusahaan == null) {
        $verifyUser = true;
        $dashboardActive = false;
    }
@endphp

@section('content')
    @if ($dashboardActive)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 text-gray-800">Dashboard</h4>
            <x-filter-date styleType="global" default="monthly" />
        </div>
        <div class="row">
            <div class="col-lg-8">
                @if (!$rolePelanggan)
                    {{-- Widget Pencarian Kontrak Cepat --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-briefcase-fill text-info me-2"></i>
                                Pencarian & Pemantauan Status Kontrak
                            </h6>
                            <p class="text-muted small mb-3">
                                Pencarian cepat berdasarkan nama perusahaan/instansi atau nomor kontrak untuk memantau
                                status pembayaran, proses lab, dan pengiriman secara instan.
                            </p>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <select class="form-select" id="contract_select2" style="width: 1%;">
                                    <option></option>
                                </select>
                                <button class="btn btn-info text-white fw-bold px-4" type="button"
                                    onclick="triggerSearchFromSelect2()">Cari</button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row g-2 mb-3">
                    @canany(['Permohonan/pengajuan', 'Staff/permohonan', 'Manager/pengajuan'])
                        <div id="widget-summary-permohonan" class="ajax-widget col-6 summary-active"
                            data-url="dashboard/widgets/summary-cards" data-jenis="permohonan"
                            data-skeleton="dashboard/skeleton/summary">

                            <x-dashboard.skeleton.summary-skeleton />
                        </div>
                    @endcan

                    @canany(['Permohonan/pengajuan'])
                        <div id="widget-summary-pembayaran" class="ajax-widget col-6 summary-active"
                            data-url="dashboard/widgets/summary-cards" data-jenis="pembayaran"
                            data-skeleton="dashboard/skeleton/summary">

                            <x-dashboard.skeleton.summary-skeleton />
                        </div>
                    @endcan

                    @canany(['Kontrak'])
                        <div id="widget-summary-kontrak" class="ajax-widget col-6 summary-active"
                            data-url="dashboard/widgets/summary-cards" data-jenis="kontrak"
                            data-skeleton="dashboard/skeleton/summary">

                            <x-dashboard.skeleton.summary-skeleton />
                        </div>
                    @endcan

                    @canany(['Tld', 'Kontrak'])
                        <div id="widget-summary-tld" class="ajax-widget col-6 summary-active"
                            data-url="dashboard/widgets/summary-cards" data-jenis="tld"
                            data-skeleton="dashboard/skeleton/summary">

                            <x-dashboard.skeleton.summary-skeleton />
                        </div>
                    @endcan

                    @canany(['Staff/penyelia'])
                        <div id="widget-summary-penyelia" class="ajax-widget col-6 summary-active"
                            data-url="dashboard/widgets/summary-cards" data-jenis="penyelia"
                            data-skeleton="dashboard/skeleton/summary">

                            <x-dashboard.skeleton.summary-skeleton />
                        </div>
                    @endcan

                    @canany(['Staff/lhu/petugas'])
                        <div id="widget-summary-petugaslab" class="ajax-widget col-6 summary-active"
                            data-url="dashboard/widgets/summary-cards" data-jenis="petugaslab"
                            data-skeleton="dashboard/skeleton/summary">

                            <x-dashboard.skeleton.summary-skeleton />
                        </div>
                    @endcan

                    @canany(['Staff/keuangan', 'Manager/keuangan'])
                        <div id="widget-summary-invoice" class="ajax-widget col-6 summary-active"
                            data-url="dashboard/widgets/summary-cards" data-jenis="invoice"
                            data-skeleton="dashboard/skeleton/summary">

                            <x-dashboard.skeleton.summary-skeleton />
                        </div>
                    @endcan
                </div>

                @canany(['Permohonan/pengajuan', 'Staff/permohonan', 'Manager/pengajuan'])
                    <div id="widget-statistics" class="ajax-widget" data-url="dashboard/widgets/statistics-layanan"
                        data-jenis="statisticsLayanan" data-skeleton="dashboard/skeleton/service-chart">

                        <x-dashboard.skeleton.service-chart-skeleton />
                    </div>
                @endcan


                @canany(['Staff/pengiriman'])
                    <div id="widget-expedisi-stats" class="ajax-widget" data-url="dashboard/widgets/expedition-stats"
                        data-skeleton="dashboard/skeleton/service-chart">

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
                    <div id="widget-monitor-penyelia" class="ajax-widget" data-url="dashboard/widgets/monitorPenyeliaan"
                        data-skeleton="dashboard/skeleton/service-chart">

                        <x-dashboard.skeleton.service-chart-skeleton />
                    </div>
                @endcan

                @canany(['Staff/keuangan', 'Manager/keuangan'])
                    <div class="row g-4">
                        <div class="col-12">
                            <div id="widget-finance-charts" class="ajax-widget" data-url="dashboard/widgets/finance-charts"
                                data-skeleton="dashboard/skeleton/finance-chart">

                                <x-dashboard.skeleton.service-chart-skeleton />
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    <div id="widget-time-cards">
                        <x-dashboard.time-cards />
                    </div>

                    @canany(['Permohonan/pengajuan', 'Staff/pengiriman'])
                        <div id="widget-delivery-stats" class="ajax-widget" data-url="dashboard/widgets/delivery-stats"
                            data-skeleton="dashboard/skeleton/delivery-stats">
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
                        <div id="widget-my-jobs" class="ajax-widget" data-url="dashboard/widgets/myJobsList"
                            data-skeleton="dashboard/skeleton/myJobs">

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
                    @canany(['Staff/keuangan', 'Manager/keuangan'])
                        <div class="col-12">
                            <div id="widget-finance-chart-service" class="ajax-widget h-100"
                                data-url="dashboard/widgets/finance-chart-service"
                                data-skeleton="dashboard/skeleton/service-chart">

                                <x-dashboard.skeleton.service-chart-skeleton />
                            </div>
                        </div>
                        {{-- <div id="widget-finance-side"
                    class="ajax-widget"
                    data-url="dashboard/widgets/finance-side"
                    data-skeleton="dashboard/skeleton/finance-chart-service">

                    <x-dashboard.skeleton.finance-chart-service />
                </div> --}}
                    @endcan
                </div>
            </div>
        </div>
    @elseif($changePIC)
        {{-- ===================== CHANGE PIC SCREEN ===================== --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">

                    {{-- Kiri: ikon + judul --}}
                    <div class="col-lg-5 text-center text-lg-start">
                        <span
                            class="badge rounded-pill bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 mb-3 px-3 py-2">
                            <i class="fas fa-exclamation-triangle me-1"></i> Perubahan PIC
                        </span>
                        <div class="mb-4">
                            <span
                                class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10"
                                style="width:90px;height:90px;font-size:2.2rem;">
                                <i class="fas fa-user-slash text-warning"></i>
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2">Pergantian PIC Terdeteksi</h4>
                        <p class="text-muted mb-4 lh-lg">
                            Akses dasbor Anda dibatasi karena perubahan PIC pada instansi terdaftar.
                        </p>
                        <a href="mailto:{{ $email_cs }}"
                            class="btn btn-outline-warning d-inline-flex align-items-center gap-2 rounded-3">
                            <i class="fas fa-headset"></i> Hubungi Administrator
                        </a>
                    </div>

                    {{-- Kanan: info card --}}
                    <div class="col-lg-7">
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <div class="rounded-3 p-3 bg-light border">
                                    <div class="text-uppercase text-muted small mb-1"
                                        style="letter-spacing:.07em;font-size:.72rem">
                                        <i class="fas fa-building me-1"></i> Instansi
                                    </div>
                                    <div class="fw-semibold">
                                        {{ $user->perusahaan?->nama_perusahaan ?? 'Perusahaan tidak diketahui' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="rounded-3 p-3 bg-light border">
                                    <div class="text-uppercase text-muted small mb-1"
                                        style="letter-spacing:.07em;font-size:.72rem">
                                        <i class="fas fa-user me-1"></i> Akun Anda
                                    </div>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="rounded-3 p-3 bg-light border">
                                    <div class="text-uppercase text-muted small mb-1"
                                        style="letter-spacing:.07em;font-size:.72rem">
                                        <i class="fas fa-user-check me-1"></i> PIC Aktif Saat Ini
                                    </div>
                                    <div class="fw-semibold">{{ $newPic ?? 'Pengguna lain' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-3 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25">
                            <p class="mb-0 text-muted small lh-lg">
                                <i class="fas fa-info-circle me-1 text-warning"></i>
                                Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator sistem untuk
                                memperbarui status PIC Anda.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($verifyUser)
        @php
            $hasRequest = Auth::user()->request_verify_instansi;
        @endphp
        {{-- ===================== VERIFY USER SCREEN ===================== --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">

                    {{-- Kiri: ikon + judul --}}
                    <div class="col-lg-5 text-center text-lg-start">
                        @if (!$hasRequest)
                            <span
                                class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 mb-3 px-3 py-2">
                                <i class="bi bi-exclamation-triangle me-1"></i> Data Belum Lengkap
                            </span>
                            <div class="mb-4">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10"
                                    style="width:90px;height:90px;font-size:2.2rem;">
                                    <i class="bi bi-building-add text-warning"></i>
                                </span>
                            </div>
                            <h4 class="fw-bold mb-2">Lengkapi Data Instansi</h4>
                            <p class="text-muted mb-4 lh-lg">
                                Anda belum melengkapi data pengajuan instansi. Silakan masuk ke halaman profil Anda dan
                                ajukan instansi baru untuk melanjutkan.
                            </p>
                            <a href="{{ route('userProfile.index') }}"
                                class="btn btn-warning text-white d-inline-flex align-items-center gap-2 rounded-3">
                                <i class="bi bi-person-lines-fill"></i> Buka Profil
                            </a>
                        @else
                            <span
                                class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-3 px-3 py-2">
                                <i class="bi bi-hourglass-split me-1"></i> Sedang Diproses
                            </span>
                            <div class="mb-4">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
                                    style="width:90px;height:90px;font-size:2.2rem;">
                                    <i class="bi bi-shield-check text-primary"></i>
                                </span>
                            </div>
                            <h4 class="fw-bold mb-2">Verifikasi Akun Sedang Berlangsung</h4>
                            <p class="text-muted mb-4 lh-lg">
                                Data instansi Anda sedang ditinjau oleh tim kami. Proses ini biasanya memakan waktu 1–3 hari
                                kerja.
                            </p>
                            <a href="mailto:{{ $email_cs }}"
                                class="btn btn-outline-primary d-inline-flex align-items-center gap-2 rounded-3">
                                <i class="bi bi-headset"></i> Hubungi Administrator
                            </a>
                        @endif
                    </div>

                    {{-- Kanan: timeline verifikasi --}}
                    <div class="col-lg-7">
                        <p class="text-uppercase text-muted small mb-3" style="letter-spacing:.08em;font-size:.72rem">
                            <i class="bi bi-list-check me-1"></i> Tahapan Verifikasi
                        </p>

                        <div class="d-flex flex-column gap-2">
                            {{-- Step 1 (Selalu Selesai) --}}
                            <div class="d-flex align-items-start gap-3">
                                <div class="d-flex flex-column align-items-center flex-shrink-0">
                                    <span
                                        class="d-inline-flex align-items-center justify-content-center rounded-circle border border-success text-success fw-bold bg-success bg-opacity-10"
                                        style="width:34px;height:34px;font-size:.75rem">
                                        <i class="bi bi-check-lg" style="font-size:.85rem"></i>
                                    </span>
                                    <div class="my-1 border-start border-2 border-success" style="min-height:20px"></div>
                                </div>
                                <div class="py-1">
                                    <div class="fw-semibold" style="font-size:.88rem">Pendaftaran Akun</div>
                                    <div class="text-muted" style="font-size:.78rem">Akun berhasil dibuat dan data awal
                                        tersimpan.</div>
                                </div>
                            </div>

                            {{-- Step 2 --}}
                            <div class="d-flex align-items-start gap-3">
                                <div class="d-flex flex-column align-items-center flex-shrink-0">
                                    @if (!$hasRequest)
                                        <span
                                            class="d-inline-flex align-items-center justify-content-center rounded-circle border border-primary text-primary fw-bold bg-primary bg-opacity-10"
                                            style="width:34px;height:34px;font-size:.75rem">
                                            <i class="bi bi-hourglass-split" style="font-size:.75rem"></i>
                                        </span>
                                        <div class="my-1 border-start border-2 border-secondary border-opacity-25"
                                            style="min-height:20px"></div>
                                    @else
                                        <span
                                            class="d-inline-flex align-items-center justify-content-center rounded-circle border border-success text-success fw-bold bg-success bg-opacity-10"
                                            style="width:34px;height:34px;font-size:.75rem">
                                            <i class="bi bi-check-lg" style="font-size:.85rem"></i>
                                        </span>
                                        <div class="my-1 border-start border-2 border-success" style="min-height:20px">
                                        </div>
                                    @endif
                                </div>
                                <div class="py-1">
                                    <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap"
                                        style="font-size:.88rem">
                                        Pengiriman Data Instansi
                                        @if (!$hasRequest)
                                            <span
                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"
                                                style="font-size:.7rem">Saat ini</span>
                                        @endif
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        @if (!$hasRequest)
                                            Harap lengkapi data instansi dan profil Anda.
                                        @else
                                            Data instansi Anda telah diterima sistem.
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Step 3 --}}
                            <div class="d-flex align-items-start gap-3">
                                <div class="d-flex flex-column align-items-center flex-shrink-0">
                                    @if (!$hasRequest)
                                        <span
                                            class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold border text-muted bg-light"
                                            style="width:34px;height:34px;font-size:.78rem">
                                            3
                                        </span>
                                        <div class="my-1 border-start border-2 border-secondary border-opacity-25"
                                            style="min-height:20px"></div>
                                    @else
                                        <span
                                            class="d-inline-flex align-items-center justify-content-center rounded-circle border border-primary text-primary fw-bold bg-primary bg-opacity-10"
                                            style="width:34px;height:34px;font-size:.75rem">
                                            <i class="bi bi-hourglass-split" style="font-size:.75rem"></i>
                                        </span>
                                        <div class="my-1 border-start border-2 border-secondary border-opacity-25"
                                            style="min-height:20px"></div>
                                    @endif
                                </div>
                                <div class="py-1">
                                    <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap @if (!$hasRequest) text-muted @endif"
                                        style="font-size:.88rem">
                                        Peninjauan oleh Tim Admin
                                        @if ($hasRequest)
                                            <span
                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"
                                                style="font-size:.7rem">Saat ini</span>
                                        @endif
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        @if (!$hasRequest)
                                            Tim kami akan memverifikasi kelengkapan data Anda.
                                        @else
                                            Tim kami sedang memverifikasi kelengkapan data Anda.
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Step 4 (pending) --}}
                            <div class="d-flex align-items-start gap-3">
                                <div class="d-flex flex-column align-items-center flex-shrink-0">
                                    <span
                                        class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold border text-muted bg-light"
                                        style="width:34px;height:34px;font-size:.78rem">
                                        4
                                    </span>
                                </div>
                                <div class="py-1">
                                    <div class="fw-semibold text-muted" style="font-size:.88rem">Akses Dasbor Aktif</div>
                                    <div class="text-muted" style="font-size:.78rem">Setelah disetujui, semua fitur akan
                                        dapat diakses.</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3 p-3 mt-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                            <p class="mb-0 text-muted small lh-lg">
                                <i class="bi bi-info-circle me-1 text-primary"></i>
                                Jika belum ada kabar lebih dari 3 hari kerja, silakan hubungi administrator untuk informasi
                                lebih lanjut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (!$rolePelanggan)
        {{-- Modal Pencarian Kontrak --}}
        <div class="modal fade" id="modalContractSearch" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi bi-briefcase-fill me-2 text-info"></i>Detail Pencarian Kontrak
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" id="modalContractSearchBody" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Results will be injected here -->
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <x-modal.periode-kontrak />
@endsection
@push('scripts')
    @if ($dashboardActive)
        <script>
            let currentGlobalFilter = 'monthly';
            let modalPeriode;

            function applyFilter(button, filter, text) {
                const styleType = button.getAttribute('data-type');
                if (styleType === 'global') {
                    currentGlobalFilter = filter;
                    document.getElementById('globalFilterText').innerText = text;

                    document.querySelectorAll('.ajax-widget').forEach(widget => {
                        widget.removeAttribute('data-filter');
                    });
                    loadAllWidgets();
                } else {
                    const widget = button.closest('.ajax-widget');
                    if (widget) {
                        widget.setAttribute('data-filter', filter);
                        // Update text if element exists
                        const filterTextEl = widget.querySelector('.widget-filter-text');
                        if (filterTextEl && text) {
                            filterTextEl.innerText = text;
                        }
                        load(widget);
                    }
                }
            }

            function initWidgetDatePicker() {
                document.querySelectorAll('.widget-custom-date').forEach(function(el) {
                    // Skip if already initialized
                    if (el._flatpickr) return;

                    flatpickr(el, {
                        mode: 'range',
                        dateFormat: 'Y-m-d',
                        onChange: function(selectedDates, dateStr, instance) {
                            if (selectedDates.length === 2) {
                                const isGlobal = instance.element.getAttribute('data-scope') === 'global';

                                if (isGlobal) {
                                    // Handle global filter
                                    currentGlobalFilter = dateStr;
                                    const textEl = document.getElementById('globalFilterText');
                                    if (textEl) textEl.innerText = dateStr;

                                    document.querySelectorAll('.ajax-widget').forEach(widget => {
                                        widget.removeAttribute('data-filter');
                                    });
                                    loadAllWidgets();
                                } else {
                                    // Handle per-widget filter
                                    const widget = instance.element.closest('.ajax-widget');
                                    if (widget) {
                                        widget.setAttribute('data-filter', dateStr);
                                        const filterTextEl = widget.querySelector('.widget-filter-text');
                                        if (filterTextEl) filterTextEl.innerText = dateStr;
                                        load(widget);
                                    }
                                }

                                // Close bootstrap dropdown manually
                                const dropdownEl = instance.element.closest('.dropdown');
                                if (dropdownEl) {
                                    const toggle = dropdownEl.querySelector('[data-bs-toggle="dropdown"]');
                                    if (toggle) {
                                        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                                            bootstrap.Dropdown.getInstance(toggle)?.hide();
                                        } else if (typeof $ !== 'undefined') {
                                            $(toggle).dropdown('hide');
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            }

            $(document).ready(function() {
                modalPeriode = new ModalPeriodeKontrak();
                cekSummaryCard();
                loadAllWidgets();
                initWidgetDatePicker(); // init global date picker

                const contractSelect2 = $('#contract_select2');
                if (contractSelect2.length > 0) {
                    contractSelect2.select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Cari nama instansi atau nomor kontrak...',
                        allowClear: true,
                        ajax: {
                            url: 'dashboard/widgets/contract-search-options',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    keyword: params.term
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data.options
                                };
                            },
                            cache: true
                        }
                    });

                    // Trigger search automatically when selected
                    contractSelect2.on('select2:select', function(e) {
                        performContractSearch(e.params.data.id);
                    });
                }

                // Reset search Select2 option when modal is hidden
                const modalSearch = $('#modalContractSearch');
                if (modalSearch.length > 0) {
                    modalSearch.on('hidden.bs.modal', function() {
                        clearContractSearch();
                    });
                }
            });

            function triggerSearchFromSelect2() {
                const val = $('#contract_select2').val();
                if (!val) {
                    swal({
                        title: 'Oops...',
                        text: 'Silakan pilih kontrak dari daftar terlebih dahulu',
                        icon: 'warning'
                    });
                    return;
                }
                performContractSearch(val);
            }

            function performContractSearch(contractId) {
                const modal = $('#modalContractSearch');
                const resultsDiv = $('#modalContractSearchBody');

                resultsDiv.html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Memuat data kontrak...</p>
                    </div>
                `);
                modal.modal('show');

                ajaxGet(`dashboard/widgets/contract-search`, {
                    id: contractId
                }, result => {
                    resultsDiv.html(result.html);
                }, error => {
                    resultsDiv.html(`
                        <div class="text-center py-5">
                            <i class="bi bi-exclamation-circle text-danger display-3 mb-2"></i>
                            <p class="mt-3 text-muted">Gagal memuat data pencarian.</p>
                        </div>
                    `);
                }, {
                    onErrorPopup: false
                });
            }

            function clearContractSearch() {
                const contractSelect2 = $('#contract_select2');
                if (contractSelect2.hasClass("select2-hidden-accessible")) {
                    contractSelect2.val(null).trigger('change');
                }
                $('#modalContractSearchBody').html('');
            }

            function cekSummaryCard() {
                const summary = document.querySelectorAll('.summary-active');

                if (summary.length % 2 === 1) {
                    const lastSummary = summary[summary.length - 1];
                    lastSummary.classList.remove('col-6');
                    lastSummary.classList.add('col-12');
                }
            }

            function loadAllWidgets() {
                const widgets = document.querySelectorAll('.ajax-widget');

                if (widgets.length == 0) $('#widget-time-cards').hide();

                widgets.forEach(widget => {
                    load(widget);
                });
            }

            function load(widget) {
                const url = widget.getAttribute('data-url');
                const jenis = widget.getAttribute('data-jenis');
                let params = {};
                jenis && (params.jenis = jenis);

                const widgetFilter = widget.getAttribute('data-filter');
                params.date_filter = widgetFilter ? widgetFilter : currentGlobalFilter;

                ajaxGet(url, params, result => {
                    widget.style.opacity = 0;
                    widget.innerHTML = result.html;

                    initWidgetDatePicker(); // Re-initialize datepickers inside the newly loaded widget

                    executeScripts(widget);

                    setTimeout(() => {
                        widget.style.transition = 'opacity 0.5s ease';
                        widget.style.opacity = 1;
                    }, 50);
                }, error => {
                    // Tampilkan pesan error user friendly
                    widget.innerHTML = errorHandler(widget.id);
                }, {
                    onErrorPopup: false
                });
            }

            function refreshData(idELement) {
                const widget = document.getElementById(idELement);
                const urlSkeleton = widget.getAttribute('data-skeleton');
                ajaxGet(urlSkeleton, {}, result => {
                    widget.style.opacity = 0;
                    widget.innerHTML = result.html;

                    setTimeout(() => {
                        widget.style.transition = 'opacity 0.5s ease';
                        widget.style.opacity = 1;
                        load(widget);
                    }, 50);
                }, error => {
                    // Tampilkan pesan error user friendly
                    widget.innerHTML = errorHandler(widget.id);
                }, {
                    onErrorPopup: false
                })
            }

            function errorHandler(idELement) {
                return `
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-muted">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i>
                            <p class="small mb-2">Gagal memuat data</p>
                            <button onclick="refreshData('${idELement}')" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                `;
            }
        </script>
    @endif
@endpush
