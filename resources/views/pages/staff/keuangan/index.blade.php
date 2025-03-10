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
            <div class="card shadow-sm m-4">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="clearFilter()">
                                <i class="bi bi-funnel"></i> Clear Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span></button>
                        </div>
                    </div>
                    <div id="list-filter"></div>
                    <div class="my-3">
                        <div class="header px-3 fw-bolder d-none d-md-flex row">
                            <div class="col-md-3">Layanan</div>
                            <div class="col-md-2">Jenis</div>
                            <div class="col-md-3">Tipe</div>
                            <div class="col-md-2">Status</div>
                            <div class="col-md-2 text-center">Action</div>
                        </div>
                        <hr>
                        <div class="body-placeholder my-3" id="list-placeholder">
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
                        <div class="body my-3" id="list-container">

                        </div>
                        <div aria-label="Page navigation example" id="list-pagination">
                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
@push('scripts')
    <script src="{{ asset('js/staff/keuangan.js') }}"></script>
@endpush