@extends('layouts.main')

@section('content')
    <div class="card card-default color-palette-box shadow">
        <div class="d-flex justify-content-between pt-2 me-4">
            <div class="m-3">
                <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()"><i
                        class="bi bi-arrow-clockwise"></i>
                    Refresh data</button>
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse"
                        data-bs-target="#collapseFilter">
                        <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none"
                            id="countFilter">4</span>
                    </button>
                    <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3" id="list-filter"></div>
            <div class="table-responsive">
                <table class="table table-hover w-100 align-middle" id="petugas-table">
                    <thead>
                        <th width="5%" class="text-center">No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th width="30%" class="text-center">Tugas</th>
                        {{-- <th width="10%" class="text-center">Action</th> --}}
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset_versioned('js/staff/petugas.js') }}"></script>
@endpush
