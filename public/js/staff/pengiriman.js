let nowTab = 1;
$(function () {
    switchLoadTab(1);
});

function switchLoadTab(menu){
    nowTab = menu;
    switch (menu) {
        case 1:
            menu = 'list';
            break;
    
        case 2:
            menu = 'selesai';
            break;
    }

    loadData(1, menu);
}

function loadData(page = 1, menu) {
    let params = {
        limit: 10,
        page: page,
        menu: menu
    };

    $(`#list-placeholder-${menu}`).show();
    $(`#list-container-${menu}`).hide();
    ajaxGet(`api/v1/pengiriman/list`, params, result => {
        let html = '';
        for (const [i, data] of result.data.entries()) {
            let periode = JSON.parse(data.periode);
            let jenis = data.jenis_pengiriman.split(',');
            
            let htmlJenis = '';
            for (const v of jenis) {
                htmlJenis += `<div>${v}</div>`;
            }

            let htmlButton = '';
            if(data.status == 1){
                htmlButton = `<button class="btn btn-outline-danger btn-sm" onclick="removePengiriman(this)">Delete</button>`;
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-12 col-md-3">
                            <div class="fw-bolder">${data.no_resi}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${data.no_kontrak}</div>
                                <div>Periode: </div>
                                <div class="badge text-bg-secondary">${dateFormat(periode.start_date, 4)} s/d ${dateFormat(periode.end_date, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2">
                            ${htmlJenis}
                        </div>
                        <div class="col-6 col-md-3">
                            <span>${data.permohonan.pelanggan.perusahaan.nama_perusahaan}</span>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>Alamat: </div>
                                <div>${data.alamat}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 text-center">
                            ${statusFormat('pengiriman', data.status)}
                        </div>
                        <div class="col-6 col-md-2 text-center" data-id="${data.pengiriman_hash}">
                            <button class="btn btn-outline-info btn-sm">Detail</button>
                            ${htmlButton}
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

        $(`#list-container-${menu}`).html(html);

        $(`#list-pagination-${menu}`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-${menu}`).hide();
        $(`#list-container-${menu}`).show();
    }, error => {
        const result = error.responseJSON;
        if(result.meta?.code && result.meta.code == 500){
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

function removePengiriman(obj){
    let idPengiriman = $(obj).parent().data('id');
    ajaxDelete(`api/v1/pengiriman/destroy/${idPengiriman}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            switchLoadTab(1);
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
    })
}