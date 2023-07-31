@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Permission</li>
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
                      Permission
                    </h3>
                    <a href="{{ route('permission.create') }}" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPermissionModal">Add permission</a>
                </div>
                <div class="card-body">
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
    </section>
</div>
@include('pages.permission.create')
@include('pages.permission.edit')
@endsection
@push('scripts')
    <script>
        let datatable_permission = false;
        $(function(){
            datatable_permission = $('#permission-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('permission.getData') }}",
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
            let id = formData.get('id_permission');
            let url = `{{ url('permission/update') }}`;

            $.ajax({
                method: "POST",
                url: url,
                processData: false,
                contentType: false,
                data: formData
            }).done((result) => {
                toastr.success(result.message);
                $('#editPermissionModal').modal('hide');
                datatable_permission?.ajax.reload();
            })
        })
        function btnEdit(obj) {
            let idPermission = $(obj).data('id');
            let value = $(obj).data('value');

            $('#editPermissionModal').modal('show');

            $('#inputEditNamePermission').val(value);
            $('#inputEditIdPermission').val(idPermission);
        }

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/permission') }}/"+id,
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
                        datatable_permission?.ajax.reload();
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
