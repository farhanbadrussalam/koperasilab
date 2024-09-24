$(function () {
    loadDataPengajuan();
});

function switchLoadTab(menu){
    switch (menu) {
        case 1:
            loadDataPengajuan();
            break;

        case 2:
            
            break;

        default:
            break;
    }
}

function loadDataPengajuan(page = 1) {
    let params = {
        limit: 10,
        page: page,
        status: [2]
    };

    $('#list-placeholder-pengajuan').show();
    $('#list-container-pengajuan').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';
        for (const [i, pengajuan] of result.data.entries()) {
            let periode = JSON.parse(pengajuan.periode_pemakaian);
            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="title">Layanan ${pengajuan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${pengajuan.jenis_tld.name}</div>
                                <div>Periode : ${periode.length} Bulan</div>
                                <div>Created : ${dateFormat(pengajuan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 my-3">${pengajuan.jenis_layanan_parent.name}-${pengajuan.jenis_layanan.name}</div>
                        <div class="col-6 col-md-3 my-3 text-end text-md-start">
                            <div>${pengajuan.tipe_kontrak}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">${pengajuan.no_kontrak}</small>
                        </div>
                        <div class="col-6 col-md-2">${statusFormat('keuangan', pengajuan.status)}</div>
                        <div class="col-6 col-md-2 text-center" data-id="${pengajuan.permohonan_hash}">
                            <button class="btn btn-outline-primary" title="Buat Invoice"><i class="bi bi-file-earmark-plus"></i></button>
                        </div>
                    </div>
                </div>
            `;
        }

        if(result.data.length == 0){
            html = `
                <div class="d-flex flex-column align-items-center py-3">
                    <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                    <span class="fw-bold mt-3 text-muted">No Data Available</span>
                </div>
            `;
        }

        $('#list-container-pengajuan').html(html);

        $('#list-pagination-pengajuan').html(createPaginationHTML(result.pagination));

        $('#list-placeholder-pengajuan').hide();
        $('#list-container-pengajuan').show();
    }, error => {
        const result = error.responseJSON;
        if(result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }
    })
}