@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container col-md-12">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Users
                    </h3>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Add user</a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select class="form-select" id="filterSatuanKerja" aria-label=".form-select-sm example">
                                    <option value="" selected>All Satuan Kerja</option>
                                    @foreach ($satuankerja as $value)
                                        <option value="{{ $value->satuan_hash }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filterRole" aria-label=".form-select-sm example">
                                    <option value="" selected>All Role</option>
                                    @foreach ($role as $value)
                                        <option value="{{ $value->name }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover w-100 align-middle" id="user-table">
                        <thead>
                            <th class="text-center">No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Satuan Kerja</th>
                            <th>Role</th>
                            <th>Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="tugasModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Tugas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="content-tugas">
        </div>
      </div>
    </div>    
</div>

@endsection
@push('scripts')
    <script>
        $(function () {
            $('#user-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('users.getData') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'satuankerja', name: 'satuankerja' },
                    { data: 'role', name: 'role' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            $('#filterSatuanKerja').on('change', filter);
            $('#filterRole').on('change', filter);

            function filter(){
                let satuanKerja = $('#filterSatuanKerja').val();
                let role = $('#filterRole').val();

                $('#user-table').DataTable().ajax.url(`{{ route('users.getData') }}?satuan_kerja=${satuanKerja}&role=${role}`).load();
            }
        });

        function showTugas(obj){
            let id = $(obj).data('id');

            ajaxGet(`management/getById/${id}`, false, result => {
                if(result.meta.code == 200) {
                    let jobs = result.data.jobs;
                    let content = '';
                    jobs.forEach(element => {
                        content += `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">${element.name}</h5>
                                </div>
                            </div>
                        `;
                    });
                    $('#content-tugas').html(content);
                    $('#tugasModal').modal('show');
                }
            });

        }
    </script>
@endpush
