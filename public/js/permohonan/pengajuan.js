let thisTab = 1;
let thisStatus = false;
let detail = false;
$(function () {
    switchLoadTab(1);

    detail = new Detail({
        jenis: 'permohonan',
        tab: {
            pengguna: true,
            activitas: false,
            dokumen: false,
            log: false
        }
    });
})

$('#pagination_list').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    
    loadData(pageno, thisStatus);
});

function switchLoadTab(menu){
    thisTab = menu;
    switch (menu) {
        case 1:
            thisStatus = [1,2,3,4,5];
            break;

        case 2:
            thisStatus = [80];
            break;
    }
    loadData(1, thisStatus);
}

function loadData(page = 1, status) {
    let params = {
        limit: 10,
        page: page,
        status: status
    };

    $('#pengajuan-placeholder').show();
    $('#pengajuan-list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';
        for (const [i, pengajuan] of result.data.entries()) {
            let btnEdit = `<a class="btn btn-sm btn-outline-warning" title="Edit" href="${base_url}/permohonan/pengajuan/edit/${pengajuan.permohonan_hash}"><i class="bi bi-pencil-square"></i> Edit</a>`;
            let btnRemove = `<button class="btn btn-sm btn-outline-danger" title="Delete" onclick="remove(this)"><i class="bi bi-trash"></i> Remove</button>`;

            let badgeClass = 'bg-primary-subtle';
            if(pengajuan.tipe_kontrak == 'kontrak lama') {
                badgeClass = 'bg-success-subtle';
            }

            if(thisTab == 2){
                html += `
                    <div class="card mb-2">
                        <div class="card-body row align-items-center">
                            <div class="col-12 col-md-4">
                                <div class="title">Layanan ${pengajuan.layanan_jasa?.nama_layanan ?? 'Untitled'}</div>
                                <small class="subdesc text-body-secondary fw-light lh-sm">
                                    <div>created : ${dateFormat(pengajuan.created_at, 4)}</div>
                                </small>
                            </div>
                            <div class="col-6 col-md-2 ms-auto">${statusFormat('permohonan', pengajuan.status)}</div>
                            <div class="col-6 col-md-2 text-center" data-id="${pengajuan.permohonan_hash}">
                                ${btnEdit}
                                ${btnRemove}
                            </div>
                        </div>
                    </div>`;
            } else {
                html += `
                    <div class="card mb-2 smooth-height">
                        <div class="card-body row align-items-center py-2">
                            <div class="col-auto">
                                <div class="">
                                    <span class="badge ${badgeClass} fw-normal rounded-pill text-secondary-emphasis">${pengajuan.tipe_kontrak}</span>
                                    <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${pengajuan.jenis_layanan_parent.name} - ${pengajuan.jenis_layanan.name}</span>
                                </div>
                                <div class="fs-5 my-2">
                                    <span class="fw-bold">${pengajuan.jenis_tld?.name ?? '-'} - Layanan ${pengajuan.layanan_jasa?.nama_layanan}</span>
                                </div>
                                <div class="d-flex gap-3 text-body-tertiary fs-7">
                                    <span><i class="bi bi-calendar-range"></i> Periode ${pengajuan.periode}</span>
                                    <div><i class="bi bi-calendar-fill"></i> ${dateFormat(pengajuan.created_at, 4)}</div>
                                    <div><i class="bi bi-cash-stack"></i> ${formatRupiah(pengajuan.total_harga)}</div>
                                </div>
                            </div>
                            <div class="col-auto mx-auto">
                                <div>${statusFormat('permohonan', pengajuan.status)}</div>
                            </div>
                            <div class="col-md-2 ms-auto">
                                <div class="d-flex gap-1 flex-wrap justify-content-center" data-id="${pengajuan.permohonan_hash}">
                                    <button class="btn btn-sm btn-outline-secondary" title="Show detail" onclick="showDetail(this)"><i class="bi bi-info-circle"></i> Detail</button>
                                    ${pengajuan.status == 1 ? btnEdit + btnRemove : ''}
                                </div>
                            </div>
                            <div class="p-3" id="listPeriode" style="display:none"></div>
                        </div>
                    </div>
                `;
            }
        }

        if(result.data.length == 0){
            html = htmlNoData();
        }

        $('#pengajuan-list-container').html(html);

        $('#pagination_list').html(createPaginationHTML(result.pagination));

        $('#pengajuan-placeholder').hide();
        $('#pengajuan-list-container').show();
    });
}

function remove(obj){
    const idLayanan = $(obj).parent().data("id");
    ajaxDelete(`api/v1/permohonan/destroyPermohonan/${idLayanan}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            switchLoadTab(thisTab);
        });
    }, error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }else{
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.message);
        }
    });
}

function showDetail(obj){
    const idPermohonan = $(obj).parent().data("id");
    detail.show(`api/v1/permohonan/getPengajuanById/${idPermohonan}`);
}

function reload(){
    switchLoadTab(thisTab);
}