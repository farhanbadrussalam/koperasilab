const invoice = new Invoice();
let filterComp = false;
$(function () {
    loadData();
    invoice.on('invoice.simpan', () => {
        loadData();
    });
    invoice.on('invoice.tolak', () => {
        loadData();
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'manager-invoice',
        filter: {
            status: true,
            jenis_tld: true,
            jenis_layanan: true,
            no_kontrak: true,
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());
});


function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.status && (params.filter.status = filterValue.status);

    if (Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/manager/listManager`, params, result => {
        let html = '';
        for (const [i, keuangan] of result.data.entries()) {
            const permohonan = keuangan.permohonan;
            permohonan.idkeuangan = keuangan.keuangan_hash;
            let btnAction = '';
            let btnAction2 = '';

            if (keuangan.status == 2) {
                btnAction2 = `<button class="btn btn-outline-primary btn-sm text-nowrap" title="Verifikasi" onclick="verifikasiInvoice(this, 'verify')">verifikasi</button>`;
            } else {
                btnAction = `
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Detail Invoice" onclick="verifikasiInvoice(this, 'detail')">
                        <i class="bi bi-info-circle me-2"></i> Detail invoice
                    </a>
                </li>`;
            }

            if (keuangan.status == 5) {
                btnAction += `
                <li>
                    <a class="dropdown-item small cursor-pointer" target="_blank" href="${base_url}/laporan/kwitansi/${keuangan.keuangan_hash}" title="Cetak Kwitansi">
                        <i class="bi bi-printer-fill me-2"></i> Kwitansi
                    </a>
                </li>`;
            }

            let badgeClass = 'bg-primary-subtle';
            if (permohonan.tipe_kontrak == 'kontrak lama') {
                badgeClass = 'bg-success-subtle';
            }

            const data = {
                tipeKontrak: permohonan.tipe_kontrak,
                jenisLayananParent: permohonan.jenis_layanan_parent.name,
                jenisLayanan: permohonan.jenis_layanan.name,
                format: 'keuangan',
                status: keuangan.status,
                jenisTld: permohonan.jenis_tld?.name ?? '-',
                namaLayanan: permohonan.layanan_jasa?.nama_layanan,
                perusahaan: permohonan.pelanggan.perusahaan.nama_perusahaan,
                pelanggan: permohonan.pelanggan.name,
                periode: permohonan.periode,
                created_at: permohonan.created_at,
                kontrak: permohonan.kontrak?.no_kontrak,
                id: keuangan.keuangan_hash
            }

            html += cardComponent(data, { btnMenuAction: btnAction, btnAction: btnAction2 });
        }

        if (result.data.length == 0) {
            html = htmlNoData();
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

function verifikasiInvoice(obj, mode) {
    const keuangan = $(obj).parent().parent().data("id");
    ajaxGet(`api/v1/keuangan/getKeuangan/${keuangan}`, false, result => {
        invoice.addData(result.data);
        invoice.open(mode);
    })
}

function reload() {
    loadData();
}

function clearFilter() {
    filterComp.clear();
    loadData();
}
