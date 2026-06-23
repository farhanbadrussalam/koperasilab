@extends('layouts.main')

@section('content')
    <style>
        .hover-scale {
            transition: all 0.2s ease-in-out;
        }

        .hover-scale:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.25) !important;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.03);
            transition: background-color 0.2s ease-in-out;
        }
    </style>

    <div class="card p-0 m-0 shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary-subtle text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                        style="width: 45px; height: 45px;">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark">Permissions</h4>
                        <p class="text-muted small mb-0">Kelola dan batasi hak akses sistem secara granular berdasarkan peran
                            pengguna.</p>
                    </div>
                </div>
                <button
                    class="btn btn-primary px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2 hover-scale transition-all"
                    data-bs-toggle="modal" data-bs-target="#create_modal">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Hak Akses</span>
                </button>
            </div>
        </div>
        <div class="card-body px-4 pb-4 pt-3">
            <div class="d-flex mb-3">
                <div class="flex-grow-1">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 transition-all" onclick="reload()"><i
                            class="bi bi-arrow-clockwise"></i> Refresh data</button>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-secondary btn-sm rounded-start-pill px-3" data-bs-toggle="collapse"
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
            <div id="list-filter"></div>
            <div class="row mt-2">
                <div class="overflow-y-auto">
                    <table class="table table-hover w-100 align-middle table-borderless border-bottom"
                        id="permission-table">
                        <thead class="table-light">
                            <th width="8%" class="text-center py-3">No</th>
                            <th class="py-3">Name Permission</th>
                            <th width="20%" class="text-center py-3">Action</th>
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
    <script src="{{ asset_versioned('js/management/permission.js') }}"></script>
@endpush
