@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Otorisasi</li>
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
                      Otorisasi
                    </h3>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createOtorisasiModal">Add otorisasi</button>
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100" id="otorisasi-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Name Otorisasi</th>
                            <th width="20%">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.otorisasi.create')
@include('pages.otorisasi.edit')
@endsection
@push('scripts')
    <script>
        let datatable_otorisasi = false;
        $(function(){
            datatable_otorisasi = $('#otorisasi-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('otorisasi.getData') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'name', name: 'name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });
        })

        $('#form-edit').on("submit", (evt) => {
            evt.preventDefault();
            const formData = new FormData(evt.target);
            let id = formData.get('id_otorisasi');
            let url = `{{ url('otorisasi') }}/${id}`;

            $.ajax({
                method: "POST",
                url: url,
                processData: false,
                contentType: false,
                data: formData
            }).done((result) => {
                toastr.success(result.message);
                $('#editOtorisasiModal').modal('hide');
                datatable_otorisasi?.ajax.reload();
            })
        })

        function btnEdit(obj) {
            let idOtorisasi = $(obj).data('id');
            let value = $(obj).data('value');

            $('#editOtorisasiModal').modal('show');

            $('#inputEditNameOtorisasi').val(value);
            $('#inputEditIdOtorisasi').val(idOtorisasi);
        }

        function btnDelete(obj) {
            let idOtorisasi = $(obj).data('id');
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/otorisasi') }}/"+idOtorisasi,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    data: {
                        _token: "{{ csrf_token() }}"
                    }
                }).done((result) => {
                    if(result.message){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        datatable_otorisasi?.ajax.reload();
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
