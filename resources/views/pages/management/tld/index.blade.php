@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="card p-0 m-0 shadow border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
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
                    <button class="btn btn-primary btn-sm rounded-pill" id="btn-create-tld"><i class="bi bi-plus"></i> Create Tld</button>
                </div>
                <div id="list-filter"></div>
                <div class="row mt-2">
                    <div class="overflow-y-auto">
                        <table class="table table-hover w-100 align-middle" id="tld-table">
                            <thead>
                                <th width="5%">No</th>
                                <th>No Seri TLD</th>
                                <th width="20%">Jenis</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Action</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
    @include('pages.management.tld.create')
    @include('pages.management.tld.edit')
@endsection
@push('scripts')
    <script src="{{ asset('js/management/tld.js') }}"></script>
@endpush
