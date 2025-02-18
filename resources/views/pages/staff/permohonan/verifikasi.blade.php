@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content col-md-12">
        <div class="container">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item px-3">
                    <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="icon-link text-danger"><i class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="detailPermohonan-tab" onclick="" data-bs-toggle="tab" data-bs-target="#detailPermohonan-tab-pane" type="button" role="tab" aria-controls="detailPermohonan-tab-pane" aria-selected="true">Detail Permohonan</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="detailPelanggan-tab" onclick="" data-bs-toggle="tab" data-bs-target="#detailPelanggan-tab-pane" type="button" role="tab" aria-controls="detailPelanggan-tab-pane" aria-selected="true">Detail Pelanggan</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="detailPermohonan-tab-pane" role="tabpanel" aria-labelledby="detailPermohonan-tab" tabindex="0">
                    <div class="card card-default border-0 color-palette-box shadow py-3">
                        <div class="card-body row">
                            <div class="col-md-4 col-12">
                                <h2>Informasi</h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="layanan-jasa" class="col-form-label">Layanan jasa</label>
                                        <input type="text" name="layanan-jasa" id="layanan-jasa" class="form-control bg-secondary-subtle" value="{{ $permohonan->layanan_jasa->nama_layanan }}" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="jenis-layanan" class="col-form-label">Jenis Layanan</label>
                                        <input type="text" name="jenis-layanan" id="jenis-layanan" class="form-control bg-secondary-subtle" value="{{ $permohonan->jenis_layanan_parent->name }} - {{ $permohonan->jenis_layanan->name }}" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="jenis-tld" class="col-form-label">Jenis TLD</label>
                                        <input type="text" name="jenis-tld" id="jenis-tld" class="form-control bg-secondary-subtle" value="{{ $permohonan->jenisTld->name }}" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="tipe-kontrak" class="col-form-label">Tipe Kontrak</label>
                                        <input type="text" name="tipe-kontrak" id="tipe-kontrak" class="form-control bg-secondary-subtle" value="{{ $permohonan->tipe_kontrak }}" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="col-form-label" for="periode-pemakaian">Periode pemakaian</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control bg-secondary-subtle" id="periode-pemakaian" aria-label="Periode pemakaian" value="" readonly>
                                            @if($permohonan->periode_pemakaian)
                                            <button class="btn btn-outline-secondary" type="button" id="btn-periode">Show periode</button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="jum-pengguna" class="col-form-label">Jumlah Pengguna</label>
                                        <input type="text" name="jum-pengguna" id="jum-pengguna" class="form-control bg-secondary-subtle" value="{{ $permohonan->jumlah_pengguna }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="jum-kontrol" class="col-form-label">Jumlah Kontrol</label>
                                        <input type="text" name="jum-kontrol" id="jum-kontrol" class="form-control bg-secondary-subtle" value="{{ $permohonan->jumlah_kontrol }}" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="jum-kontrol" class="col-form-label">Total harga</label>
                                        <input type="text" name="jum-kontrol" id="jum-kontrol" class="form-control bg-secondary-subtle" value="{{ formatCurrency($permohonan->total_harga) }}" readonly>
                                        @if($permohonan->tipe_kontrak == 'kontrak baru')
                                        <small class="text-info">*Belum termasuk PPN</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-default border-0 color-palette-box shadow py-3 mt-2">
                        <div class="card-body row">
                            <div class="col-12">
                                <h2 class="text-center">TLD Kontrol</h2>
                            </div>
                            <div class="col-12 overflow-auto" style="max-height: 25rem;">
                                <div id="tld-kontrol-content" class="row"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-default border-0 color-palette-box shadow py-3 mt-2">
                        <div class="card-body row">
                            <div class="col-12">
                                <h2 class="text-center">Daftar nama pemakai TLD</h2>
                            </div>
                            <div class="col-12 overflow-auto" style="max-height: 25rem;">
                                <div class="body-placeholder my-3" id="pengguna-placeholder">
                                    @for ($i = 1; $i < 4; $i++)
                                    <div class="card mb-2 shadow-sm border-dark">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-md-4 lh-sm d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-25 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-md-3">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-md-3">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-md-2 text-end">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <table class="table w-100 d-none" id="pengguna-table">
                                    <thead>
                                        <tr>
                                            <th width="1%">No</th>
                                            <th width="20%">Nama</th>
                                            <th width="40%">Radiasi</th>
                                            <th width="20%">Kode Lencana TLD</th>
                                            <th width="10%">ktp</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pengguna-list-container" class="align-middle"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card card-default border-0 color-palette-box shadow py-3 mt-2">
                        <div class="card-body">
                            <input type="hidden" id="status_tandaterima" value="false">
                            <h2 class="text-center">TANDA TERIMA <span class="text-danger">*</span></h2>
                            <div id="tambah-tandaterima">
                                <button type="button" class="btn btn-outline-secondary w-100 border-dashed" id="btn-tandaterima"><i class="bi bi-plus"></i> Tambah Tanda Terima</button>
                            </div>
                            <div id="show-tandaterima" class="d-none">
                                <table class="table w-100 table-hover">
                                    <thead>
                                        <tr>
                                            <th width="1%">No</th>
                                            <th>Nama</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tandaterima-list-container" class="align-middle">
                                        <tr>
                                            <td>1</td>
                                            <td>Tanda Terima Pengujian</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-outline-success btn-sm" id="btn-show-tandaterima"><i class="bi bi-eye"></i></button>
                                                {{-- <button type="button" class="btn btn-outline-warning btn-sm" id="btn-edit-tandaterima"><i class="bi bi-pencil"></i></button> --}}
                                                <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-tandaterima"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="detailPelanggan-tab-pane" role="tabpanel" aria-labelledby="detailPelanggan-tab" tabindex="0">
                    <div class="card card-default border-0 color-palette-box shadow py-3">
                        <div class="card-body row">
                            <div class="col-md-4 col-12">
                                <h2>Data Pelanggan</h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="nama-instansi" class="col-form-label">Nama instansi</label>
                                        <input type="text" name="nama-instansi" id="nama-instansi" class="form-control bg-secondary-subtle" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="nama-pic" class="col-form-label">Nama PIC</label>
                                        <input type="text" name="nama-pic" id="nama-pic" class="form-control bg-secondary-subtle" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="jabatan-pic" class="col-form-label">Jabatan PIC</label>
                                        <input type="text" name="jabatan-pic" id="jabatan-pic" class="form-control bg-secondary-subtle" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="col-form-label">Email</label>
                                        <input type="text" name="email-pic" id="email-pic" class="form-control bg-secondary-subtle" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="telepon" class="col-form-label">Telepon</label>
                                        <input type="text" name="telepon-pic" id="telepon-pic" class="form-control bg-secondary-subtle" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="npwp" class="col-form-label">NPWP</label>
                                        <input type="text" name="npwp-pic" id="npwp-pic" class="form-control bg-secondary-subtle" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-default border-0 color-palette-box shadow py-3 mt-3">
                        <div class="card-body row">
                            <div class="col-md-4 col-12">
                                <h2>Alamat</h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="alamat-utama" class="col-form-label">Alamat Utama</label>
                                        <textarea name="alamat-utama" id="alamat-utama" cols="30" rows="3" class="form-control bg-secondary-subtle" readonly></textarea>
                                        <input type="text" class="form-control mt-2 bg-secondary-subtle" data-field="kode_pos" placeholder="Kode pos" id="txt-kode-pos-utama" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="alamat-tld" class="col-form-label">Alamat TLD</label>
                                        <textarea name="alamat-tld" id="alamat-tld" cols="30" rows="3" class="form-control bg-secondary-subtle" readonly></textarea>
                                        <input type="text" class="form-control mt-2 bg-secondary-subtle" data-field="kode_pos" placeholder="Kode pos" id="txt-kode-pos-tld" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="alamat-lhu" class="col-form-label">Alamat LHU</label>
                                        <textarea name="alamat-lhu" id="alamat-lhu" cols="30" rows="3" class="form-control bg-secondary-subtle" readonly></textarea>
                                        <input type="text" class="form-control mt-2 bg-secondary-subtle" data-field="kode_pos" placeholder="Kode pos" id="txt-kode-pos-lhu" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="alamat-invoice" class="col-form-label">Alamat Invoice</label>
                                        <textarea name="alamat-invoice" id="alamat-invoice" cols="30" rows="3" class="form-control bg-secondary-subtle" readonly></textarea>
                                        <input type="text" class="form-control mt-2 bg-secondary-subtle" data-field="kode_pos" placeholder="Kode pos" id="txt-kode-pos-invoice" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- @if($permohonan->jenis_layanan->name == 'Sewa')
                <div class="card card-default border-0 color-palette-box shadow py-3 mt-2">
                    <div class="card-body row">
                        <div class="mb-3 col-md-12">
                            <label class="form-label">Upload Document LHU Zero cek<span class="text-danger ml-2">*</span></label>
                            <div id="uploadDocLHU"></div>
                        </div>
                    </div>
                </div>
                @endif --}}
                <div class="card card-default border-0 color-palette-box shadow py-3 mt-2">
                    <div class="card-body row">
                        <div class="col-md-12 d-flex justify-content-center">
                            <div class="wrapper" id="content-ttd-2"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <button class="btn btn-danger" onclick="verif_kelengkapan('tidak_lengkap', this)">Data tidak lengkap</button>
                    </div>
                    <div class="col-6 text-end">
                        <button class="btn btn-primary" onclick="verif_kelengkapan('lengkap', this)">Data Lengkap</button>
                    </div>
                </div>
            </div>
            
        </div>
    </section>
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

@endsection
@push('scripts')
    <script>
        const dataPermohonan = @json($permohonan);
        const tandaterima = @json($pertanyaan);
    </script>
    <script src="{{ asset('js/staff/verifikasi_permohonan.js') }}"></script>
@endpush