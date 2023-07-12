@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Layanan Jasa</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-9 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Layanan jasa
                    </h3>
                    <a href="{{ route('layananJasa.create') }}" class="btn btn-primary btn-sm">Add Layanan</a>
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100" id="user-table">
                        <thead>
                            <th>No</th>
                            <th>Jenis Layanan</th>
                            <th>Tarif</th>
                            <th>Action</th>
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
        $(function () {
            // $('#user-table').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     ajax: "{{ route('users.getData') }}",
            //     columns: [
            //         { data: 'id', name: 'id' },
            //         { data: 'name', name: 'name' },
            //         { data: 'email', name: 'email' },
            //         { data: 'role', name: 'role' },
            //         { data: 'action', name: 'action', orderable: false, searchable: false },
            //     ]
            // });
        });
    </script>
@endpush
