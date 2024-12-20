$(function () {
    loadData();
});

$('#list-pagination').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    
    loadPengajuan(pageno);
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page
    };

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';
        for (const [i, pengajuan] of result.data.entries()) {
            let periode = pengajuan.periode_pemakaian;
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
                        <div class="col-6 col-md-3 my-3">${pengajuan.jenis_layanan_parent.name}-${pengajuan.jenis_layanan.name}</div>
                        <div class="col-6 col-md-2 my-3 text-end text-md-start">${pengajuan.tipe_kontrak}</div>
                        <div class="col-6 col-md-2">${pengajuan.pelanggan.name}</div>
                        <div class="col-6 col-md-2 text-center" data-id="${pengajuan.permohonan_hash}">
                            <a class="btn btn-outline-primary btn-sm" title="Verifikasi" href="${base_url}/staff/permohonan/verifikasi/${pengajuan.permohonan_hash}"><i class="bi bi-check2-circle"></i> Verifikasi</a>
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

        $('#list-container').html(html);

        $('#list-pagination').html(createPaginationHTML(result.pagination));

        $('#list-placeholder').hide();
        $('#list-container').show();
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