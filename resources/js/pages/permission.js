import {} from '../global';

document.addEventListener('DOMContentLoaded', function () {
    // Initialisasi
    const bearer = $('#bearer-token').val();
    const csrf = $('#csrf-token').val();
    const base_url = $('#base_url').val();
    let datatable_permission = $('#permission-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: `${base_url}/getDataPermission`,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    // Method
    console.log("sini")

    // Event
})
