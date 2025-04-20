@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content col-md-12">
        <div class="container">
            <ul class="nav nav-tabs" id="myTab">
                <li class="nav-item"><button class="nav-link active" role="tab" data-bs-toggle="tab" onclick="switchLoadTab(1)">Pengajuan <span id="countPengajuan"></span></button></li>
                <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab" onclick="switchLoadTab(6)">Faktur <span id="countFaktur"></span></button></li>
                <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab" onclick="switchLoadTab(2)">Pembayaran <span id="countPembayaran"></span></button></li>
                <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab" onclick="switchLoadTab(3)">Verifikasi <span id="countVerifikasi"></span></button></li>
                <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab" onclick="switchLoadTab(4)">Diterima <span id="countDiterima"></span></button></li>
                <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab" onclick="switchLoadTab(5)">Ditolak <span id="countDitolak"></span></button></li>
            </ul>
            <div class="card shadow-sm mt-2">
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