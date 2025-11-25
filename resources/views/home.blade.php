@extends('layouts.main')

@section('content')

    <div class="row">
        <div class="col-lg-8">
            <div class="row g-2 mb-3">
                @canany(['Permohonan/pengajuan', 'Staff/permohonan', 'Manager/pengajuan'])
                <div id="widget-summary"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="permohonan">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Staff/keuangan','Permohonan/pengajuan'])
                <div id="widget-summary"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="pembayaran">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Kontrak'])
                <div id="widget-summary"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="kontrak">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Tld'])
                <div id="widget-summary"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="tld">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Staff/penyelia'])
                <div id="widget-summary"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="penyelia">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan

                @canany(['Staff/lhu/petugas'])
                <div id="widget-summary"
                    class="ajax-widget col-6"
                    data-url="dashboard/widgets/summary-cards"
                    data-jenis="petugaslab">

                    <x-dashboard.skeleton.summary-skeleton />
                </div>
                @endcan
            </div>
            @canany(['Permohonan/pengajuan', 'Staff/permohonan', 'Manager/pengajuan'])
            <x-dashboard.pending-request :requests="[
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
            ]" />
            @endcan
            @canany(['Kontrak'])
                <x-dashboard.active-contracts :contracts="[
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
                ]" />
            @endcan

            <x-dashboard.my-jobs :jobs="[
                array(
                    'id' => 101,
                    'nomor_surat' => '001/LHU-KOP/XI/2025',
                    'nama_perusahaan' => 'PT. Sumber Makmur Jaya',
                    'current_step_name' => 'Anealing',
                    'deadline' => '28-01-2023',
                    'has_pending_parallel' => false,
                    'status' => 'active'
                ),
                array(
                    'id' => 102,
                    'nomor_surat' => '002/LHU-KOP/XI/2025',
                    'nama_perusahaan' => 'CV. Maju Terus',
                    'current_step_name' => 'Cooling',
                    'deadline' => '30-01-2023',
                    'has_pending_parallel' => true,
                    'status' => 'active'
                ),
                array(
                    'id' => 103,
                    'nomor_surat' => '003/LHU-KOP/XI/2025',
                    'nama_perusahaan' => 'PT. Sumber Makmur Jaya',
                    'current_step_name' => 'Anealing',
                    'deadline' => '28-01-2023',
                    'has_pending_parallel' => false,
                    'status' => 'active'
                ),
                array(
                    'id' => 104,
                    'nomor_surat' => '004/LHU-KOP/XI/2025',
                    'nama_perusahaan' => 'PT. Sumber Makmur Jaya',
                    'current_step_name' => 'Anealing',
                    'deadline' => '28-01-2023',
                    'has_pending_parallel' => false,
                    'status' => 'active'
                ),
            ]" />

            @canany(['Staff/penyelia'])
            <x-dashboard.penyelia-table :tasks="[
                array(
                    'id' => 1,
                    'nomor_surat' => '001/NL-UPD/XI/2025',
                    'nomor_referensi' => 'S-0001/JKRL/XI/2025',
                    'nama_perusahaan' => 'PT. Nusa Lestari Tbk',
                    'nama_petugas' => 'Budi Santoso',
                    'periode' => 'Zero Cek',
                    'current_step' => 0, // Awal Banget
                    'step_name' => 'TTD Manager',
                    'is_labeled' => false,
                    'is_stored' => false
                ),
                array(
                    'id' => 2,
                    'nomor_surat' => '002/SM-EXT/XI/2025',
                    'nomor_referensi' => 'S-0045/JKRL/XI/2025',
                    'nama_perusahaan' => 'PT. Sumber Makmur',
                    'nama_petugas' => 'Siti Aminah',
                    'periode' => 'Periode 1',
                    'current_step' => 3, // Pertengahan
                    'step_name' => 'Penerbitan LHU',
                    'is_labeled' => false, // Kuning (Warning)
                    'is_stored' => false   // Abu-abu
                ),
                array(
                    'id' => 3,
                    'nomor_surat' => '003/KA-INT/XI/2025',
                    'nomor_referensi' => 'S-0099/JKRL/XI/2025',
                    'nama_perusahaan' => 'CV. Karya Abadi',
                    'nama_petugas' => 'Joko Anwar',
                    'periode' => 'Periode 2',
                    'current_step' => 6, // Hampir Selesai
                    'step_name' => 'Scan Laporan',
                    'is_labeled' => true,  // Hijau
                    'is_stored' => true    // Hijau
                ),
            ]" />
            @endcan
        </div>
        <div class="col-lg-4">
            <div class="sticky-sidebar">
                <x-dashboard.time-cards />
                @canany(['Permohonan/pengajuan', 'Staff/pengiriman'])
                <x-dashboard.tracking :shipments="[
                    array(
                        'id' => 1,
                        'nomor_kontrak' => '#001',
                        'nomor_resi' => 'JP-88219322',
                        'detail_paket' => 'Dokumen TLD, Invoice',
                        'periode' => 1,
                        'status' => 'shipping'
                    ),
                    array(
                        'id' => 2,
                        'nomor_kontrak' => '#002',
                        'nomor_resi' => 'JP-88219323',
                        'detail_paket' => 'Dokumen Hosting, Invoice',
                        'periode' => 2,
                        'status' => 'preparing'
                    ),
                    array(
                        'id' => 3,
                        'nomor_kontrak' => '#003',
                        'nomor_resi' => 'JP-88219324',
                        'detail_paket' => 'Dokumen TLD, Invoice',
                        'periode' => 3,
                        'status' => 'shipping'
                    )
                ]" />
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
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        loadAllWidgets();
    });

    function loadAllWidgets() {
        const widgets = document.querySelectorAll('.ajax-widget');

        widgets.forEach(widget => {
            const url = widget.getAttribute('data-url');
            const jenis = widget.getAttribute('data-jenis');

            ajaxGet(url, {jenis: jenis}, result => {
                widget.style.opacity = 0;
                widget.innerHTML = result.html;

                setTimeout(() => {
                    widget.style.transition = 'opacity 0.5s ease';
                    widget.style.opacity = 1;
                }, 50);
            }, error => {
                console.error('Widget Error:', error);
                // Tampilkan pesan error user friendly
                widget.innerHTML = `
                    <div class="alert alert-warning text-center small">
                        Gagal memuat data. <button class="btn btn-link btn-sm p-0" onclick="location.reload()">Refresh</button>
                    </div>
                `;
            });
        })
    }
</script>
@endpush
