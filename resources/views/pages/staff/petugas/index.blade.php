@extends('layouts.main')

@section('content')
<div class="card card-default color-palette-box shadow">
    <div class="d-flex justify-content-between pt-2 me-4">
        <div class="m-3">
            <button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
        </div>
        <div class="mt-3">
            {{-- <a class="btn btn-primary" href="#"><i class="bi bi-plus"></i> Buat petugas</a> --}}
        </div>
    </div>
    <div class="card-body">
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
    <script src="{{ asset('js/staff/petugas.js') }}"></script>
@endpush