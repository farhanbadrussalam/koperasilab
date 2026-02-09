@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Rincian Pengiriman</h4>
                    {{-- <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item text-muted">Logistik</li>
                            <li class="breadcrumb-item active text-primary" aria-current="page">Buat Pengiriman</li>
                        </ol>
                    </nav> --}}
                </div>
                <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">Informasi Kontrak & Pelanggan</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <small class="text-muted d-block mb-1">No Pengiriman</small>
                                        <span class="fw-bold text-dark font-monospace" id="no_pengiriman">{{ $noPengiriman }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <small class="text-muted d-block mb-1">Pelanggan</small>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle me-2 text-primary"></i>
                                            <span class="fw-bold text-dark">{{ $informasi->pelanggan->perusahaan->nama_perusahaan }} - {{ $informasi->pelanggan->name }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <small class="text-muted d-block mb-1">No Kontrak</small>
                                        <span class="fw-bold text-primary">{{ $informasi->kontrak?->no_kontrak ?? $informasi->no_kontrak ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <small class="text-muted d-block mb-1">Jenis</small>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $informasi->jenis_layanan_parent->name }}-{{ $informasi->jenis_layanan->name }}</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-secondary opacity-10 my-4">

                            <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">Tujuan Pengiriman</h6>
                            <div class="form-group mb-3">
                                <label class="small fw-bold text-muted mb-2">Pilih Alamat Tujuan <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3 shadow-sm mb-3" id="select_alamat">
                                    <option selected>Pilih alamat</option>
                                </select>
                                <textarea class="form-control rounded-3 bg-light"
                                    cols="30" rows="4" placeholder="Detail alamat lengkap..."
                                    id="alamatTujuan" readonly></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 rounded-top-4">
                            <h6 class="fw-bold text-dark">
                                <i class="bi bi-files me-2 text-primary"></i>Daftar Dokumen
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="list-group" id="list-document">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="sticky-sidebar">
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark mb-3 text-center text-uppercase small tracking-wide">Tindakan Pengiriman</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm" onclick="buatPengiriman(this)">
                                        <i class="bi bi-box-seam me-2"></i>Buat Pengiriman
                                    </button>
                                    <a class="btn btn-outline-secondary py-2 fw-bold rounded-3 d-none" id="btnCetakSurat" target="_blank">
                                        <i class="bi bi-printer me-2"></i>Cetak Surat Pengantar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 bg-light">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center text-muted mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>Pastikan alamat tujuan sudah sesuai dengan lokasi fisik pelanggan.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-preview" tabindex="-1" aria-labelledby="modal-searchLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="#" alt="preview" class="img-fluid rounded" id="modal-preview-image">
                </div>
            </div>
        </div>
    </div>

    @include('pages.management.tld.create')
@endsection

@push('scripts')
    <script>
        const informasi = @json($informasi);
        const status_tld = @json($status_tld);
    </script>
    <script src="{{ asset('js/staff/pengiriman_send.js') }}"></script>
@endpush
