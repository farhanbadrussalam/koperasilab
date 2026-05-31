@extends('layouts.main')

@section('content')
    <style>
        .hover-scale {
            transition: all 0.2s ease-in-out;
        }
        .hover-scale:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.25) !important;
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.03);
            transition: background-color 0.2s ease-in-out;
        }
    </style>

    <div class="card p-0 m-0 shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary-subtle text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark">Users Management</h4>
                        <p class="text-muted small mb-0">Kelola informasi akun pengguna, NIK, penugasan Satuan Kerja, dan peran sistem.</p>
                    </div>
                </div>
                <a href="{{ route('users.create') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2 hover-scale transition-all col-auto">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Tambah Pengguna</span>
                </a>
            </div>
        </div>
        <div class="card-body px-4 pb-4 pt-3">
            <div class="d-flex mb-3">
                <div class="flex-grow-1">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 transition-all" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-secondary btn-sm rounded-start-pill px-3" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
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
                    <table class="table table-hover w-100 align-middle table-borderless border-bottom" id="user-table">
                        <thead class="table-light">
                            <th class="text-center py-3">No</th>
                            <th class="py-3" width="20%">Name</th>
                            <th class="py-3" width="20%">Email</th>
                            <th class="py-3" width="20%">Satuan Kerja</th>
                            <th class="py-3" width="20%">Role</th>
                            <th class="py-3 text-center" width="10%">Action</th>
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
