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
    <section class="content col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Jadwal layanan
                    </h3>
                    <a href="{{ route('jadwal.create') }}" class="btn btn-primary btn-sm">Add jadwal</a>
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100" id="jadwal-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Nama Layanan</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Kuota</th>
                            <th>Petugas</th>
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
        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @elseif (session('error'))
            toastr.error('{{ session('error') }}');
        @endif

        let datatable_jadwal = false;
        $(function () {
            datatable_jadwal = $('#jadwal-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('jadwal.getData') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'nama_layanan', name: 'nama_layanan' },
                    { data: 'date_mulai', name: 'date_mulai' },
                    { data: 'date_selesai', name: 'date_selesai' },
                    { data: 'kuota', name: 'kuota' },
                    { data: 'petugas_id', name: 'kuota' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });
        });

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/deleteJadwal') }}/"+id,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    }
                }).done((result) => {
                    if(result.message){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        datatable_jadwal?.ajax.reload();
                    }
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                });
            });
        }
    </script>
@endpush