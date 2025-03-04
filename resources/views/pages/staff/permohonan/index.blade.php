@extends('layouts.main')

@section('content')
<div class="card shadow-sm m-4">
    <div class="card-body">
        <div class="d-flex">
            <div class="flex-grow-1"><button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button></div>
            <button class="btn btn-outline-secondary btn-sm" onclick="clearFilter()">
                <i class="bi bi-funnel"></i> Clear Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span></button>
        </div>
        <div class="w-100 d-flex flex-wrap my-3 gap-2">
            <div class="me-2 col-3">
                <select name="filterStatus" id="filterStatus" class="form-select form-select-sm">
                    <option value="" selected>All</option>
                    <option value="{{ encryptor('1') }}">Pengajuan</option>
                    <option value="{{ encryptor('5') }}">Selesai</option>
                </select>
            </div>
            <div class="me-2 col-3">
                <select name="filterJenisTld" id="filterJenisTld" class="form-select form-select-sm">
                    <option value="" selected>All</option>
                    @foreach($jenisTld as $value)
                        <option value="{{ $value->jenis_tld_hash }}">{{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="me-2 col-3">
                <select name="filterJenisLayanan" id="filterJenisLayanan" class="form-select form-select-sm">
                    <option value="" selected>All</option>
                    @foreach($jenisLayanan as $value)
                        <option value="{{ $value->jenis_layanan_hash }}">{{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="me-2 col-2">
                <select name="filterJenisLayananChild" id="filterJenisLayananChild" class="form-select form-select-sm">
                    <option value="" selected>All</option>
                </select>
            </div>
            <div class="me-2 col-3">
                <select name="filterSearchKontrak" id="filterSearchKontrak" class="form-select form-select-sm">
                    <option value="" selected>All</option>
                </select>
            </div>
        </div>
        <div class="my-3">
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
    <script src="{{ asset('js/staff/permohonan.js') }}"></script>
@endpush
