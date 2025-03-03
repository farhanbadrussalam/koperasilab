@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Tld</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="card p-0 m-0 shadow border-0">
            <div class="card-header d-flex ">
                <h3 class="card-title flex-grow-1">
                  TLD
                </h3>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTldModal"><i class="bi bi-plus"></i> Create Tld</button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatus" aria-label=".form-select-sm example">
                                <option value="" selected>All Status</option>
                                <option value="1">Digunakan</option>
                                <option value="2">Tidak digunakan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterJenis" aria-label=".form-select-sm example">
                                <option value="" selected>All Jenis</option>
                                <option value="kontrol" >Kontrol</option>
                                <option value="pengguna">Pengguna</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="overflow-y-auto">
                        <table class="table table-hover w-100 align-middle" id="tld-table">
                            <thead>
                                <th width="5%">No</th>
                                <th>Kode lencana</th>
                                <th width="15%" class="text-center">Status</th>
                                <th width="10%">Jenis</th>
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