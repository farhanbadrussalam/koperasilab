@extends('layouts.main')

@section('content')
@php
$pengembalianStart = '';
$pengembalianEnd = '';
$isPengembalian = false;
if(!$periode2Next || !$isSewa){
$startDate = new DateTime($periodeNow->end_date);
// $startDate->modify('first day of this month');
$startDate->modify('first day of +4 months');

$endDate = clone $startDate;
$endDate->modify('last day of +2 months');

$pengembalianStart = $startDate->format('Y-m-d');
$pengembalianEnd = $endDate->format('Y-m-d');
}

if(!$periode2Next && !$isSewa){
$isPengembalian = true;
}
@endphp

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Header Halaman -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Form Evaluasi Kontrak</h4>
                <p class="text-muted small mb-0">Lakukan evaluasi kelayakan penggunaan TLD secara berkala.</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="row g-4">
            <!-- Kolom Kiri: Form Alamat & Daftar TLD -->
            <div class="col-lg-8">
                <!-- Bagian 1: Alamat Pengiriman -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">
                        <span class="bg-primary text-white rounded-circle px-2 py-1 me-1">1</span> Alamat Pengiriman
                    </h6>
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="mb-3">
                            <label for="selectAlamat" class="form-label small fw-bold">Pilih Alamat</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-end-0 ps-3">
                                    <i class="bi bi-geo-alt text-primary"></i>
                                </span>
                                <select name="selectAlamat" id="selectAlamat" class="form-select border-start-0 py-3 cursor-pointer">
                                    <option value="">Pilih alamat</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label for="txt_alamat" class="form-label small fw-bold">Detail Alamat Lengkap</label>
                            <textarea name="txt_alamat" id="txt_alamat" cols="30" rows="3" class="form-control bg-light border-0 rounded-3 p-3" readonly placeholder="Detail alamat akan otomatis terisi setelah memilih jenis alamat..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: TLD Pengguna -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-0 tracking-wide">
                            <span class="bg-primary text-white rounded-circle px-2 py-1 me-1">2</span> TLD Pengguna
                        </h6>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input cursor-pointer" type="checkbox" id="checkAllTldPengguna">
                            <label class="form-check-label small fw-bold text-muted cursor-pointer" for="checkAllTldPengguna">Pilih Semua</label>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="card-body p-0 overflow-auto" style="max-height: 40vh;">
                            <div id="pengguna-list-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: TLD Kontrol -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">
                            <span class="bg-primary text-white rounded-circle px-2 py-1 me-1">3</span> TLD Kontrol
                        </h6>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input cursor-pointer" type="checkbox" id="checkAllTldKontrol">
                            <label class="form-check-label small fw-bold text-muted cursor-pointer" for="checkAllTldKontrol">Pilih Semua</label>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="card-body p-0 overflow-auto" style="max-height: 40vh;">
                            <div id="tld-kontrol-content"></div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Bawah -->
                <div class="card border-0 shadow-sm rounded-4 p-4 text-end bg-white gap-2 d-flex justify-content-between flex-row">
                    <a href="{{ url()->previous() }}" class="btn btn-light rounded-pill px-4 me-2 text-muted">Batal</a>
                    <button class="btn btn-primary rounded-pill px-5 flex-grow-1 fw-bold shadow-sm" onclick="buatPermohonan(this)">
                        <i class="bi bi-check-circle-fill me-2"></i>Buat Permohonan Evaluasi
                    </button>
                </div>
            </div>

            <!-- Kolom Kanan: Rincian & Status Periode -->
            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    <!-- Status Periode -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 rounded-top-4">
                            <h5 class="fw-bold text-primary mb-0">
                                <i class="bi bi-calendar-check me-2"></i>Status Periode
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @if($periodeNow->start_date)
                            <div class="mb-3">
                                <label class="text-uppercase text-muted small fw-bold tracking-wide">Periode Pemakaian</label>
                                <div class="p-3 bg-light rounded-3 border border-secondary border-opacity-25 mt-1 d-flex align-items-center">
                                    <i class="bi bi-clock-history text-secondary me-3 fs-4"></i>
                                    <div>
                                        <div class="fw-bold text-dark">Periode Aktif</div>
                                        <div class="small text-muted">{{ convert_date($periodeNow->start_date, 2) }} - {{ convert_date($periodeNow->end_date, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($periode2Next)
                            <div class="mb-3">
                                <label class="text-uppercase text-muted small fw-bold tracking-wide">Periode Berikutnya</label>
                                <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 mt-1 d-flex align-items-center">
                                    <i class="bi bi-arrow-right-circle text-primary me-3 fs-4"></i>
                                    <div>
                                        <div class="fw-bold text-primary">Periode Mendatang</div>
                                        <div class="small text-muted">{{ $periode2Next ? convert_date($periode2Next->start_date, 2) : '-' }} - {{ $periode2Next ? convert_date($periode2Next->end_date, 2) : '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(!$periode2Next && !$isSewa)
                            <div class="mb-3">
                                <label class="text-uppercase text-muted small fw-bold tracking-wide">Pengembalian Ke-{{ $periodeNow->count_tld }}</label>
                                <div class="p-3 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-25 mt-1 d-flex align-items-center">
                                    <i class="bi bi-arrow-return-left text-warning me-3 fs-4"></i>
                                    <div>
                                        <div class="fw-bold text-warning">Jadwal Pengembalian</div>
                                        <div class="small text-muted">{{ convert_date($pengembalianStart, 2) }} - {{ convert_date($pengembalianEnd, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Rincian Kontrak -->
                    <div class="card border-0 shadow-sm rounded-4 bg-white">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 rounded-top-4">
                            <h5 class="fw-bold text-primary mb-0">
                                <i class="bi bi-info-circle me-2"></i>Rincian Kontrak
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="text-uppercase text-muted small fw-bold tracking-wide">Nomor Kontrak</label>
                                <div class="p-3 bg-light rounded-3 border mt-1">
                                    <span class="fw-bold text-dark">{{ $kontrak->no_kontrak ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="text-uppercase text-muted small fw-bold tracking-wide">Pelanggan</label>
                                <div class="p-3 bg-light rounded-3 border mt-1">
                                    <div class="fw-bold text-dark">{{ $kontrak->pelanggan->perusahaan->nama_perusahaan }}</div>
                                    <div class="small text-muted">PIC: <b>{{ $kontrak->pelanggan->name }}</b></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="text-uppercase text-muted small fw-bold tracking-wide">Layanan</label>
                                <div class="p-3 bg-light rounded-3 border mt-1">
                                    <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis fs-7 px-3">
                                        {{ $kontrak->jenis_layanan->name }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="text-uppercase text-muted small fw-bold tracking-wide">Harga</label>
                                <div class="p-3 bg-light rounded-3 border mt-1">
                                    <span class="fw-bold text-success">{{ formatCurrency($kontrak->total_harga) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('pages.management.tld.create')
@endsection

@push('scripts')
<script>
    const dataKontrak = @json($kontrak);
    const dataPeriodeNow = @json($periodeNow);
    const dataPeriodeNext = @json($periodeNext);
    const dataJenisLayanan = @json($jenisLayanan);
    const isPengembalian = @json($isPengembalian);
    const pengembalianStart = @json($pengembalianStart);
    const pengembalianEnd = @json($pengembalianEnd);
</script>
<script src="{{ asset('js/permohonan/kontrak_evaluasi.js') }}"></script>
@endpush