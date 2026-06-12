let datatable_tld = false;
let filterComp = false;
let detail = false;
$(function () {
    detail = new Detail({
        jenis: 'tld',
        tab: {
            log: true
        }
    });

    datatable_tld = $('#tld-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/management/getDataTld`,
            type: "GET",
            data: function(d) {
                let filterValue = filterComp && filterComp.getAllValue();

                d.filter = {};
                filterValue.search && (d.filter.search = filterValue.search);
                filterValue.status && (d.filter.status = filterValue.status);
                filterValue.selected_custom && (d.filter.jenis = filterValue.selected_custom);
                filterValue.no_kontrak && (d.filter.no_kontrak = filterValue.no_kontrak);

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
            { data: 'no_seri_tld', name: 'no_seri_tld' },
            { data: 'status', name: 'status', className: 'text-center' },
            { data: 'jenis', name: 'jenis' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ]
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'tld',
        filter : {
            search: true,
            status: true,
            selected_custom: true,
            no_kontrak: true
        },
        placeholder: {
            search: 'Cari TLD...',
            status: 'Semua Status',
            selected_custom: 'Semua Jenis TLD'
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => datatable_tld?.ajax.reload());

    $('#editTldModal').on('hide.bs.modal', resetForm);

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
function btnEdit(obj) {
    const id = $(obj).data('id');
    openModalTld('edit', id);
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
    datatable_tld.ajax.reload();
}

function clearFilter(){
    filterComp.clear();
    reload();
}

function btnDetail(obj) {
    const id = $(obj).data('id');
    detail.show(`management/tld/${id}`);
}
