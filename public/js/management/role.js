let datatable_role = false;
let filterComp = false;
$(function () {
    datatable_role = $('#role-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/management/getDataRoles`,
            type: "GET",
            data: function (d) {
                let filterValue = filterComp && filterComp.getAllValue();

                d.filter = {};
                filterValue.search && (d.filter.search = filterValue.search);

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
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' },
        ]
    });

    filterComp = new FilterComponent('list-filter', {
        filter : {
            search: true
        },
        placeholder: {
            search: 'Cari Role...'
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => datatable_role?.ajax.reload());
})

function btnEdit(obj) {
    let idRole = $(obj).data('id');

    ajaxGet(`management/roles/${idRole}`, false, result => {
        if (result.meta.code == 200) {
            $('#editRoleModal').modal('show');

            $('#inputEditNameRole').val(result.data.name);
            $('#inputEditIdRole').val(idRole);
            // Permission
            for (const permission of result.data.permissions) {
                $(`#checkPermission${permission.id}`).prop('checked', true);
            }
        }
    })
}

$('#form-edit').on("submit", (evt) => {
    evt.preventDefault();
    const formData = new FormData(evt.target);

    spinner('show', $('#btn-edit'));
    ajaxPost(`management/roles/update`, formData, result => {
        if (result.meta.code == 200) {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                $('#editRoleModal').modal('hide');
                spinner('hide', $('#btn-edit'));
                datatable_role?.ajax.reload();
            })
        }
    })
})

$('#form-create').on("submit", (evt) => {
    evt.preventDefault();
    const formData = new FormData(evt.target);
    spinner('show', $('#btn-create'));

    ajaxPost(`management/roles`, formData, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            $('#createRoleModal').modal('hide');
            resetForm();
            datatable_role?.ajax.reload();
        })
    }, error => {
        spinner('hide', $('#btn-create'));
    });
})

$('#createRoleModal').on('hidden.bs.modal', resetForm);
$('#editRoleModal').on('hidden.bs.modal', resetForm);

function btnDelete(obj) {
    let id = $(obj).data('id');

    ajaxDelete(`management/roles/${id}`, result => {
        if (result.meta.code == 200) {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                datatable_role?.ajax.reload();
            })
        }
    })
}

function resetForm() {
    $('#inputNameRole').val('');
    // reset checkbox permission
    $('input[name="permission[]"]').prop('checked', false);
    $('input[name="permissionEdit[]"]').prop('checked', false);
}

function reload() {
    datatable_role?.ajax.reload();
}

function clearFilter(){
    filterComp.clear();
    reload();
}
