@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="card p-0 m-0 shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="flex-grow-1">
                            <button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i
                                    class="bi bi-arrow-clockwise"></i> Refresh data</button>
                        </div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modal-add-pengguna"><i class="bi bi-plus"></i> Create Pengguna</button>
                    </div>
                    <div class="mb-3" id="list-filter"></div>
                    <div class="row mt-2">
                        <div class="overflow-y-auto">
                            <table class="table table-hover w-100 align-middle" id="pengguna-table">
                                <thead>
                                    <th width="5%">No</th>
                                    <th>Name</th>
                                    <th width="25%" class="text-center">Radiasi</th>
                                    <th width="20%">Posisi</th>
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
    @include('pages.management.pengguna.create')
@endsection

@push('scripts')
    <script src="{{ asset('js/management/pengguna.js') }}"></script>
@endpush
