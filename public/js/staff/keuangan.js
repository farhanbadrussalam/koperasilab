const invoice = new Invoice();
let thisTab = 1;

$(function () {
    switchLoadTab(1);
    invoice.on('invoice.simpan', () => {
        switchLoadTab(thisTab);
    });
});

function switchLoadTab(menu){
    thisTab = menu;
    switch (menu) {
        case 1:
            menu = 'pengajuan';
            break;

        case 2:
            menu = 'pembayaran';
            break;

        case 3:
            menu = 'verifikasi';
            break;

        case 4:
            menu = 'diterima';
            break;

        default:
            menu = 'ditolak';
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
    ajaxGet(`api/v1/keuangan/listKeuangan`, params, result => {
        let html = '';
        for (const [i, keuangan] of result.data.entries()) {
            const permohonan = keuangan.permohonan;
            permohonan.idkeuangan = keuangan.keuangan_hash;
            let periode = JSON.parse(permohonan.periode_pemakaian);
            let btnAction = '';
            switch (menu) {
                case 'pengajuan':
                    btnAction = `<button class="btn btn-outline-primary btn-sm" title="Buat Invoice" onclick="openInvoiceModal(this, 'create')"><i class="bi bi-plus"></i> Buat invoice</button>`;
                    break;
                case 'pembayaran':
                case 'diterima':
                    btnAction = `<button class="btn btn-outline-info btn-sm" title="Detail Invoice" onclick="openInvoiceModal(this, 'detail')"><i class="bi bi-info-circle"></i> Detail invoice</button>`;
                    break;
                case 'verifikasi':
                    btnAction = `<button class="btn btn-outline-primary" title="Verifikasi" onclick="openInvoiceModal(this, 'verifStaff')"><i class="bi bi-check2-circle"></i> Verif Invoice</button>`;
                    break;
                default:
                    break;
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

        $(`#list-container-${menu}`).html(html);

        $(`#list-pagination-${menu}`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-${menu}`).hide();
        $(`#list-container-${menu}`).show();
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

function openInvoiceModal(obj, mode) {
    const keuangan = $(obj).parent().data("keuangan");
    ajaxGet(`api/v1/keuangan/getKeuangan/${keuangan}`, false, result => {
        invoice.addData(result.data);
        invoice.open(mode);
    }, error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result?.meta?.code == 500){
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
            console.error(error);
        }
    })
}