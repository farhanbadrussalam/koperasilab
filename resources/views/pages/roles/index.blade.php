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
        <div class="container col-xl-7 col-md-12">
            <div class="card card-default color-palette-box bg-white shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Roles
                    </h3>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">Add role</button>
                </div>
                <div class="card-body">
                    <table class="table table-hover  w-100" id="role-table">
                        <thead>
                            <th width="5%">ID</th>
                            <th>Name role</th>
                            <th width="20%">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.roles.create')
@include('pages.roles.edit')
@endsection
@push('scripts')
    <script>
        let datatable_role = false;
        $(function(){
            datatable_role = $('#role-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('roles.getData') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'name', name: 'name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            })
        })

        function btnEdit(obj) {
            let idRole = $(obj).data('id');

            $.ajax({
                url: "{{ url('roles') }}/"+idRole,
                method: 'GET',
                dataType: 'json',
                processData: true,
            }).done(result => {
                $('#editRoleModal').modal('show');

                $('#inputEditNameRole').val(result.name);
                $('#inputEditIdRole').val(idRole);

                $('.permissionEdit').attr('checked', false);
                // Permission
                for (const permission of result.permissions) {
                    $(`#checkPermission${permission.id}`).attr('checked', true);
                }
            })
        }

        $('#form-edit').on("submit", (evt) => {
            evt.preventDefault();
            const formData = new FormData(evt.target);
            let id = formData.get('id_role');
            let url = `{{ url('roles/update') }}`;

            $.ajax({
                method: "POST",
                url: url,
                processData: false,
                contentType: false,
                data: formData
            }).done((result) => {
                toastr.success(result.message);
                $('#editRoleModal').modal('hide');
                datatable_role?.ajax.reload();
                $('.permissionEdit').attr('checked', false);
            })
        })

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/roles') }}/"+id,
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
                        datatable_role?.ajax.reload();
                        $('.permissionEdit').attr('checked', false);
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
