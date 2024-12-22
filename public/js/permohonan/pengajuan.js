let thisTab = 1;
let thisStatus = false;
$(function () {
    switchLoadTab(1);
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
            thisStatus = [1,2];
            break;

        case 2:
            thisStatus = [80];
            break;
    }
    loadData(1, thisStatus);
}

function loadData(page = 1, status) {
    let params = {
        limit: 3,
        page: page,
        status: status
    };

    $('#pengajuan-placeholder').show();
    $('#pengajuan-list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';
        for (const [i, pengajuan] of result.data.entries()) {
            let periode = pengajuan.periode_pemakaian;

            let btnEdit = `<a class="btn btn-sm btn-outline-warning me-1" title="Edit" href="${base_url}/permohonan/pengajuan/edit/${pengajuan.permohonan_hash}"><i class="bi bi-pencil-square"></i> Edit</a>`;
            let btnRemove = `<button class="btn btn-sm btn-outline-danger me-1 mt-1" title="Delete" onclick="remove(this)"><i class="bi bi-trash"></i> Remove</button>`;

            if(thisTab == 2){
                html += `
                    <div class="card mb-2">
                        <div class="card-body row align-items-center">
                            <div class="col-12 col-md-3">
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
                    <div class="card mb-2">
                        <div class="card-body row align-items-center">
                            <div class="col-12 col-md-3">
                                <div class="">
                                    <span class="badge bg-primary-subtle fw-normal rounded-pill text-secondary-emphasis">${pengajuan.tipe_kontrak}</span>
                                    <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${pengajuan.jenis_layanan_parent.name} - ${pengajuan.jenis_layanan.name}</span>
                                </div>
                                <div class="title">Layanan ${pengajuan.layanan_jasa?.nama_layanan ?? 'Untitled'}</div>
                                <small class="subdesc text-body-secondary fw-light lh-sm">
                                    <div>${pengajuan.jenis_tld?.name ?? '-'}</div>
                                    <div>Periode : ${periode?.length ?? '0'} Bulan</div>
                                    <div>Created : ${dateFormat(pengajuan.created_at, 4)}</div>
                                </small>
                            </div>
                            <div class="col-6 col-md-3 my-3 fw-semibold">${formatRupiah(pengajuan.total_harga)}</div>
                            <div class="col-6 col-md-2 ms-auto">${statusFormat('permohonan', pengajuan.status)}</div>
                            <div class="col-6 col-md-2 text-center" data-id="${pengajuan.permohonan_hash}">
                                <button class="btn btn-sm btn-outline-secondary" title="Show detail"><i class="bi bi-info-circle"></i> Detail</button>
                                ${pengajuan.status == 1 ? btnEdit + btnRemove : ''}
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        if(result.data.length == 0){
            html = `
                <div class="d-flex flex-column align-items-center py-3">
                    <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                    <span class="fw-bold mt-3 text-muted">No Data Available</span>
                </div>
            `;
        }

        $('#pengajuan-list-container').html(html);

        $('#pagination_list').html(createPaginationHTML(result.pagination));

        $('#pengajuan-placeholder').hide();
        $('#pengajuan-list-container').show();
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