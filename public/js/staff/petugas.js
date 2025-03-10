let dataTable_petugas = false;
$(function () {
    dataTable_petugas = $('#petugas-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/management/getData`,
            type: 'GET',
            data: function (d) {
                d.role = 'Staff LHU';
                d.satuan_kerja = userActive.satuankerja_hash;
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
});

function reload() {
    dataTable_petugas.ajax.reload();
}