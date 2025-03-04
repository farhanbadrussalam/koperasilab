@extends('layouts.main')

@section('content')
<ul class="nav nav-tabs" id="pengajuanTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pengajuan-tab" data-bs-toggle="tab" data-bs-target="#pengajuan" type="button" role="tab" aria-controls="pengajuan" aria-selected="true" onclick="switchLoadTab(1)">Pengajuan</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft" type="button" role="tab" aria-controls="draft" aria-selected="false" onclick="switchLoadTab(2)">Draft</button>
    </li>
</ul>
<div class="card shadow-sm m-4">
    <div class="card-body">
        <div class="d-flex">
            {{-- <h3 class="card-title flex-grow-1"></h3> --}}
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
            <div class="body my-3" id="pengajuan-list-container">
                
            </div>
            <div aria-label="Page navigation example" id="pagination_list">
                
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/permohonan/pengajuan.js') }}"></script>
@endpush
