const invoice = new Invoice();
let thisTab = 1;
let filterComp = false;
let detail = false;
let dataKeuangan = false;
const modalDoc = new ModalDocument();

$(function () {
    switchLoadTab(1);
    invoice.on('invoice.simpan', () => {
        switchLoadTab(thisTab);
    });
    invoice.on('invoice.batal', () => {
        switchLoadTab(thisTab);
    });

    detail = new Detail({
        jenis: 'kontrak',
        tab: {
            dokumen: true
        },
        activeTab: 'dokumen'
    });

    filterComp = new FilterComponent('list-filter', {
        filter: {
            jenis_tld: true,
            jenis_layanan: true,
            no_kontrak: true,
            date_range: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => switchLoadTab(thisTab));
});

function switchLoadTab(menu) {
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

        case 6:
            menu = 'faktur';
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
    // filterValue.periode && (params.filter.periode = filterValue.periode);
    (filterValue.date_range && filterValue.date_range.length == 2) && (params.filter.date_range = filterValue.date_range);

    if (Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/keuangan/listKeuangan`, params, result => {
        let html = '';
        dataKeuangan = result.data;
        for (const keuangan of result.data) {
            const permohonan = keuangan.permohonan;
            let btnAction = '';
            let btnAction2 = '';
            btnAction += `
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Show detail" onclick="showDetail(this)">
                        <i class="bi bi-info-circle me-2"></i> Detail Kontrak
                    </a>
                </li>
            `;
            switch (keuangan.status) {
                case 1:
                    btnAction2 = `<button class="btn btn-outline-primary btn-sm text-nowrap" title="Buat Invoice" onclick="openInvoiceModal(this, 'create')"><i class="bi bi-plus"></i> Buat invoice</button>`;
                    break;
                case 7:
                    btnAction2 = `<button class="btn btn-outline-primary btn-sm text-nowrap" title="Upload Faktur" onclick="openInvoiceModal(this, 'detail')"><i class="bi bi-upload"></i> Upload Faktur</button>`;
                    break;
                case 4:
                    btnAction2 = `<button class="btn btn-outline-primary btn-sm text-nowrap" title="Verifikasi" onclick="openInvoiceModal(this, 'verifStaff')"><i class="bi bi-check2-circle"></i> Verif Invoice</button>`;
                    break;
                default:
                    btnAction += `
                        <li>
                            <a class="dropdown-item small cursor-pointer" title="Detail Invoice" onclick="openInvoiceModal(this, 'detail')">
                                <i class="bi bi-info-circle me-2"></i> Detail invoice
                            </a>
                        </li>`;
                    break;
            }

            if (keuangan.status == 5) {
                btnAction += `
                    <li>
                        <a class="dropdown-item small cursor-pointer" data-url="laporan/kwitansi/${keuangan.keuangan_hash}" data-title="Kwitansi" onclick="btnShowDoc(this)" title="Cetak Kwitansi">
                            <i class="bi bi-printer-fill me-2"></i> Kwitansi
                        </a>
                    </li>`;
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
                kontrak: permohonan.kontrak?.no_kontrak,
                is_have_tld: permohonan.kontrak?.is_have_tld,
                is_zerocek: permohonan.kontrak?.is_zerocek,
                perusahaan: permohonan.pelanggan?.perusahaan?.nama_perusahaan
            }
            html += cardComponent(data, { btnAction: btnAction2, btnMenuAction: btnAction });
        }

        if (result.data.length == 0) {
            html = htmlNoData();
        }

        $(`#list-container`).html(html);

        $(`#list-pagination`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder`).hide();
        $(`#list-container`).show();
    })

    countList();
}

function openInvoiceModal(obj, mode) {
    const keuangan = $(obj).parent().parent().data("id");
    ajaxGet(`api/v1/keuangan/getKeuangan/${keuangan}`, false, result => {
        invoice.addData(result.data);
        invoice.open(mode);
    })
}

function showDetail(obj) {
    const keuangan = $(obj).closest('.dropdown-menu').data("id");
    let find = dataKeuangan.find(d => d.keuangan_hash == keuangan);
    if (find) {
        detail.show(`api/v1/kontrak/getById/${find.permohonan.kontrak.kontrak_hash}`);

    }
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

function btnShowDoc(obj) {
    const url = $(obj).data('url');
    const title = $(obj).data('title') || 'Dokumen';
    modalDoc.show(url, {
        title: title
    });
}