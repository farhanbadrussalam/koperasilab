@extends('layouts.main')

@section('content')
<div class="card p-0 m-0 shadow border-0">
    <div class="card-body">
        <div class="row d-flex align-items-center mb-4 px-3">
            <h4 class="col-12 col-md-10">Permission</h4>
            <a class="btn btn-primary col-12 col-md-2" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#create_modal">
                <i class="bi bi-plus"></i>
                Created
            </a>
        </div>
        <div class="row mt-2">
            <div class="overflow-y-auto">
                <table class="table table-hover w-100" id="permission-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Name Permission</th>
                            <th width="20%">Action</th>
                        </thead>
                    </table>
            </div>
        </div>
    </div>
</div>
@include('pages.permission.create')
@include('pages.permission.edit')
@endsection
@push('scripts')
    <script src="{{ asset('js/management/permission.js') }}"></script>
@endpush
