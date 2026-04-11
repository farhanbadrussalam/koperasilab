let datatable_tld = false;
$(function () {
    datatable_tld = $('#tld-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: `${base_url}/management/getDataTld`,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'no_seri_tld', name: 'no_seri_tld' },
            { data: 'status', name: 'status', className: 'text-center' },
            { data: 'jenis', name: 'jenis' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ]
    });

    $('#editTldModal').on('hide.bs.modal', resetForm);

    $('#filterStatus').on('change', filter);
    $('#filterJenis').on('change', filter);

    $('#form-edit').on("submit", (evt) => {
        evt.preventDefault();
        const formData = new FormData(evt.target);
        spinner('show', $('#btn-edit'));
        ajaxPost(`management/tld/update`, formData, result => {
            if (result.meta.code == 200) {
                Swal.fire({
                    icon: 'success',
                    text: result.data.msg,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false,
                }).then(() => {
                    $('#editTldModal').modal('hide');
                    datatable_tld.ajax.reload();
                    spinner('hide', $('#btn-edit'));
                    resetForm();
                })
            }
        }, error => {
            spinner('hide', $('#btn-edit'));
        });
    });

    $('#btn-create-tld').on('click', () => {
        openModalTld('create');
    });
})

function filter() {
    let status = $('#filterStatus').val();
    let jenis = $('#filterJenis').val();

    datatable_tld.ajax.url(`${base_url}/management/getDataTld?status=${status}&jenis=${jenis}`).load();
}

function btnEdit(obj) {
    const id = $(obj).data('id');
    openModalTld('edit', id);
    // ajaxGet(`management/tld/${id}`, false, result => {
    //     $('#inputIdTldEdit').val(result.data.tld_hash);
    //     $('#inputNoSeriEdit').val(result.data.no_seri_tld);
    //     $('#inputJenisTldEdit').val(result.data.jenis);
    //     $('#inputMerkEdit').val(result.data.merk);
    //     $('#editTldModal').modal('show');
    // });
}

function btnDelete(obj) {
    const id = $(obj).data('id');
    ajaxDelete(`management/tld/${id}`, result => {
        if (result.meta.code == 200) {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                datatable_tld.ajax.reload();
            })
        }
    })
}

function resetForm() {
    $('#form-edit')[0].reset();
}
function reload(){
    filter();
}
