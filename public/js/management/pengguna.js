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

    $(document).on('click', '.btn-detail-keterikatan', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const btn = $(this);
        const contractsData = btn.data('contracts');

        const tbody = $('#body-detail-keterikatan-pengguna');
        tbody.empty();

        if (contractsData && Array.isArray(contractsData) && contractsData.length > 0) {
            contractsData.forEach(c => {
                const tr = `
                    <tr>
                        <td class="fw-bold text-primary">${c.no_kontrak || '-'}</td>
                        <td><span class="badge bg-light text-dark border">${c.layanan || '-'}</span></td>
                        <td><span class="badge bg-secondary">KODE: ${c.kode_lencana || '-'}</span> <span class="badge bg-info-subtle text-info-emphasis">${c.divisi || '-'}</span></td>
                    </tr>
                `;
                tbody.append(tr);
            });
        } else {
            tbody.html('<tr><td colspan="5" class="text-center text-muted">Tidak ada kontrak aktif yang terikat.</td></tr>');
        }

        $('#modal-detail-keterikatan-pengguna').modal('show');
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
