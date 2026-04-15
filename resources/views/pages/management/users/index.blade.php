@extends('layouts.main')

@section('content')
<div class="card p-0 m-0 shadow border-0">
    <div class="card-body">
        <div class="row d-flex align-items-center mb-4 px-3">
            <h4 class="col-12 col-md-10">Users</h4>
            <a href="{{ route('users.create') }}" class="btn btn-primary col-12 col-md-2">
                <i class="bi bi-plus-lg"></i>
                Tambah
            </a>
        </div>
        <div class="d-flex">
            <div class="flex-grow-1">
                <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                        <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span>
                    </button>
                    <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="list-filter"></div>
        <div class="row mt-2">
            <div class="overflow-y-auto">
                <table class="table table-hover w-100 align-middle" id="user-table">
                    <thead>
                        <th class="text-center">No</th>
                        <th width="20%">Name</th>
                        <th width="20%">Email</th>
                        <th width="20%">Satuan Kerja</th>
                        <th width="20%">Role</th>
                        <th width="10%">Action</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
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
        let filterComp = false;
        let datatable_users = null;
        $(function () {
            datatable_users = $('#user-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.getData') }}",
                    type: "GET",
                    data: function (d) {
                        let filterValue = filterComp && filterComp.getAllValue();

                        d.filter = {};
                        filterValue.search && (d.filter.search = filterValue.search);
                        filterValue.satuan_kerja && (d.filter.satuan_kerja = filterValue.satuan_kerja);
                        filterValue.roles && (d.filter.roles = filterValue.roles);

                        if(Object.keys(d.filter).length > 0) {
                            $('#countFilter').html(Object.keys(d.filter).length);
                            $('#countFilter').removeClass('d-none');
                        } else {
                            $('#countFilter').addClass('d-none');
                        }
                        return d
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'satuankerja', name: 'satuankerja' },
                    { data: 'role', name: 'role' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            filterComp = new FilterComponent('list-filter', {
                filter : {
                    search: true,
                    satuan_kerja: true,
                    roles: true
                },
                placeholder: {
                    search: 'Cari User...',
                    satuan_kerja: 'All Satuan Kerja',
                    roles: 'All Role'
                }
            })

            // SETUP FILTER
            filterComp.on('filter.change', () => datatable_users?.ajax.reload());
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

        function reload(){
            datatable_users.ajax.reload();
        }

        function clearFilter(){
            filterComp.clear();
            datatable_users.ajax.reload();
        }
    </script>
@endpush
