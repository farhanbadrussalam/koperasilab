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

$(function () {
    // Group select all button logic
    $(document).on('click', '.btn-toggle-group', function(e) {
        e.preventDefault();
        const groupSlug = $(this).data('group');
        const isEditModal = $(this).closest('.modal').attr('id') === 'editRoleModal';
        const checkboxClass = isEditModal ? '.edit-perm-checkbox' : '.create-perm-checkbox';
        const checkboxes = $(checkboxClass + `[data-group-class="${groupSlug}"]`);
        
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !allChecked).trigger('change');
    });
});

function btnEdit(obj) {
    let idRole = $(obj).data('id');

    // Reset Parsley validation and checked states on opening
    const formParsley = $('#form-edit').parsley();
    formParsley.reset();
    $('#form-edit').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    $('input[name="permissionEdit[]"]').prop('checked', false);

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
    const formParsley = $('#form-edit').parsley();
    if (!formParsley.validate()) {
        return;
    }
    const formData = new FormData(evt.target);
    const idRole = $('#inputEditIdRole').val();

    spinner('show', $('#btn-edit'));
    ajaxPost(`management/roles/${idRole}`, formData, result => {
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
    }, error => {
        spinner('hide', $('#btn-edit'));
    })
})

$('#form-create').on("submit", (evt) => {
    evt.preventDefault();
    const formParsley = $('#form-create').parsley();
    if (!formParsley.validate()) {
        return;
    }
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
    
    // reset Parsley validation
    $('#form-create').parsley().reset();
    $('#form-create').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
}

function reload() {
    datatable_role?.ajax.reload();
}

function clearFilter(){
    filterComp.clear();
    reload();
}
