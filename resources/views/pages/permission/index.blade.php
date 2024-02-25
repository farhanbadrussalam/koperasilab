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
@vite(['resources/js/pages/permission.js'])
    <script>
        // let datatable_permission = false;
        $(function(){
            // datatable_permission = $('#permission-table').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     ajax: "{{ route('permission.getData') }}",
            //     columns: [
            //         { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
            //         { data: 'name', name: 'name' },
            //         { data: 'action', name: 'action', orderable: false, searchable: false}
            //     ]
            // });
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
