let dataKontrak = false;
let filterComp = false;
$(function () {
    loadData();
    detail = new Detail({
        jenis: 'kontrak',
        tab: {
            pengguna: true,
            periode: true,
            dokumen: true
        }
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'kontrak',
        filter : {
            status : true,
            jenis_tld : true,
            no_kontrak : true,
            date_range: true,
            periode: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());

    $(`#list-pagination`).on('click', 'a', function(e){
        e.preventDefault();
        const pageno = e.target.dataset.page;
        loadData(pageno);
    });
});

function loadData(page = 1) {
    let params = {
        limit: 5,
        page: page,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.status && (params.filter.status = filterValue.status);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.periode && (params.filter.periode = filterValue.periode);
    (filterValue.date_range && filterValue.date_range.length == 2) && (params.filter.date_range = filterValue.date_range);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/kontrak/list`, params, result => {
        dataKontrak = result.data;
        let html = '';
        for (const [i, data] of result.data.entries()) {
            let arrPeriode = data.periode;

            let htmlLastPeriod = '';
            let periodeNow = getCurrentPeriod(arrPeriode);
            switch (periodeNow) {
                case 'notstarted':
                    htmlLastPeriod = `<span>Belum masuk periode</span>`;
                    break;
                case 'ended':
                    htmlLastPeriod = `<span>Periode Selesai</span>`;
                    break;
                default:
                    if(periodeNow?.endDate){
                        let remaining = getDaysRemaining(periodeNow.endDate);
                        htmlLastPeriod = `
                            <span>${periodeNow.name}</span>
                            <span>Sisa ${remaining} hari</span>
                        `;
                    }else{
                        htmlLastPeriod = ``;
                    }
                    break;
            }

            let detailPengiriman = [];
            let arrFind = ['invoice','tld', 'lhu'];
            const JL = jenislayanan(data.jenis_layanan_parent, data.jenis_layanan);

            for (const pengiriman of data.pengiriman) {
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

            let activePeriode = '';
            let lastPeriodeKontrak = false;
            let htmlPengembalian = '';
            for (const periode of data.periode) {
                const dokumenAktif = periodeMapDocument(periode, data, arrFind);
                const isComplete = cekPeriodeComplete(periode, detailPengiriman, data, dokumenAktif);

                let jml_periode = data.periode_count;
                if(!tmpArrSewa.includes(JL)){
                    if(data.is_zerocek && !data.is_have_tld){
                        jml_periode = jml_periode - 1;
                    }
                }

                if(!isComplete){
                    activePeriode = periode;
                    break;
                }
                lastPeriodeKontrak = (jml_periode) == periode.periode;
            }

            if(lastPeriodeKontrak && role.includes('Staff Pengiriman')){
                htmlPengembalian = pengembalianTLD(data);
            } else if(lastPeriodeKontrak) {
                htmlPengembalian = pengembalianTLD(data);
            }

            let tmpPeriod = [...arrPeriode].filter(d => d.status == 1);
            let jumlahPeriod = data.is_zerocek == 1 ? tmpPeriod.length - 1 : tmpPeriod.length;

            let hidden = role.includes('Pelanggan') ? 'd-none' : '';

            let btnAdendum = role.includes('Pelanggan') ? `
                <div class="mb-2 text-end fs-8 ${data.status == 2 ? 'd-none' : ''}">
                    <a class="btn btn-sm btn-warning rounded-pill" href="${base_url}/permohonan/kontrak/a/${data.kontrak_hash}">
                        <i class="bi bi-pencil"></i> Adendum
                    </a>
                </div>
            ` : '';

            html += `
                <div class="card mb-2 smooth-height hover-effect">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-auto">
                            <div class="">
                                <span class="badge bg-primary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.tipe_kontrak}</span>
                                <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.jenis_layanan_parent.name} - ${data.jenis_layanan.name}</span>
                            </div>
                            <div class="fs-5 my-2">
                                <span class="fw-bold">${data.jenis_tld.name} - Layanan ${data.layanan_jasa.nama_layanan}</span> <span class="text-body-tertiary">#${data.no_kontrak}</span>
                                <div class="text-body-tertiary fs-7 ${hidden}">
                                    <div><i class="bi bi-building-fill"></i> ${data.pelanggan.perusahaan.nama_perusahaan}</div>
                                </div>
                            </div>
                            <div class="d-flex gap-3 text-body-tertiary fs-7">
                                <div><i class="bi bi-calendar-fill"></i> ${dateFormat(data.created_at, 4)}</div>
                                <div><i class="bi bi-cash-stack"></i> ${formatRupiah(data.total_harga)}</div>
                            </div>
                        </div>
                        <div class="col-auto ms-auto align-self-end">
                            ${btnAdendum}
                            <div class="mb-2 text-end fs-8">
                                ${statusFormat('kontrak',data.status)}
                            </div>
                            <div class="d-flex gap-1" data-id="${data.kontrak_hash}">
                                <div class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" onclick="showPeriode(${i})"><i class="bi bi-clock-fill"></i> ${jumlahPeriod} Periode</div>
                                <div class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" onclick="showDetail(this)"><i class="bi bi-info-circle"></i> Detail</div>
                            </div>
                        </div>
                        <div class="p-3 pb-0" id="listPeriodeNow${i}">
                            ${(() => {
                                return activePeriode ? htmlPeriode(activePeriode, i, detailPengiriman, arrFind, { active: true }) : '';
                            })()}
                            ${role.includes('Staff Pengiriman') ? htmlPengembalian : ''}
                        </div>
                        <div class="p-3 pb-0" id="listPeriode${i}" style="display:none">
                            ${(() => {
                                let html = '';
                                let evaluasiState = { active: false }; // Objek referensi
                                for (const kontrak of data.periode) {
                                    html += htmlPeriode(kontrak, i, detailPengiriman, arrFind, evaluasiState);
                                }
                                return html;
                            })()}
                            ${htmlPengembalian}
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

function showPeriode(index) {

    if ($(`#listPeriode${index}`).is(':visible')) {
        $(`#listPeriodeNow${index}`).show();
        $(`#listPeriode${index}`).hide();
        return;
    }

    $(`#listPeriode${index}`).show();
    $(`#listPeriodeNow${index}`).hide();
}

function htmlPeriode(data, index, cekStatusPeriode, arrFind, evaluasiState) {
    const isPelanggan = role.includes('Pelanggan');
    let htmlAction = ``;
    let htmlDoc = ``;
    // let periodeAwal = getPeriodeAwal(dataKontrak[index]);

    // cek apakah sudah bayar atau belum
    // let lastPeriode = (dataKontrak[index].periode_count) == data.periode;
    let statusKirimTld = false;

    const JL = jenislayanan(dataKontrak[index].jenis_layanan_parent, dataKontrak[index].jenis_layanan);

    let aktifDokumenKirim = periodeMapDocument(data, dataKontrak[index], arrFind);


    for (const doc of aktifDokumenKirim) {
        let findPeriode = cekStatusPeriode.find(cek => cek.periode == data.periode && cek.jenis == doc);

        // pengecekan TLD
        if(doc === 'tld') {
            statusKirimTld = findPeriode?.status;
        }
        let htmlStatusInvoice = '';

        if(doc === 'invoice') {
            if(data.permohonan){
                htmlStatusInvoice = statusFormat('invoice', data.permohonan.invoice.status);
                if(data.permohonan.invoice.status == 3 && role.includes('Pelanggan')){
                    htmlStatusInvoice = `<a href="${base_url}/permohonan/pembayaran/bayar/${data.permohonan.invoice.keuangan_hash}">${htmlStatusInvoice}</a>`;
                }
            }
        }

        let htmlTooltip = findPeriode?.no_resi ? `<div class="tooltip-text border border-dark-subtle">No resi : ${findPeriode?.no_resi ?? 'Belum ada'}</div>` : '';

        htmlDoc += `
            <div>
                <span class="fw-normal">• ${doc[0].toUpperCase() + doc.substring(1)}</span>
                <small class="cursoron hover-1 pe-2 ${findPeriode?.no_resi ? 'tooltip-container' : ''}">
                    ${statusFormat('pengiriman', findPeriode?.status)}
                    ${htmlTooltip}
                </small>
                <div class="ms-2">
                    ${htmlStatusInvoice}
                </div>
            </div>
        `;

    }

    // Gunakan fungsi isPeriodeComplete untuk mengecek status
    let isComplete = cekPeriodeComplete(data, cekStatusPeriode, dataKontrak[index], aktifDokumenKirim);

    let htmlBtnEvaluasi = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/permohonan/kontrak/e/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text"></i> Evaluasi</a>`;
    let htmlBtnTld = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/staff/pengiriman/permohonan/kirim/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-send-fill"></i> Kirim TLD</a>`;

    if(data.permohonan){
        htmlAction = `
        <div class="d-flex flex-column justify-content-center align-items-end">
            <div class="fs-8">${data.permohonan.jenis_layanan_parent.name} - ${data.permohonan.jenis_layanan.name}</div>
            <div>${statusFormat('permohonan', data.permohonan.status)}</div>
        </div>`;
    }

    if(isPelanggan) {
        if(evaluasiState.active) {
            if(!data.permohonan){
                if(data.status == 1) { // bukan status periode pengembalian
                    htmlAction += htmlBtnEvaluasi;
                }
            }
        }
        evaluasiState.active = isComplete;
    } else {
        evaluasiState.active = !isComplete;

        if(evaluasiState.active) {
            let tldSelesai = false;
            let penyelia2 = dataKontrak[index].periode.find(cek => cek.periode == data.periode - 2);
            if(penyelia2){
                tldSelesai = cekPenyelia(penyelia2?.penyelia, 'Pelabelan TLD');

            }else {
                if(dataKontrak[index].is_zerocek == 0 && dataKontrak[index].is_have_tld == 0) {
                    tldSelesai = true;
                } else if(dataKontrak[index].is_zerocek == 1 && dataKontrak[index].is_have_tld == 1) {
                    if(JL != StringZerocek) {
                        tldSelesai = true;
                    }
                }
            }

            if(aktifDokumenKirim.includes('tld')) {
                if(tldSelesai) {
                    if(!data.permohonan){
                        if(!statusKirimTld){
                            htmlAction = htmlBtnTld;
                        }
                    }
                }
            }
        }
    }

    if(data.periode == 2){
        console.log(data);
    }
    let textPeriode = !data.periode ? 'Zero cek' : 'Periode ' + data.periode;

    if(dataKontrak[index].is_have_tld && dataKontrak[index].is_zerocek && data.periode == 1) {
        textPeriode += ' + Zero cek';
    }

    if(data.status == 2) { // Status 2 == Pengembalian
        textPeriode = 'Pengembalian TLD';
    }

    let htmlRangeDate = ``;
    if(data.periode != 0) {
        let rangeDate = range_date(data.start_date, data.end_date, 1);
        htmlRangeDate = `<small class="text-body-tertiary"> - (${rangeDate.start} - ${rangeDate.end})</small>`;
    }

    let htmlAdendum = ``;
    if(data.adendum.length > 0) {
        htmlAdendum = `<small class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2">${data.adendum.length} Adendum</small>`;
    }

    return `
        <div class="border-top py-2 d-flex justify-content-between align-items-center">
            <div class="px-2">
                <span class="fw-semibold fs-6">${textPeriode}</span>
                ${htmlRangeDate} ${htmlAdendum}
                <div class="d-flex gap-3 flex-wrap">
                    ${htmlDoc}
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 text-secondary">
                ${htmlAction}
            </div>
        </div>
    `;
}

function cekTldComplete(){

}

function cekPenyelia(penyelia, jobs_point) {
    let search = penyelia?.penyelia_map.find(d => d.jobs.name == jobs_point);
    if(search?.status == 2) {
        return true;
    }

    return false;
}

function buttonEvaluasi(data_periode, data_kontrak, active){
    let result = false;
    let btnEvaluasi = '';
    if(!data_periode.permohonan) {
        result = active;
    }

    return result;
}

function buttonTLD(data_periode, data_kontrak, active){
    let result = false;
    let btnEvaluasi = '';
    if(!data_periode.permohonan) {
        result = active;
    }

    return result;
}

function reload() {
    loadData();
}

function clearFilter(){
    filterComp.clear();
    loadData();
}

function showDetail(obj){
    const id = $(obj).parent().data("id");
    let url = `api/v1/kontrak/getById/${id}`;
    detail.show(url);
}

function pengembalianTLD(data){
    let htmlBtnTld = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/staff/pengiriman/pengembalian/${data.kontrak_hash}"><i class="bi bi-send-fill"></i> Kirim TLD</a>`;
    let htmlAction = ``;
    let jumlah = 0;
    let html = ``;
    let tldTidakDigunakan = data.rincian_list_tld;
    if(tldTidakDigunakan.length > 0){
        jumlah = tldTidakDigunakan.length;
        let countTld = tldTidakDigunakan[0].count_tld;

        let orderPeriode = [...data.periode];
        orderPeriode.sort((a, b) => b.periode - a.periode);
        let ambil = false;
        for (const item of orderPeriode) {
            if(item.count_tld === countTld) {
                ambil = item;
                break;
            }
        }

        if(ambil.status == 2) return '';

        let tldSelesai = cekPenyelia(ambil.penyelia, 'Pelabelan TLD');
        if(tldSelesai && role.includes('Staff Pengiriman')) {
            htmlAction = htmlBtnTld;
        }

        // set tanggal
        let startDate = new Date(ambil.end_date);
        // awal bulan setelah startDate
        startDate.setDate(1);
        startDate.setMonth(startDate.getMonth() + 4);

        let endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + 3);
        endDate.setDate(0);

        if(data.periode_next) {
            startDate = new Date(data.periode_next[0].start_date);
            endDate = new Date(data.periode_next[0].end_date);
        }

        html = `
            <div class="border-top py-2 d-flex justify-content-between align-items-center">
                <div class="px-2">
                    <span class="fw-semibold fs-6">Pengembalian TLD </span><small class="text-body-tertiary">(${dateFormat(startDate, 4)} - ${dateFormat(endDate, 4)})</small>
                    <div class="d-flex gap-3 flex-wrap">
                        <div>
                            <span class="fw-normal">• TLD</span>
                            <small class="cursoron hover-1 pe-2">
                                ${statusFormat('pengiriman', 0)}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 text-secondary">
                    ${htmlAction}
                </div>
            </div>
        `;
    }

    return html;
}
