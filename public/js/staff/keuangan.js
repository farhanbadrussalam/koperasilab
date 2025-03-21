const invoice = new Invoice();
let thisTab = 1;
let filterComp = false;

$(function () {
    switchLoadTab(1);
    invoice.on('invoice.simpan', () => {
        switchLoadTab(thisTab);
    });

    filterComp = new FilterComponent('list-filter', {
        filter : {
            jenis_tld : true,
            jenis_layanan : true,
            no_kontrak : true,
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => switchLoadTab(thisTab));
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
        menu: menu,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/keuangan/listKeuangan`, params, result => {
        let html = '';
        for (const [i, keuangan] of result.data.entries()) {
            const permohonan = keuangan.permohonan;
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
                    btnAction = `<button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openInvoiceModal(this, 'verifStaff')"><i class="bi bi-check2-circle"></i> Verif Invoice</button>`;
                    break;
                default:
                    break;
            }

            const data = {
                id: keuangan.keuangan_hash,
                tipeKontrak: permohonan.tipe_kontrak,
                jenisLayananParent: permohonan.jenis_layanan_parent.name,
                jenisLayanan: permohonan.jenis_layanan.name,
                format: 'keuangan',
                status: keuangan.status,
                jenisTld: permohonan.jenis_tld.name,
                namaLayanan: permohonan.layanan_jasa.nama_layanan,
                pelanggan: permohonan.pelanggan.name,
                periode: permohonan.periode,
                created_at: permohonan.created_at,
                kontrak: permohonan.kontrak.no_kontrak,
            }
            html += cardComponent(data, { btnAction: btnAction });
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
    })

    countList();
}

function openInvoiceModal(obj, mode) {
    const keuangan = $(obj).parent().data("id");
    ajaxGet(`api/v1/keuangan/getKeuangan/${keuangan}`, false, result => {
        invoice.addData(result.data);
        invoice.open(mode);
    })
}

function reload() {
    switchLoadTab(thisTab);
}

function clearFilter() {
    filterComp.clear();
    switchLoadTab(thisTab);
}

function countList() {
    ajaxGet(`api/v1/keuangan/countList`, false, result => {
        const count = result.data.reduce((acc, cur) => {
            acc[cur.name] = (acc[cur.name] || 0) + cur.total;
            return acc;
        }, {});
        Object.entries(count).forEach(([key, value]) => {
            const element = $(`#count${key}`);
            element.html(value === 0 ? "" : `(${value})`);
            element.toggle(value > 0);
        });
    })
}