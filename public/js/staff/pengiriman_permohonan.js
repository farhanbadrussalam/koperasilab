let dataPermohonan = false;
let filterComp = false;
$(function () {
    loadData();

    filterComp = new FilterComponent('list-filter', {
        filter : {
            // search: true,
            jenis_tld : true,
            jenis_layanan : true,
            perusahaan: true,
            no_kontrak : true,
            // periode: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());
});

function loadData(page = 1, menu) {
    let filterValue = filterComp && filterComp.getAllValue();
    let params = {
        limit: 3,
        page: page,
        menu: menu,
        filter: {}
    };

    // filterValue.search && (params.filter.search = filterValue.search);
    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.perusahaan && (params.filter.id_perusahaan = filterValue.perusahaan);
    // filterValue.periode && (params.filter.periode = filterValue.periode);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder-list`).show();
    $(`#list-container-list`).hide();
    ajaxGet(`api/v1/pengiriman/listPermohonan`, params, result => {
        // Mengambil periode
        dataPermohonan = result.data;
        let html = '';
        for (const [i, data] of result.data.entries()) {
            let arrPeriode = data.kontrak?.periode ?? data.periode_pemakaian;
            let urlLaporanInvoice = data.invoice?.status == 5 ? `<a href="${base_url}/laporan/invoice/${data.invoice.keuangan_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Invoice</a>` : '<i class="bi bi-printer-fill"></i> Cetak Invoice';
            let urlDocLhu = data.lhu?.status == 3 ? `<a href="${base_url}/storage/${data.lhu.media.file_path}/${data.lhu.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak LHU</a>` : '<i class="bi bi-printer-fill"></i> Cetak LHU';
            let arrDocCustom = [];
            const JL = jenislayanan(data.kontrak.jenis_layanan_parent, data.kontrak.jenis_layanan);

            // Data Invoice
            let htmlInvoice = '';
            data.invoice ? htmlInvoice = `
                <div class="col-md-12 mt-2">
                    <div class="border-top py-2 d-flex justify-content-between align-items-center">
                        <div class="px-2">
                            <span class="fw-semibold fs-6">Invoice</span>
                            <small class="text-body-tertiary"> - ${data.invoice.no_invoice}</small>
                            <small>${statusFormat('pengiriman', data.invoice.pengiriman?.status)}</small>
                        </div>
                        <div class="d-flex align-items-center gap-3 text-secondary">
                            <small><i class="bi bi-calendar-fill"></i> ${dateFormat(data.invoice.created_at, 4)}</small>
                            <small>${statusFormat('invoice', data.invoice.status)}</small>
                            <small class="bg-body-tertiary rounded-pill ${data.invoice.status == 5 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlLaporanInvoice}</small>
                        </div>
                    </div>
                </div>
            ` : false;

            let aktifJobsLhu = data.lhu?.penyelia_map.filter(d => d.status == 1);

            // Data layanan jasa (TLD)
            let htmlTld = '';
            let periodeTld = data.periode === 0 ? 1 : data.periode;
            let cekStatusTldPengiriman = data.kontrak.pengiriman.find(d => d.detail.find(c => c.jenis == 'tld' && c.periode == periodeTld));
            let htmlStatus = '';
            const periodeAwal = getPeriodeAwal(data.kontrak);

            if( periodeTld !== null && (!periodeAwal.includes(periodeTld))) {
                htmlTld = `
                    <div class="col-md-12 mt-2">
                        <div class="border-top py-2 d-flex justify-content-between align-items-center">
                            <div class="px-2">
                                <span class="fw-semibold fs-6">${data.layanan_jasa.nama_layanan} Periode ${periodeTld}</span>
                                <small class="text-body-tertiary"> - ${data.jumlah_pengguna} Pengguna + ${data.jumlah_kontrol} Kontrol</small>
                                <small>${statusFormat('pengiriman', cekStatusTldPengiriman ? cekStatusTldPengiriman.status : false)}</small>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-secondary">
                                <small>${htmlStatus}</small>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Data LHU
            let htmlLhu = '';
            let htmlStatusLhu = statusFormat('penyelia', data.lhu?.status);
            // let htmlStatusLhu = data.lhu ? statusFormat('penyelia', data.lhu.status) : '';
            if(aktifJobsLhu && data.lhu.status == 10) {
                aktifJobsLhu.map(d => {
                    htmlStatusLhu += statusFormat('penyelia', d.jobs.status);
                });
            }

            let htmlPeriode = false;
            if(data.lhu) {
                htmlPeriode = data.lhu?.periode == 0 ? "Zero cek" : `Periode ${data.lhu.periode}`;
                if(data.lhu.periode == 1 && data.is_zerocek == 1 && data.is_have_tld == 1) {
                    htmlPeriode += " + Zero cek";
                }
            }

            data.lhu ? htmlLhu = `
                <div class="col-md-12 mt-2">
                    <div class="border-top py-2 d-flex justify-content-between align-items-center">
                        <div class="px-2">
                            <span class="fw-semibold fs-6">LHU</span>
                            <small class="text-body-tertiary"> - ${htmlPeriode}</small>
                            <small>${statusFormat('pengiriman', data.lhu.pengiriman?.status)}</small>
                        </div>
                        <div class="d-flex align-items-center gap-3 text-secondary">
                            <small><i class="bi bi-calendar-fill"></i> ${dateFormat(data.lhu.created_at, 4)}</small>
                            <small>${htmlStatusLhu}</small>
                            <!-- <small class="bg-body-tertiary rounded-pill ${data.lhu.status == 3 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlDocLhu}</small> -->
                        </div>
                    </div>
                </div>
            ` : false;

            // Data custom
            let htmlCustom = '';
            if(data.file_lhu){
                arrDocCustom.push({jenis: "lhu zero cek", media: data.file_lhu});
            }
            for (const custom of arrDocCustom) {
                let urlDocCustom = custom.media ? `<a href="${base_url}/storage/${custom.media.file_path}/${custom.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Document</a>` : false;
                htmlCustom += `
                    <div class="col-md-12 mt-2">
                        <div class="border-top py-2 d-flex justify-content-between align-items-center">
                            <div class="px-2">
                                <span class="fw-semibold fs-6">${custom.jenis}</span>
                                <small class="text-body-tertiary"></small>
                                <small>${statusFormat('pengiriman', data.pengiriman?.status)}</small>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-secondary">
                                ${urlDocCustom ? '<small class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2">'+urlDocCustom+'</small>' : ''}
                            </div>
                        </div>
                    </div>
                `;
            }

            let htmlBtn = '';
            let arrFind = ['invoice','tld', 'lhu'];
            let detailPengiriman = [];
            if(data.kontrak.pengiriman){
                for (const pengiriman of data.kontrak.pengiriman) {
                    let detail = pengiriman.detail.filter(detail => arrFind.includes(detail.jenis));
                    if(detail.length > 0){
                        detail.map(d => detailPengiriman.push({
                            jenis: d.jenis,
                            periode: d.periode ? d.periode : (pengiriman.periode ? pengiriman.periode : 0),
                            status: pengiriman.status,
                            no_resi: pengiriman.no_resi ?? false
                        }));
                    }
                }
            }
            const dokumenAktif = periodeMapDocument(data, data.kontrak, arrFind);
            const isComplete = cekComplete(data, detailPengiriman, dokumenAktif);
            const kontrak_periode = data.kontrak.periode.find(d => d.periode == data.periode);

            if(!isComplete){
                htmlBtn += `<a class="btn btn-outline-primary" href="${base_url}/staff/pengiriman/permohonan/kirim/${data.kontrak.kontrak_hash}/${kontrak_periode.periode_hash}"><i class="bi bi-send-fill"></i> Kirim document</a>`;
            }
            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-9">
                            <div class="">
                                <span class="badge bg-primary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.tipe_kontrak}</span>
                                <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.jenis_layanan_parent.name} - ${data.jenis_layanan.name}</span>
                            </div>
                            <div class="fs-5 my-2"><span class="fw-bold">${data.jenis_tld.name} - ${data.pelanggan.perusahaan.nama_perusahaan}</span> <span class="text-body-tertiary">${data.kontrak ? "#"+data.kontrak.no_kontrak : ''}</span></div>
                            <div class="d-flex gap-3 text-body-tertiary">
                                <div class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" onclick="showPeriode(${i})">${data.is_zerocek == 1 ? arrPeriode.length - 1 : arrPeriode.length} Periode</div>
                                <div><i class="bi bi-person-check-fill"></i> ${data.pelanggan.name}</div>
                                <div><i class="bi bi-calendar-fill"></i> ${dateFormat(data.created_at, 4)}</div>
                            </div>
                        </div>
                        <div class="col-auto ms-auto">
                            ${htmlBtn}
                        </div>
                        ${htmlTld}
                        ${htmlInvoice}
                        ${htmlLhu}
                        ${htmlCustom}
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

        $(`#list-container-list`).html(html);

        $(`#list-pagination-list`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-list`).hide();
        $(`#list-container-list`).show();
    });
}

$('#list-pagination-list').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;

    loadData(pageno);
});

function showPeriode(index) {
    const arrPeriode = dataPermohonan[index].kontrak?.periode ?? dataPermohonan[index].periode_pemakaian;
    const periodeJs = new Periode(arrPeriode, {
        preview: true,
        max: arrPeriode.length
    });

    periodeJs.show();
    periodeJs.on('periode.hide.modal', () => {
        periodeJs.destroy();
    });
}

function cekLastPeriode(periode_kontrak, periode_now){
    // Ambil periode terakhir
    const lastPeriode = periode_kontrak[periode_kontrak.length-1];
    const isLast = periode_now == lastPeriode?.periode ? true : false;
    return isLast;
}

function reload(){
    loadData();
}

function clearFilter(){
    filterComp.clear();

    loadData();
}

function cekComplete(data_periode, detail_pengiriman, arrFindDokumen) {
    return arrFindDokumen.every(doc => detail_pengiriman.some(cek => cek.periode === data_periode.periode && cek.jenis === doc));
    for (const doc of arrFindDokumen) {
        let findPeriode = detail_pengiriman.find(cek => cek.periode == data_periode.periode && cek.jenis == doc);

        if(!findPeriode) {
            return false;
        }
    }

    return true;
}

