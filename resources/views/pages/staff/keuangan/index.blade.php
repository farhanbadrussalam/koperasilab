@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content col-md-12">
        <div class="container">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="pengajuan-tab" onclick="switchLoadTab(1)" data-bs-toggle="tab" data-bs-target="#pengajuan-tab-pane" type="button" role="tab" aria-controls="pengajuan-tab-pane" aria-selected="true">Pengajuan</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="pembayaran-tab" onclick="switchLoadTab(2)" data-bs-toggle="tab" data-bs-target="#pembayaran-tab-pane" type="button" role="tab" aria-controls="pembayaran-tab-pane" aria-selected="true">Pembayaran</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="verifikasi-tab" onclick="switchLoadTab(3)" data-bs-toggle="tab" data-bs-target="#verifikasi-tab-pane" type="button" role="tab" aria-controls="verifikasi-tab-pane" aria-selected="true">Verifikasi</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="diterima-tab" onclick="switchLoadTab(4)" data-bs-toggle="tab" data-bs-target="#diterima-tab-pane" type="button" role="tab" aria-controls="diterima-tab-pane" aria-selected="true">Diterima</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="ditolak-tab" onclick="switchLoadTab(5)" data-bs-toggle="tab" data-bs-target="#ditolak-tab-pane" type="button" role="tab" aria-controls="ditolak-tab-pane" aria-selected="true">Ditolak</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="pengajuan-tab-pane" role="tabpanel" aria-labelledby="pengajuan-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Jenis</div>
                                    <div class="col-md-3">Tipe</div>
                                    <div class="col-md-2">Status</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-pengajuan">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-pengajuan">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-pengajuan">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pembayaran-tab-pane" role="tabpanel" aria-labelledby="pembayaran-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Jenis</div>
                                    <div class="col-md-3">Tipe</div>
                                    <div class="col-md-2">Status</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-pembayaran">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-pembayaran">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-pembayaran">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="verifikasi-tab-pane" role="tabpanel" aria-labelledby="verifikasi-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Jenis</div>
                                    <div class="col-md-3">Tipe</div>
                                    <div class="col-md-2">Status</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-verifikasi">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-verifikasi">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-verifikasi">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="diterima-tab-pane" role="tabpanel" aria-labelledby="diterima-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Jenis</div>
                                    <div class="col-md-3">Tipe</div>
                                    <div class="col-md-2">Status</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-diterima">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-diterima">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-diterima">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="ditolak-tab-pane" role="tabpanel" aria-labelledby="ditolak-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Jenis</div>
                                    <div class="col-md-3">Tipe</div>
                                    <div class="col-md-2">Status</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-ditolak">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-ditolak">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-ditolak">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="diskonModal" tabindex="-1" aria-labelledby="diskonModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="diskonModalLabel">Tambah Diskon</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <label class="col-form-label" for="inputNamaDiskon">Nama diskon</label>
                        <input type="text" name="inputNamaDiskon" id="inputNamaDiskon" class="form-control">
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="col-form-label" for="inputJumDiskon">Jumlah diskon %</label>
                        <input type="text" name="inputJumDiskon" id="inputJumDiskon" class="form-control maskNumber" autocomplete="off">
                    </div>
                    <div class="col-md-12 d-flex justify-content-center">
                        <button type="button" class="btn btn-primary" onclick="tambahDiskon()"><i class="bi bi-plus"></i> Tambah</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createInvoiceModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="createInvoiceModalLabel">Buat Invoice</h1>
          <button type="button" class="btn-close" aria-label="Close" onclick="closeInvoice()"></button>
        </div>
        <div class="modal-body px-4">
            <div class="row mx-2">
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">No Invoice</label>
                    <div id="txtNoInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">No Kontrak</label>
                    <div id="txtNoKontrakInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Jenis</label>
                    <div id="txtJenisInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Pengguna</label>
                    <div id="txtPenggunaInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Tipe Kontrak</label>
                    <div id="txtTipeKontrakInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Pelanggan</label>
                    <div id="txtPelangganInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Jenis TLD</label>
                    <div id="txtJenisTldInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Instansi</label>
                    <div id="txtInstansiInvoice">-</div>
                </div>
            </div>
            <hr class="my-2">
            <div class="row">
                <div class="col-md-6 col-12">
                    <div>
                        <label class="col-form-label" for="inputPpn">PPN %</label>
                        <div class="input-group">
                            <input type="text" name="inputPpn" id="inputPpn" class="form-control maskNumber" value="11" autocomplete="off">
                            <span class="input-group-text"><input class="form-check-input m-0" type="checkbox" id="checkPpn"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12 d-flex align-items-end">
                    <button class="btn btn-outline-secondary me-3" data-bs-toggle="modal" data-bs-target="#diskonModal"><i class="bi bi-plus"></i> Tambah Diskon</button>
                    <button class="btn btn-outline-secondary"><i class="bi bi-plus"></i> Tambah Faktur</button>
                </div>
            </div>
            <div class="border rounded p-3 mt-3">
                <table class="table w-100 text-center">
                    <thead>
                        <tr>
                            <th class="text-start" width="40%">Rincian</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Periode (Bulan)</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody id="deskripsiInvoice">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer d-flex justify-content-center">
          <button type="button" class="btn btn-primary" onclick="simpanInvoice(this)">Simpan</button>
        </div>
      </div>
    </div>
</div>


@endsection
@push('scripts')
    <script src="{{ asset('js/staff/keuangan.js') }}"></script>
@endpush