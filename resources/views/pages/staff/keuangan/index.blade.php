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
                    test 2
                </div>
                <div class="tab-pane fade" id="verifikasi-tab-pane" role="tabpanel" aria-labelledby="verifikasi-tab" tabindex="0">
                    test 3
                </div>
                <div class="tab-pane fade" id="diterima-tab-pane" role="tabpanel" aria-labelledby="diterima-tab" tabindex="0">
                    test 4
                </div>
                <div class="tab-pane fade" id="ditolak-tab-pane" role="tabpanel" aria-labelledby="ditolak-tab" tabindex="0">
                    test 5
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/staff/keuangan.js') }}"></script>
@endpush