@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Permohonan</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-8 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Permohonan layanan
                    </h3>
                    @can('Permohonan.create')
                    <a href="{{ route('permohonan.create') }}" class="btn btn-primary btn-sm">Add permohonan</a>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100" id="permohonan-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Layanan</th>
                            <th>Jadwal</th>
                            <th>Progress</th>
                            <th width="10%">Action</th>
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
        let datatable_permohonan = false;
        $(function () {
            datatable_permohonan = $('#permohonan-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('permohonan.getData') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'nama_layanan', name: 'nama_layanan' },
                    { data: 'jadwal', name: 'jadwal' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });
            datatable_permohonan.on('init.dt', function() {
                maskReload();
                // Lakukan tindakan lain setelah DataTables diinisialisasi
            });
        });
    </script>
@endpush
