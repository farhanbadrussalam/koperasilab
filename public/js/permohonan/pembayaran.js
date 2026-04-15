const invoice = new Invoice();
let filterComp = false;

$(function() {
    loadData();

    filterComp = new FilterComponent('list-filter', {
        jenis: 'pembayaran',
        filter : {
            status : true,
            jenis_tld : true,
            jenis_layanan : true,
            no_kontrak : true,
            date_range: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());
})
function loadData(page = 1) {
    let params = {
        limit: 5,
        page: page,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.status && (params.filter.status = filterValue.status);
    // filterValue.periode && (params.filter.periode = filterValue.periode);
    (filterValue.date_range && filterValue.date_range.length == 2) && (params.filter.date_range = filterValue.date_range);

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
            let btnAction2 = '';
            if(keuangan.status == 3){
                btnAction2 = `<a class="btn btn-outline-warning btn-sm" href="${base_url}/permohonan/pembayaran/bayar/${keuangan.keuangan_hash}" title="Bayar"><i class="bi bi-cash"></i> Bayar</a>`;
            }else{
                btnAction = `
                    <li>
                        <a class="dropdown-item small cursor-pointer" title="Show Invoice" onclick="openInvoiceModal(this, 'detail')">
                            <i class="bi bi-eye-fill me-2"></i> Detail
                        </a>
                    </li>
                `;
                if(keuangan.status == 5){
                    btnAction += `
                    <li>
                        <a class="dropdown-item small cursor-pointer ms-1 text-primary" target="_blank" href="${base_url}/laporan/kwitansi/${keuangan.keuangan_hash}" title="Cetak Kwitansi">
                            <i class="bi bi-printer-fill me-2"></i> Kwitansi
                        </a>
                    </li>
                    `;
                }
            }

            const data = {
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
                id: keuangan.keuangan_hash,
                kontrak: permohonan.kontrak.no_kontrak,
                is_have_tld: permohonan.kontrak.is_have_tld,
                is_zerocek: permohonan.kontrak.is_zerocek
            }

            html += cardComponent(data, {btnAction: btnAction2, btnMenuAction: btnAction});
        }

        if(result.data.length == 0){
            html = htmlNoData();
        }

        $(`#list-container`).html(html);

        $(`#list-pagination`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder`).hide();
        $(`#list-container`).show();
    });
}

function openInvoiceModal(obj, mode) {
    const keuangan = $(obj).parent().parent().data("id");
    ajaxGet(`api/v1/keuangan/getKeuangan/${keuangan}`, false, result => {
        invoice.addData(result.data);
        invoice.open(mode);
    })
}

function clearFilter(){
    filterComp.clear();
    loadData();
}

function reload() {
    loadData();
}
