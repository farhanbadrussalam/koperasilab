let dataKontrak = false;
let filterComp = false;
$(function () {
    loadData();
    detail = new Detail({
        jenis: 'kontrak',
        tab: {
            pengguna: true,
            tld: true
        }
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'kontrak',
        filter : {
            status : true,
            jenis_tld : true,
            no_kontrak : true,
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

            let htmlStatusInvoice = statusFormat('invoice', data.invoice.status);
            if(data.invoice.status == 3){
                htmlStatusInvoice = `<a href="${base_url}/permohonan/pembayaran/bayar/${data.invoice.keuangan_hash}">${htmlStatusInvoice}</a>`;
            }

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

            let cekStatusPeriode = [];
            let arrFind = ['invoice','tld'];

            if([2,3,5,6].includes(Number(dataKontrak[i].jenis_layanan_2))){
                arrFind.push('lhu');
            }

            for (const pengiriman of dataKontrak[i].pengiriman) {
                let detail = pengiriman.detail.filter(detail => arrFind.includes(detail.jenis));
                if(detail.length > 0){
                    detail.map(d => cekStatusPeriode.push({
                        jenis: d.jenis,
                        periode: d.periode ? d.periode : (pengiriman.periode ? pengiriman.periode : 0),
                        status: pengiriman.status,
                        no_resi: pengiriman.no_resi ?? false
                    }));
                }
            }

            let activePeriode = '';
            for (const periode of dataKontrak[i].periode) {
                const isComplete = isPeriodeComplete(periode, i, cekStatusPeriode, arrFind);
                if(!isComplete){
                    activePeriode = periode;
                    break;
                }
            }

            let hidden = role === 'Pelanggan' ? 'd-none' : '';
            
            html += `
                <div class="card mb-2 smooth-height">
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
                                <div>${htmlStatusInvoice}</div>
                            </div>
                        </div>
                        <div class="col-auto ms-auto align-self-end">
                            <div class="mb-2 text-end fs-8">
                                ${statusFormat('kontrak',data.status)}
                            </div>
                            <div class="d-flex gap-1" data-id="${data.kontrak_hash}">
                                <div class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" onclick="showPeriode(${i})"><i class="bi bi-clock-fill"></i> ${arrPeriode.length - 1} Periode</div>
                                <div class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" onclick="showDetail(this)"><i class="bi bi-info-circle"></i> Detail</div>
                            </div>
                        </div>
                        <div class="p-3 pb-0" id="listPeriodeNow${i}">
                            ${(() => {
                                return htmlPeriode(activePeriode, i, cekStatusPeriode, arrFind, { active: true });
                            })()}
                        </div>
                        <div class="p-3 pb-0" id="listPeriode${i}" style="display:none">
                            ${(() => {
                                let html = '';
                                let evaluasiState = { active: false }; // Objek referensi
                                for (const data of dataKontrak[i].periode) {
                                    html += htmlPeriode(data, i, cekStatusPeriode, arrFind, evaluasiState);
                                }
                                return html;
                            })()}
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
    const isPelanggan = role === 'Pelanggan';
    let htmlAction = ``;
    let htmlDoc = ``;
    // Gunakan fungsi isPeriodeComplete untuk mengecek status
    let isComplete = isPeriodeComplete(data, index, cekStatusPeriode, arrFind);

    // cek apakah sudah bayar atau belum
    let lastPeriode = dataKontrak[index].periode[dataKontrak[index].periode.length - 1].periode == data.periode;
    let statusKirimTld = false;

    for (const doc of arrFind) {
        let findPeriode = cekStatusPeriode.find(cek => cek.periode == data.periode && cek.jenis == doc);
        if (doc === 'invoice' && data.permohonan_hash !== dataKontrak[index].invoice?.permohonan_hash) continue;
        if (doc === 'lhu' && data.permohonan?.file_lhu) continue;
        if (doc === 'tld' && lastPeriode) continue;

        if (doc === 'tld') {
            statusKirimTld = findPeriode?.status;
        }
        let htmlTooltip = findPeriode?.no_resi ? `<div class="tooltip-text border border-dark-subtle">No resi : ${findPeriode?.no_resi ?? 'Belum ada'}</div>` : '';

        htmlDoc += `
            <div>
                <span class="fw-normal">• ${doc[0].toUpperCase() + doc.substring(1)}</span>
                <small class="cursoron hover-1 pe-2 ${findPeriode?.no_resi ? 'tooltip-container' : ''}">
                    ${statusFormat('pengiriman', findPeriode?.status)}
                    ${htmlTooltip}
                </small>
            </div>
        `;
    }

    // update status permohonan jika isComplete true
    if(isComplete && data.permohonan.status != 5){
        const params = new FormData();
        params.append('idPermohonan', data.permohonan.permohonan_hash);
        params.append('status', 5);

        ajaxPost('api/v1/permohonan/tambahPengajuan', params, result => {}, error => {});
        data.permohonan.status = 5;
    }

    if(data.permohonan){
        const showEvaluasi = isPelanggan && data.permohonan.status == 11 && [2, 3, 5].includes(Number(dataKontrak[index].jenis_layanan_2)) && data.permohonan.kontrak_hash;
        htmlAction = showEvaluasi ?
            `<a class="btn btn-sm btn-outline-primary" href="${base_url}/permohonan/kontrak/e/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text"></i> Evaluasi</a>` :
            `<div class="d-flex flex-column justify-content-center align-items-end"><div class="fs-8">${data.permohonan.jenis_layanan_parent.name} - ${data.permohonan.jenis_layanan.name}</div><div>${statusFormat('permohonan', data.permohonan.status)}</div></div>`;

        if (!statusKirimTld && role == 'Staff Pengiriman' && data.periode != 0) {
            !lastPeriode && (htmlAction = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/staff/pengiriman/permohonan/kirim/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-send-fill"></i> Kirim TLD</a>`);
            // data.periode == 0 ? htmlAction = '' : '';
        } else {
            if (isPelanggan && [2, 3, 5].includes(Number(dataKontrak[index].jenis_layanan_2)) && !statusKirimTld && !data.permohonan.kontrak_hash) {
                evaluasiState.active && (htmlAction = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/permohonan/kontrak/e/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text"></i> Evaluasi</a>`);
            }
        }
        evaluasiState.active = isComplete;
    }else{
        if(role == 'Staff Pengiriman') {
            (evaluasiState.active && !lastPeriode) && (htmlAction = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/staff/pengiriman/permohonan/kirim/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-send-fill"></i> Kirim TLD</a>`);
        } else if(role == 'Pelanggan' && [2, 3, 5].includes(Number(dataKontrak[index].jenis_layanan_2))) {
            // 2 = Sewa, 3 = Evaluasi, 5 = Evaluasi - dengan kontrak
            evaluasiState.active && (htmlAction = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/permohonan/kontrak/e/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text"></i> Evaluasi</a>`);
        }
        evaluasiState.active = false;
    }
    
    return `
        <div class="border-top py-2 d-flex justify-content-between align-items-center">
            <div class="px-2">
                <span class="fw-semibold fs-6">${data.periode == 0 ? 'Zero cek' : `Periode ${data.periode}`}</span>
                ${data.periode == 0 ? '' : `<small class="text-body-tertiary"> - (${dateFormat(data.start_date, 4)} - ${dateFormat(data.end_date, 4)})</small>`}
                <div class="d-flex gap-3 flex-wrap">
                    ${htmlDoc}
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 text-secondary">
                ${htmlAction}
            </div>
        </div>
    `;
}

function isPeriodeComplete(data, index, cekStatusPeriode, arrFind) {
    // Jika invoice belum status 5, maka langsung false
    if (dataKontrak[index].invoice.status != 5) return false;

    // Cek apakah semua dokumen dalam arrFind sudah selesai
    for (const doc of arrFind) {
        let findPeriode = cekStatusPeriode.find(cek => cek.periode == data.periode && cek.jenis == doc);

        if (doc === 'invoice' && data.permohonan_hash !== dataKontrak[index].invoice?.permohonan_hash) continue;
        if (doc === 'lhu' && data.permohonan?.file_lhu) continue;

        // Jika ada dokumen yang statusnya bukan 2 (selesai), maka periode belum complete
        if (!findPeriode || findPeriode.status != 2) {
            return false;
        }
    }

    return true; // Semua dokumen sudah complete
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