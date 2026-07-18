@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content col-md-12">
            <div class="container">
                <ul class="nav nav-tabs" id="myTab">
                    <li class="nav-item"><button class="nav-link active" role="tab" data-bs-toggle="tab"
                            onclick="switchLoadTab(1)">Pengajuan <span class="badge bg-secondary ms-1" id="countPengajuan"></span></button></li>
                    <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab"
                            onclick="switchLoadTab(6)">Faktur <span class="badge bg-secondary ms-1" id="countFaktur"></span></button></li>
                    <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab"
                            onclick="switchLoadTab(2)">Pembayaran <span class="badge bg-secondary ms-1" id="countPembayaran"></span></button></li>
                    <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab"
                            onclick="switchLoadTab(3)">Verifikasi <span class="badge bg-secondary ms-1" id="countVerifikasi"></span></button></li>
                    <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab"
                            onclick="switchLoadTab(4)">Diterima <span class="badge bg-secondary ms-1" id="countDiterima"></span></button></li>
                    <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab"
                            onclick="switchLoadTab(5)">Ditolak <span class="badge bg-secondary ms-1" id="countDitolak"></span></button></li>
                </ul>
                <div class="card shadow-sm mt-2">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()"><i
                                        class="bi bi-arrow-clockwise"></i> Refresh data</button>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-secondary btn-sm rounded-start-pill"
                                        data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                                        <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none"
                                            id="countFilter">4</span>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="list-filter"></div>
                        <div class="my-3">
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
    <script src="{{ asset_versioned('js/staff/keuangan.js') }}"></script>
@endpush
