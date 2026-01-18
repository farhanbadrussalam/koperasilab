@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Rincian Permohonan</h4>
                {{-- <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item text-muted">Aplikasi</li>
                        <li class="breadcrumb-item text-muted">Permohonan</li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">Detail #REQ-001</li>
                    </ol>
                </nav> --}}
            </div>
            <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 rounded-top-4">
                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold text-primary border-bottom-0" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button">
                                    <i class="bi bi-file-earmark-text me-2"></i>Informasi Layanan
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-muted border-bottom-0" id="customer-tab" data-bs-toggle="tab" data-bs-target="#customer" type="button">
                                    <i class="bi bi-person me-2"></i>Detail Pelanggan
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="detail" role="tabpanel">

                                <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">Klasifikasi Jasa</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <small class="text-muted d-block mb-1">Layanan Jasa</small>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded bg-white p-2 me-3 text-primary border shadow-sm">
                                                    <i class="bi bi-briefcase fs-5"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $permohonan->layanan_jasa->nama_layanan }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <small class="text-muted d-block mb-1">Jenis Layanan</small>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded bg-white p-2 me-3 text-success border shadow-sm">
                                                    <i class="bi bi-check-circle fs-5"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $permohonan->jenis_layanan_parent->name }} - {{ $permohonan->jenis_layanan->name }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <small class="text-muted d-block mb-1">Jenis TLD</small>
                                            <span class="fw-bold text-dark fs-6">{{ $permohonan->jenisTld->name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <hr class="border-secondary opacity-10 my-4">

                                <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">Spesifikasi Kontrak</h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="d-flex mb-3 align-items-center">
                                            <i class="bi bi-file-text me-3 text-secondary fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">Tipe Kontrak</small>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 mt-1">
                                                    {{ $permohonan->tipe_kontrak }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex mb-3 align-items-center">
                                            <i class="bi bi-calendar-range me-3 text-secondary fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">Periode Pemakaian</small>
                                                <div class="d-flex gap-1 flex-column">
                                                    <div class="fw-bold text-dark" id="periode-pemakaian">3 Periode</div>
                                                    <div>
                                                        <button class="btn btn-xs btn-link text-decoration-none p-0" id="btn-periode">
                                                            <i class="bi bi-eye"></i> Lihat
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($permohonan->periode_next)
                                    <div class="col-md-6">
                                        <div class="d-flex mb-3 align-items-center">
                                            <i class="bi bi-calendar-range me-3 text-secondary fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">Periode Pemakaian Selanjutnya</small>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-dark" id="periode-pemakaian-next">3 Periode</span>
                                                    <button class="btn btn-xs btn-link text-decoration-none p-0" id="btn-periode-next">
                                                        <i class="bi bi-eye"></i> Lihat
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-people me-3 text-secondary fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">Jumlah Pengguna</small>
                                                <span class="fw-bold text-dark fs-5">{{ $permohonan->jumlah_pengguna }}</span>
                                                <small class="text-muted">Orang</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-speedometer2 me-3 text-secondary fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">Jumlah Kontrol</small>
                                                <span class="fw-bold text-dark fs-5">{{ $permohonan->jumlah_kontrol }}</span>
                                                <small class="text-muted">Unit</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="border-secondary opacity-10 my-4">

                                <div class="d-flex justify-content-between mb-3 align-items-center">
                                    <h6 class="text-uppercase text-muted small fw-bold tracking-wide mb-0">Daftar Pemakai TLD</h6>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" id="jumlah-pengguna">
                                        1 Orang
                                    </span>
                                </div>

                                {{-- <div class="row text-muted small fw-bold mb-2 px-3 d-none d-md-flex">
                                    <div class="col-1">#</div>
                                    <div class="col-4">Nama Personil</div>
                                    <div class="col-4">Spesifikasi Alat</div>
                                    <div class="col-3 text-end">Kode TLD</div>
                                </div> --}}

                                <div id="pengguna-list-container"></div>

                                <hr class="border-secondary opacity-10 my-4">

                                <div>
                                    <div class="d-flex justify-content-between mb-2 pe-3 align-items-center">
                                        <h6 class="text-uppercase text-muted small fw-bold tracking-wide">TLD Kontrol</h6>
                                        <div class="">
                                            <i class="bi bi-speedometer"></i>
                                        </div>
                                    </div>
                                    <div class="card bg-light border-0">
                                        <div id="tld-kontrol-content"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="customer" role="tabpanel">
                                <div class="row g-4 mb-4">

                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-4 border h-100 position-relative overflow-hidden">

                                            <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">
                                                <i class="bi bi-person-circle me-1 text-primary"></i>Penanggung Jawab (PIC)
                                            </h6>

                                            <div class="d-flex align-items-center mb-4">
                                                <div class="rounded-circle bg-white text-primary border shadow-sm d-flex justify-content-center align-items-center me-3 flex-shrink-0"
                                                    style="width: 56px; height: 56px; font-size: 1.5rem; font-weight: bold;">
                                                    S
                                                </div>
                                                <div>
                                                    <h5 class="fw-bold text-dark mb-0" id="nama-pic">Supri</h5>
                                                    <small class="text-muted" id="jabatan-pic">Bagian Umum / Staff</small>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Nomor WhatsApp / Telepon</small>
                                                    <span class="fw-bold text-dark font-monospace" id="telepon-pic">-</span>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Email Pribadi</small>
                                                    <span class="fw-bold text-dark" id="email-pic">supri.driver@gmail.com</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-4 border h-100 shadow-sm">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="text-uppercase text-muted small fw-bold tracking-wide">
                                                    <i class="bi bi-building me-1 text-danger"></i>Data Instansi
                                                </h6>
                                                <span class="badge border border-success-subtle rounded-pill" id="status-instansi">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Terverifikasi
                                                </span>
                                            </div>

                                            <h5 class="fw-bold text-dark mb-3" id="nama-instansi">PT. Rachman Abadi</h5>
                                            <p class="text-muted small mb-3 d-none">Perusahaan Jasa Konstruksi & Umum</p>

                                            <div class="row g-2 p-3 bg-light rounded-3 border">
                                                <div class="col-6 border-end">
                                                    <small class="text-muted d-block mb-1" style="font-size: 0.7rem;">Kode Instansi</small>
                                                    <div class="d-flex align-items-center">
                                                        <span class="fw-bold text-dark font-monospace small me-2" id="kodeInstansi">-</span>
                                                        <i class="bi bi-copy text-secondary cursor-pointer" style="font-size: 0.8rem;" title="Salin" onclick="copyKode('kodeInstansi')"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6 ps-3">
                                                    <small class="text-muted d-block mb-1" style="font-size: 0.7rem;">NPWP Perusahaan</small>
                                                    <span class="fw-bold text-dark font-monospace small" id="npwp-pic"></span>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Email Resmi Instansi</small>
                                                <a href="#" class="text-decoration-none fw-bold small" id="email-perusahaan">contact@rachman.com</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="border-secondary opacity-10 my-4">

                                <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">
                                    <i class="bi bi-map me-1 text-info"></i>Logistik & Pengiriman
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="card h-100 border border-primary">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle bg-white p-1 text-secondary me-2 shadow-sm border" style="width: 28px; height: 28px; display:grid; place-items:center;">
                                                        <i class="bi bi-geo-alt-fill" style="font-size: 0.7rem;"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark small">Alamat Kantor Utama</span>
                                                </div>
                                                <p class="text-muted small mb-0" style="line-height: 1.4;" id="alamat-Utama">
                                                    -
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle bg-white p-1 text-secondary me-2 shadow-sm border" style="width: 28px; height: 28px; display:grid; place-items:center;">
                                                        <i class="bi bi-geo-alt-fill" style="font-size: 0.7rem;"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark small">Alamat TLD</span>
                                                </div>
                                                <p class="text-muted small mb-0" style="line-height: 1.4;" id="alamat-tld">
                                                    -
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle bg-white p-1 text-secondary me-2 shadow-sm border" style="width: 28px; height: 28px; display:grid; place-items:center;">
                                                        <i class="bi bi-geo-alt-fill" style="font-size: 0.7rem;"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark small">Alamat LHU</span>
                                                </div>
                                                <p class="text-muted small mb-0" style="line-height: 1.4;" id="alamat-lhu">
                                                    -
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle bg-white p-1 text-secondary me-2 shadow-sm border" style="width: 28px; height: 28px; display:grid; place-items:center;">
                                                        <i class="bi bi-receipt" style="font-size: 0.7rem;"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark small">Alamat Invoice</span>
                                                </div>
                                                <p class="text-muted small mb-0" id="alamat-invoice">Sama dengan alamat utama</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white mb-3"
                    style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <div class="card-body p-4 text-center">
                        <small class="text-white-50 text-uppercase fw-bold">Total Estimasi Harga</small>
                        <h2 class="fw-bold my-2">{{ formatCurrency($permohonan->total_harga) }}</h2>
                        <span class="badge bg-white bg-opacity-25 text-white fw-normal border border-light border-opacity-25">
                            Belum Termasuk PPN
                        </span>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 rounded-top-4">
                        <h6 class="fw-bold text-dark m-0">
                            <i class="bi bi-paperclip me-2 text-danger"></i>Berkas Kelengkapan
                        </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="show-tandaterima" class="d-none p-3 bg-white rounded-3 border border-success border-opacity-25 shadow-sm position-relative overflow-hidden transition-all hover-shadow">

                            <div class="position-absolute top-0 start-0 h-100 bg-success" style="width: 4px;"></div>

                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden me-3">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate" title="Tanda Terima Pengujian">
                                        Tanda Terima Pengujian
                                    </h6>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <button class="btn btn-sm btn-outline-primary border-0 bg-primary-subtle text-primary"
                                            title="Lihat File" id="btn-show-tandaterima">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>

                                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-tandaterima">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="tambah-tandaterima" class="border-2 border-dashed border-secondary border-opacity-25 rounded-3 p-4 text-center bg-light mb-3">
                            <i class="bi bi-file-earmark-plus text-muted fs-3 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark small mb-1">Tanda Terima *</h6>
                            <small class="text-muted d-block mb-3" style="font-size: 0.75rem;">Belum ada dokumen</small>
                            <button id="btn-tandaterima" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-upload me-1"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <div class="sticky-sidebar">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-light border-0 pt-3 pb-2 text-center">
                            <h6 class="fw-bold text-uppercase text-secondary mb-0">
                                <i class="fas fa-stamp me-2"></i>Validasi Front Desk
                            </h6>
                        </div>

                        <div class="card-body p-4 text-center" id="validasi-frontdesk">
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Tindakan</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary py-2 fw-bold" onclick="verif_kelengkapan('lengkap', this)">
                                    <i class="bi bi-check me-2"></i>Lengkap
                                </button>
                                <button class="btn btn-outline-danger py-2" onclick="verif_kelengkapan('tidak_lengkap', this)">
                                    <i class="bi bi-x me-2"></i>Tidak Lengkap
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal select tld --}}
<div class="modal fade" id="modal-verif-invalid" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Data tidak lengkap</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row justify-content-center">
                <div class="row">
                    <div class="col-md-12">
                        <label class="col-form-label" for="txt_note">Note</label>
                        <textarea name="txt_note" id="txt_note" rows="3" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="return_permohonan(this)">Return</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-select-tld" tabindex="-1" aria-labelledby="modal-select-tldLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-select-tldLabel">List TLD</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <button class="btn btn-outline-secondary btn-sm mb-2" id="btnSelectAllTld">
                    <input class="form-check-input" type="checkbox" id="selectAllTld" checked>
                    Pilih semua
                </button>
                <ul class="list-group shadow-sm" id="listTldSelect">

                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="simpanTldPermohonan(this)" id="btnPilihTld">Pilih</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-tandaterima" tabindex="-1" aria-labelledby="modal-tandaterimaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-tandaterimaLabel">List Tanda Terima</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row mt-2" id="content-pertanyaan"></form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="simpanTandaTerimaPermohonan(this)" id="btnPilihTandaTerima">Simpan</button>
            </div>
        </div>
    </div>
</div>

@include('pages.management.tld.create')
@endsection
@push('scripts')
    <script>
        const dataPermohonan = @json($permohonan);
        const tandaterima = @json($pertanyaan);
        const isEvaluasi = "{{ $isEvaluasi }}";
    </script>
    <script src="{{ asset('js/staff/verifikasi_permohonan.js') }}"></script>
@endpush
