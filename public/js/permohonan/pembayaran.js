const invoice = new Invoice();

$(function() {
    loadData();
})
function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page
    };
    
    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/keuangan/listKeuangan`, params, result => {
        let html = '';
        for (const [i, keuangan] of result.data.entries()) {
            const permohonan = keuangan.permohonan;
            permohonan.idkeuangan = keuangan.keuangan_hash;
            let periode = permohonan.periode_pemakaian;
            let btnAction = '';
            if(keuangan.status == 3){
                btnAction = `<a class="btn btn-outline-warning btn-sm" href="${base_url}/permohonan/pembayaran/bayar/${keuangan.keuangan_hash}" title="Bayar"><i class="bi bi-cash"></i> Bayar</a>`;
            }else{
                btnAction = `<button class="btn btn-outline-info btn-sm" title="Show Invoice" onclick="openInvoiceModal(this, 'detail')"><i class="bi bi-eye-fill"></i> Detail</button>`;
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${permohonan.jenis_tld.name}</div>
                                <div>${periode.length} Periode</div>
                                <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 my-3">${permohonan.jenis_layanan_parent.name}-${permohonan.jenis_layanan.name}</div>
                        <div class="col-6 col-md-3 my-3 text-end text-md-start">
                            <div>${permohonan.tipe_kontrak}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.kontrak.no_kontrak}</small>
                        </div>
                        <div class="col-6 col-md-2">${statusFormat('keuangan', keuangan.status)}</div>
                        <div class="col-6 col-md-2 text-center" data-keuangan='${keuangan.keuangan_hash}'>
                            ${btnAction}
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

        $(`#list-container`).html(html);

        $(`#list-pagination`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder`).hide();
        $(`#list-container`).show();
    });
}

function openInvoiceModal(obj, mode) {
    const keuangan = $(obj).parent().data("keuangan");
    ajaxGet(`api/v1/keuangan/getKeuangan/${keuangan}`, false, result => {
        invoice.addData(result.data);
        invoice.open(mode);
    })
}