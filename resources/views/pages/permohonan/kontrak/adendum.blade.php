@extends('layouts.main')

@section('content')

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Form Adendum Kontrak</h4>
                <p class="text-muted small mb-0">Perbarui periode dan daftar pengguna untuk kontrak berjalan.</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 rounded-top-4">
                        <h5 class="fw-bold text-dark mb-0">Data Perubahan</h5>
                    </div>

                    <div class="card-body p-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">
                            <span class="bg-primary text-white rounded-circle px-2 py-1 me-1">1</span> Perpanjang Periode
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="input-group mb-3 shadow-sm rounded-3 overflow-hidden ps-0">
                                <span class="input-group-text bg-white border-end-0 ps-3">
                                    <i class="bi bi-calendar-range text-primary"></i>
                                </span>
                                <input type="text" class="form-control bg-white border-start-0 py-3"
                                        id="periode-pemakaian"
                                        placeholder="Pilih rentang tanggal..."
                                        readonly style="cursor: pointer;">
                                <button class="btn btn-primary px-4 fw-bold" type="button" id="btn-periode">
                                    Pilih Tanggal
                                </button>
                            </div>

                            <button class="btn btn-link text-danger text-decoration-none p-0 d-none small fw-medium mt-0" id="btn-clear-periode">
                                <i class="bi bi-x-circle me-1"></i> Hapus Periode Terpilih
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h6 class="text-uppercase text-muted small fw-bold mb-0 tracking-wide">
                                <span class="bg-primary text-white rounded-circle px-2 py-1 me-1">2</span> Daftar Pengguna TLD
                            </h6>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btn-add-pengguna">
                                <i class="bi bi-plus-lg me-1"></i>Tambah User
                            </button>
                        </div>

                        <div id="tld-pengguna" class="d-flex flex-column gap-1">
                            <div class="text-center py-5 bg-light rounded-4 border border-dashed">
                                <div class="text-muted mb-2"><i class="bi bi-people fs-1 opacity-25"></i></div>
                                <h6 class="text-muted fw-bold">Belum ada pengguna ditambahkan</h6>
                                <p class="small text-muted mb-0">Klik tombol "Tambah Pengguna" di atas.</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-end my-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-0 tracking-wide">
                                <span class="bg-primary text-white rounded-circle px-2 py-1 me-1">3</span> Daftar Kontrol TLD
                            </h6>
                        </div>

                        <div class="card bg-light border-0">
                            <div id="tld-kontrol"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catatan Tambahan</label>
                            <textarea class="form-control bg-light border-0" rows="3" placeholder="Tuliskan keterangan jika ada perubahan khusus..."></textarea>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top-0 p-4 text-end rounded-bottom-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 me-2 text-muted">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 rounded-top-4">
                        <h5 class="fw-bold text-primary mb-0">
                            <i class="bi bi-info-circle me-2"></i>Rincian Kontrak
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="text-uppercase text-muted small fw-bold tracking-wide">Nomor Kontrak</label>
                            <div class="p-3 bg-light rounded-3 border mt-1">
                                <span class="fw-bold text-dark">{{ $kontrak->no_kontrak }}</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-uppercase text-muted small fw-bold tracking-wide">Pelanggan</label>
                            <div class="p-3 bg-light rounded-3 border mt-1">
                                <div class="fw-bold">{{ $kontrak->pelanggan->perusahaan->nama_perusahaan }}</div>
                                <div class="small">PIC: <b>{{ $kontrak->pelanggan->name }}</b></div>
                                <div class="small text-muted">{{ $kontrak->pelanggan->perusahaan->alamat[0]->alamat }}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-uppercase text-muted small fw-bold tracking-wide">Layanan</label>
                            <div class="p-3 bg-light rounded-3 border mt-1">
                                <div>{{ $kontrak->jenisTld->name }} - Layanan {{ $kontrak->layanan_jasa->nama_layanan }}</div>
                                <div class="badge bg-light text-dark border rounded-pill fw-normal px-3">
                                    {{ $kontrak->jenis_layanan->name }} - {{ $kontrak->jenis_layanan_parent->name }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="text-uppercase text-muted small fw-bold tracking-wide">Periode Saat Ini</label>
                            <div class="" id="list-periode">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('pages.permohonan.pengajuan.tld_pengguna')
@endsection

@push('scripts')
<script>
    const dataKontrak = @json($kontrak);
</script>

<script src="{{ asset('js/permohonan/adendum.js') }}"></script>

{{-- Sedikit CSS Inline untuk perbaikan visual tabel input --}}
<style>
    .hover-bg-danger-subtle:hover {
        background-color: #fee2e2; /* Bootstrap danger-subtle like */
    }
    /* .form-control:focus, .form-select:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    } */
    /* Transisi halus saat hapus baris */
    .tld-row {
        transition: all 0.2s ease;
    }
</style>
@endpush
