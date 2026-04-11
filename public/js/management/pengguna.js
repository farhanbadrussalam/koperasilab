let datatable_ = false;
let filterComp = false;
let penggunaForm = false;
$(function () {
    filterComp = new FilterComponent('list-filter', {
        jenis: 'pengguna',
        filter: {
            status: true
        }
    })
    penggunaForm = new PenggunaForm();

    datatable_ = $('#pengguna-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/management/getDataPengguna`,
            data: function(d) {
                let filterValue = filterComp && filterComp.getAllValue();
                d.filter = {};

                filterValue.status && (d.filter.status = filterValue.status);
            }
        },
        columns: [
            { data: 'html', orderable: false },
        ]
    });

    // Setup Filter
    filterComp.on('filter.change', () => {
        datatable_.ajax.reload();
    });

    datatable_.on('draw.dt', function () {
        showPopupReload();
        $('.btn-edit-pengguna').on('click', function () {
            const id = $(this).data('id');
            penggunaForm.showEdit(id);
        })
    });
})

function tambahPengguna(){
    penggunaForm.showAdd();
}


function btnDelete(obj) {
    const id = $(obj).data('id');
    ajaxDelete(`api/v1/pengguna/destroy/${id}`, result => {
        if (result.meta.code == 200) {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                datatable_.ajax.reload();
            })
        }
    })
}

function reload() {
    datatable_.ajax.reload();
}
