let dataTable_petugas = false;
let filterComp = false;

$(function () {
    filterComp = new FilterComponent('list-filter', {
        jenis: 'petugas',
        filter: {
            search: true,
            tugas: true
        },
        multiple: ['tugas'],
        placeholder: {
            search: 'Cari Nama/Email',
            tugas: 'Semua Tugas'
        },
        showOnLoad: true
    });

    dataTable_petugas = $('#petugas-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/staff/lhu/petugas/getData`,
            type: 'GET',
            data: function (d) {
                d.role = ['Staff LHU', 'Staff Penyelia'];
                let filterValue = filterComp && filterComp.getAllValue();
                d.filter = {};

                if (filterValue) {
                    filterValue.search && (d.filter.search = filterValue.search);
                    filterValue.tugas && (d.filter.tugas = filterValue.tugas);
                }
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'  },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'tugas', name: 'tugas', searchable: false, className: 'text-center' },
            // { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    filterComp.on('filter.change', () => {
        dataTable_petugas.ajax.reload();
    });
});

function reload() {
    dataTable_petugas.ajax.reload();
}

function clearFilter() {
    filterComp.clear();
    reload();
}