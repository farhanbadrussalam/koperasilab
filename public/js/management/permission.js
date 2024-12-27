let datatable_permission = false;
$(function(){
    datatable_permission = $('#permission-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: `${base_url}/getDataPermission`,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });
})

$('#form-edit').on("submit", (evt) => {
    evt.preventDefault();
    const formData = new FormData(evt.target);
    let url = `${base_url}/permission/update`;

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
            url: `${base_url}/permission/${id}`,
            method: 'DELETE',
            dataType: 'json',
            processData: true,
            data: {
                _token: csrf
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