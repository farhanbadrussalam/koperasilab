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
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'pengguna_info', name: 'name' },
            { data: 'divisi_info', name: 'divisi.name' },
            { data: 'radiasi_info', name: 'radiasi_info', orderable: false, searchable: false },
            { data: 'status', name: 'status', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    // Setup Filter
    filterComp.on('filter.change', () => {
        datatable_.ajax.reload();
    });

    datatable_.on('draw.dt', function () {
        showPopupReload();
    });
})

function tambahPengguna(){
    penggunaForm.showAdd();
}

function editPengguna(obj){
    const id = $(obj).data('id');
    if (penggunaForm) {
        penggunaForm.showEdit(id);
    }
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
