let datatable_radiasi = false;
let filterComp = false;
$(function () {
    datatable_radiasi = $('#radiasi-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/management/getDataRadiasi`,
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
            { data: 'nama_radiasi', name: 'nama_radiasi' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' },
        ]
    })

    filterComp = new FilterComponent('list-filter', {
        filter : {
            search: true
        },
        placeholder: {
            search: 'Cari Radiasi...'
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => datatable_radiasi?.ajax.reload());

    $('#createRadiasiModal').on('hide.bs.modal', resetForm);
    $('#editRadiasiModal').on('hide.bs.modal', resetForm);

    $('#form-create').on("submit", (evt) => {
        evt.preventDefault();
        const formData = new FormData(evt.target);
        spinner('show', $('#btn-create'));
        ajaxPost(`management/radiasi`, formData, result => {
            if (result.meta.code == 200) {
                Swal.fire({
                    icon: 'success',
                    text: result.data.msg,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    $('#createRadiasiModal').modal('hide');
                    datatable_radiasi.ajax.reload();
                    spinner('hide', $('#btn-create'));
                    resetForm();
                })
            }
        }, error => {
            spinner('hide', $('#btn-create'));
        });
    });

    $('#form-edit').on("submit", (evt) => {
        evt.preventDefault();
        const formData = new FormData(evt.target);
        spinner('show', $('#btn-edit'));
        ajaxPost(`management/radiasi/update`, formData, result => {
            if (result.meta.code == 200) {
                Swal.fire({
                    icon: 'success',
                    text: result.data.msg,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    $('#editRadiasiModal').modal('hide');
                    datatable_radiasi.ajax.reload();
                    spinner('hide', $('#btn-edit'));
                    resetForm();
                })
            }
        }, error => {
            spinner('hide', $('#btn-edit'));
        });
    });
})

function btnEdit(el) {
    let id = $(el).data('id');
    ajaxGet(`management/radiasi/${id}/edit`, false, result => {
        $('#editRadiasiModal').modal('show');
        $('#id_radiasi').val(result.data.radiasi_hash);
        $('#inputNamaRadiasiEdit').val(result.data.nama_radiasi);
    });
}

function btnDelete(el) {
    let id = $(el).data('id');
    ajaxDelete(`management/radiasi/${id}`, result => {
        if (result.meta.code == 200) {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                datatable_radiasi.ajax.reload();
            })
        }
    })
}

function resetForm() {
    $('#form-create').trigger('reset');
    $('#form-edit').trigger('reset');
}

function reload() {
    datatable_radiasi.ajax.reload();
}

function clearFilter(){
    filterComp.clear();
    reload();
}
