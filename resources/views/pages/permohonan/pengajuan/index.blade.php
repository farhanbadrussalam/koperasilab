@extends('layouts.main')

@section('content')
<ul class="nav nav-tabs" id="pengajuanTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" type="button" role="tab" aria-selected="true" onclick="switchLoadTab(1)">Semua <span id="countSemua"></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" type="button" role="tab" aria-selected="true" onclick="switchLoadTab(2)">Pengajuan <span id="countPengajuan"></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" type="button" role="tab" aria-selected="true" onclick="switchLoadTab(3)">Terverifikasi <span id="countVerifikasi"></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" type="button" role="tab" aria-selected="true" onclick="switchLoadTab(4)">Proses LAB <span id="countLab"></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" type="button" role="tab" aria-selected="true" onclick="switchLoadTab(5)">Selesai <span id="countSelesai"></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" type="button" role="tab" aria-selected="false" onclick="switchLoadTab(6)">Draft <span id="countDraft"></span></button></li>
</ul>
<div class="card shadow-sm mt-2">
    <div class="card-body">
        <div class="d-flex">
            <div class="flex-grow-1">
                <button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearFilter()">
                    <i class="bi bi-funnel"></i> Clear Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span></button>
            </div>
            <a href="{{ route('permohonan.pengajuan.tambah') }}" class="btn btn-primary btn-sm">Buat pengajuan</a>
        </div>
        <div id="pengajuan-filter"></div>
        <div class="my-3">
            <div class="body-placeholder my-3" id="pengajuan-placeholder">
                @for ($i = 0; $i < 5; $i++)
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-75 mb-1"></div>
                        </div>
                        <div class="placeholder-glow col-md-3">
                            <div class="placeholder w-50 mb-1"></div>
                        </div>
                        <div class="placeholder-glow col-md-2 ms-auto">
                            <div class="placeholder w-50 mb-1"></div>
                        </div>
                        <div class="placeholder-glow col-md-2 text-end text-md-start">
                            <div class="placeholder w-50 mb-1"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
            <div class="body my-3" id="pengajuan-list-container"></div>
            <div aria-label="Page navigation example" id="pagination_list"></div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/permohonan/pengajuan.js') }}"></script>
@endpush
