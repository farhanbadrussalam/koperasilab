@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Lab</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container col-xl-8 col-md-12">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                    List Lab
                    </h3>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createLabModal">Add lab</button>
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100" id="lab-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Name Lab</th>
                            <th width="20%">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.lab.create')
@include('pages.lab.edit')
@endsection
@push('scripts')
    <script>
        let datatable_lab = false;
        $(function(){
            datatable_lab = $('#lab-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('lab.getData') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'name_lab', name: 'name_lab' },
                    { data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });
        })

        $('#form-edit').on("submit", (evt) => {
            evt.preventDefault();
            const formData = new FormData(evt.target);
            let id = formData.get('id_lab');
            let url = `{{ url('lab') }}/${id}`;

            $.ajax({
                method: "POST",
                url: url,
                processData: false,
                contentType: false,
                data: formData
            }).done((result) => {
                toastr.success(result.message);
                $('#editLabModal').modal('hide');
                datatable_lab?.ajax.reload();
            })
        })
        function btnEdit(obj) {
            let idLab = $(obj).data('id');
            let value = $(obj).data('value');

            $('#editLabModal').modal('show');

            $('#inputEditNameLab').val(value);
            $('#inputEditIdLab').val(idLab);
        }

        function btnDelete(obj) {
            let idLab = $(obj).data('id');
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/lab') }}/"+idLab,
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
                        datatable_lab?.ajax.reload();
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
