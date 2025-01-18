const invoice = new Invoice();

$(function () {
    loadData();
    invoice.on('invoice.simpan', () => {
        loadData();
    });
    invoice.on('invoice.tolak', () => {
        loadData();
    });
});


function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        status: [90]
    };

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/manager/listManager`, params, result => {
        let html = '';
        for (const [i, keuangan] of result.data.entries()) {
            const permohonan = keuangan.permohonan;
            permohonan.idkeuangan = keuangan.keuangan_hash;
            let periode = permohonan.periode_pemakaian;
            let btnAction = '';

            if(keuangan.status == 2){
                btnAction = `<button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="verifikasiInvoice(this)">verifikasi</button>`;
            }else{
                btnAction = statusFormat('keuangan', keuangan.status);
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${permohonan.jenis_tld.name}</div>
                                <div>Periode : ${periode.length} Bulan</div>
                                <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 my-3">${permohonan.jenis_layanan_parent.name}-${permohonan.jenis_layanan.name}</div>
                        <div class="col-6 col-md-3 my-3 text-end text-md-start">
                            <div>${permohonan.tipe_kontrak}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.kontrak?.no_kontrak ?? ''}</small>
                        </div>
                        <div class="col-6 col-md-2">${permohonan.pelanggan.name}</div>
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

        $('#list-container').html(html);

        $('#list-pagination').html(createPaginationHTML(result.pagination));

        $('#list-placeholder').hide();
        $('#list-container').show();
    })
}

$('#list-pagination').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    
    loadData(pageno);
});

function verifikasiInvoice(obj){
    const keuangan = $(obj).parent().data("keuangan");
    ajaxGet(`api/v1/keuangan/getKeuangan/${keuangan}`, false, result => {
        invoice.addData(result.data);
        invoice.open('verify');
    })
}

function reload() {
    loadData();
}