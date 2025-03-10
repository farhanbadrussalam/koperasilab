@extends('layouts.main')

@section('content')
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
                <div class="col-md-3">Jenis</div>
                <div class="col-md-2">Tipe</div>
                <div class="col-md-2">Pelanggan</div>
                <div class="col-md-2 text-center">Action</div>
            </div>
            <hr>
            <div class="body-placeholder my-3" id="list-placeholder">
                @for ($i = 0; $i < 5; $i++)
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
@endsection
@push('scripts')
    <script src="{{ asset('js/manager/pengajuan.js') }}"></script>
@endpush