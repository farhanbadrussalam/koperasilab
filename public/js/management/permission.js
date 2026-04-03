let datatable_permission = false;
$(function(){
    datatable_permission = $('#permission-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: `${base_url}/management/getDataPermission`,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: true, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ]
    });
})

$('#form-edit').on("submit", (evt) => {
    evt.preventDefault();
    const formData = new FormData(evt.target);
    spinner('show', $('#btn-edit'));

    ajaxPost(`management/permission/update`, formData, result => {
        $('#editPermissionModal').modal('hide');
        resetForm();
        spinner('hide', $('#btn-edit'));
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            datatable_permission?.ajax.reload();
        })
    }, error => {
        spinner('hide', $('#btn-edit'));
    })
})

$('#form-create').on("submit", (evt) => {
    evt.preventDefault();
    const formData = new FormData(evt.target);
    spinner('show', $('#btn-create'));

    ajaxPost(`management/permission`, formData, result => {
        $('#create_modal').modal('hide');
        resetForm();
        spinner('hide', $('#btn-create'));
        Swal.fire({
            icon: 'success',
            text: result.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            datatable_permission?.ajax.reload();
        })
    }, error => {
        spinner('hide', $('#btn-create'));
    });
});
function btnEdit(obj) {
    let idPermission = $(obj).data('id');
    let value = $(obj).data('value');

    $('#editPermissionModal').modal('show');

    $('#inputEditNamePermission').val(value);
    $('#inputEditIdPermission').val(idPermission);
}

function resetForm () {
    $('#form-create')[0].reset();
    $('#form-edit')[0].reset();
}

function btnDelete(obj) {
    let id = $(obj).data('id');
    ajaxDelete(`management/permission/${id}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            showConfirmButton: false
        }).then(() => {
            datatable_permission?.ajax.reload();
        })
    })
}
