let datatable_permission = false;
let filterComp = false;
$(function(){
    datatable_permission = $('#permission-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/management/getDataPermission`,
            type: "GET",
            data: function(params) {
                let filterValue = filterComp && filterComp.getAllValue();

                params.filter = {};
                filterValue.search && (params.filter.search = filterValue.search);

                if(Object.keys(params.filter).length > 0) {
                    $('#countFilter').html(Object.keys(params.filter).length);
                    $('#countFilter').removeClass('d-none');
                } else {
                    $('#countFilter').addClass('d-none');
                }
                return params
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: true, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' },
        ]
    });

    filterComp = new FilterComponent('list-filter', {
        filter : {
            search: true
        },
        placeholder: {
            search: 'Cari Permission...'
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => datatable_permission?.ajax.reload());
})

$('#form-edit').on("submit", (evt) => {
    evt.preventDefault();
    const formParsley = $('#form-edit').parsley();
    if (!formParsley.validate()) {
        return;
    }
    const formData = new FormData(evt.target);
    const idPermission = $('#inputEditIdPermission').val();
    spinner('show', $('#btn-edit'));

    ajaxPost(`management/permission/${idPermission}`, formData, result => {
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
    const formParsley = $('#form-create').parsley();
    if (!formParsley.validate()) {
        return;
    }
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

    // Reset Parsley classes and alerts on opening edit modal
    const formParsley = $('#form-edit').parsley();
    formParsley.reset();
    $('#form-edit').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');

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

function reload(){
    datatable_permission?.ajax.reload();
}

function clearFilter(){
    filterComp.clear();
    reload();
}
