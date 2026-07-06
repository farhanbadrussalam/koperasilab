@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="card p-0 m-0 shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="flex-grow-1">
                            <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()"><i
                                    class="bi bi-arrow-clockwise"></i> Refresh data</button>
                        </div>
                        <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="tambahPengguna()"><i
                                class="bi bi-plus"></i> Create Pengguna</button>
                    </div>
                    <div class="mb-3" id="list-filter"></div>
                    <div class="row mt-2" style="min-height: 50vh">
                        <table class="table table-hover w-100 align-middle table-borderless border-bottom" id="pengguna-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%" class="text-center py-3">No</th>
                                    <th class="py-3">Informasi Pengguna</th>
                                    <th class="py-3">Divisi</th>
                                    <th class="py-3">Radiasi</th>
                                    <th class="py-3 text-center" width="15%">Status</th>
                                    <th class="py-3 text-center" width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @include('pages.permohonan.pengajuan.tld_pengguna')
@endsection

@push('scripts')
    <script src="{{ asset_versioned('js/management/pengguna.js') }}"></script>
@endpush
