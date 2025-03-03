@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container col-md-12">
            <div class="card card-default color-palette-box bg-white shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Roles
                    </h3>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal"><i class="bi bi-plus"></i> Create role</button>
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100 align-middle" id="role-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Name role</th>
                            <th width="20%" class="text-center">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.management.roles.create')
@include('pages.management.roles.edit')
@endsection
@push('scripts')
    <script src="{{ asset('js/management/role.js') }}"></script>
@endpush
