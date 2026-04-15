@extends('layouts.main')

@section('content')
    <div class="card p-0 m-0 shadow border-0">
        <div class="card-body">
            <div class="row d-flex align-items-center mb-4 px-3">
                <h4 class="col-12 col-md-10">Permission</h4>
                <a class="btn btn-primary col-12 col-md-2" href="javascript:void(0)" data-bs-toggle="modal"
                    data-bs-target="#create_modal">
                    <i class="bi bi-plus-lg"></i>
                    Tambah
                </a>
            </div>
            <div class="d-flex">
                <div class="flex-grow-1">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                            <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span>
                        </button>
                        <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div id="list-filter"></div>
            <div class="row mt-2">
                <div class="overflow-y-auto">
                    <table class="table table-hover w-100 align-middle" id="permission-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Name Permission</th>
                            <th width="20%" class="text-center">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('pages.management.permission.create')
    @include('pages.management.permission.edit')
@endsection
@push('scripts')
    <script src="{{ asset('js/management/permission.js') }}"></script>
@endpush
