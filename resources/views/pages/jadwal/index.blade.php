@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Jadwal</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-5 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Jadwal layanan
                    </h3>
                    <a href="{{ route('jadwal.create') }}" class="btn btn-primary btn-sm">Add jadwal</a>
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100" id="layanan-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Nama Layanan</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Kuota</th>
                            <th>Petugas</th>
                            <th width="20%">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('scripts')
    <script>
        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @elseif (session('error'))
            toastr.error('{{ session('error') }}');
        @endif
    </script>
@endpush
