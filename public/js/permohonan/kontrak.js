let dataKontrak = false;
$(function () {
    loadData();

    $(`#list-pagination`).on('click', 'a', function(e){
        e.preventDefault();
        const pageno = e.target.dataset.page;
        loadData(pageno);
    });
});

function loadData(page = 1) {
    let params = {
        limit: 5,
        page: page
    };

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
                    let remaining = getDaysRemaining(periodeNow.endDate);
                    htmlLastPeriod = `
                        <span>${periodeNow.name}</span>
                        <span>Sisa ${remaining} hari</span>
                    `;
                    break;
            }
            
            html += `
                <div class="card mb-2 smooth-height">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-auto">
                            <div class="">
                                <span class="badge bg-primary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.tipe_kontrak}</span>
                                <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.jenis_layanan_parent.name} - ${data.jenis_layanan.name}</span>
                            </div>
                            <div class="fs-5 my-2">
                                <span class="fw-bold">${data.jenis_tld.name} - ${data.pelanggan.perusahaan.nama_perusahaan}</span> <span class="text-body-tertiary">#${data.no_kontrak}</span>
                            </div>
                            <div class="d-flex gap-3 text-body-tertiary">
                                <div><i class="bi bi-person-check-fill"></i> ${data.pelanggan.name}</div>
                                <div><i class="bi bi-calendar-fill"></i> ${dateFormat(data.created_at, 4)}</div>
                                <div><i class="bi bi-cash-stack"></i> ${formatRupiah(data.total_harga)}</div>
                                <div>${htmlStatusInvoice}</div>
                            </div>
                        </div>
                        <div class="col-auto ms-auto align-self-end">
                            <div class="mb-2 text-end fs-8">
                                ${htmlLastPeriod}
                            </div>
                            <div class=" d-flex gap-1">
                                <div class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" onclick="showPeriode(${i})"><i class="bi bi-clock-fill"></i> ${arrPeriode.length} Periode</div>
                                <div class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" onclick="showPengguna(${i})"><i class="bi bi-people-fill"></i> ${data.pengguna?.length ?? 0} Pengguna</div>
                            </div>
                        </div>
                        <div class="p-3" id="listPeriode${i}" style="display:none"></div>
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
    const arrPeriode = dataKontrak[index].periode;
    if ($(`#listPeriode${index}`).is(':visible')) {
        $(`#listPeriode${index}`).hide();
        return;
    }

    let cekStatusPeriode = [];
    let arrFind = ['invoice','tld'];

    if([3,5,6].includes(Number(dataKontrak[index].jenis_layanan_2))){
        arrFind.push('lhu');
    }

    for (const pengiriman of dataKontrak[index].pengiriman) {
        let detail = pengiriman.detail.filter(detail => arrFind.includes(detail.jenis));
        if(detail.length > 0){
            detail.map(d => cekStatusPeriode.push({
                jenis: d.jenis,
                periode: d.periode ? d.periode : (pengiriman.periode ? pengiriman.periode : 1),
                status: pengiriman.status,
                no_resi: pengiriman.no_resi ?? false
            }));
        }
    }

    let html = '';
    let evaluasiActive = false;
    for (const [i, data] of arrPeriode.entries()) {
        const isPelanggan = role === 'Pelanggan';
        let htmlAction = ``;
        let htmlDoc = ``;
        let isComplete = true;

        // cek apakah sudah bayar atau belum
        isComplete = dataKontrak[index].invoice.status == 5;
        let statusKirimTld = false;

        for (const doc of arrFind) {
            let findPeriode = cekStatusPeriode.find(cek => cek.periode == data.periode && cek.jenis == doc);
            if (doc === 'invoice' && data.permohonan_hash !== dataKontrak[index].invoice?.permohonan_hash) continue;
            if (doc === 'lhu' && data.permohonan?.file_lhu) continue;

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
            isComplete = isComplete && findPeriode?.status == 2;

        }

        // update status permohonan jika isComplete true
        if(isComplete && data.permohonan.status != 5){
            const params = new FormData();
            params.append('idPermohonan', data.permohonan.permohonan_hash);
            params.append('status', 5);

            ajaxPost('api/v1/permohonan/tambahPengajuan', params, result => {
            });
            data.permohonan.status = 5;
        }

        if(data.permohonan){
            const showEvaluasi = isPelanggan && data.permohonan.status == 11 && [3, 5].includes(Number(dataKontrak[index].jenis_layanan_2));
            htmlAction = showEvaluasi ?
                `<a class="btn btn-sm btn-outline-primary" href="${base_url}/permohonan/kontrak/e/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text"></i> Evaluasi</a>` :
                `<div class="d-flex flex-column justify-content-center align-items-center"><div class="fs-8">${data.permohonan.jenis_layanan_parent.name} - ${data.permohonan.jenis_layanan.name}</div><div>${statusFormat('permohonan', data.permohonan.status)}</div></div>`;

            if (!statusKirimTld && role == 'Staff Pengiriman') {
                htmlAction = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/staff/pengiriman/permohonan/kirim/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-send-fill"></i> Kirim TLD</a>`;
            } else {
                if (isPelanggan && [3, 5].includes(Number(dataKontrak[index].jenis_layanan_2)) && !statusKirimTld) {
                    htmlAction = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/permohonan/kontrak/e/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text"></i> Evaluasi</a>`;
                }
            }
            isComplete ? evaluasiActive = true : evaluasiActive = false;
        }else{
            if(role == 'Staff Pengiriman') {
                evaluasiActive && (htmlAction = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/staff/pengiriman/permohonan/kirim/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-send-fill"></i> Kirim TLD</a>`);
            } else if(role == 'Pelanggan' && [3, 5].includes(Number(dataKontrak[index].jenis_layanan_2))) {
                evaluasiActive && (htmlAction = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/permohonan/kontrak/e/${dataKontrak[index].kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text"></i> Evaluasi</a>`);
            }
            evaluasiActive = false;
        }
        
        html += `
            <div class="border-top py-2 d-flex justify-content-between align-items-center">
                <div class="px-2">
                    <span class="fw-semibold fs-6">Periode ${data.periode}</span>
                    <small class="text-body-tertiary"> - (${dateFormat(data.start_date, 4)} - ${dateFormat(data.end_date, 4)})</small>
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
    $(`#listPeriode${index}`).html(html);

    $(`#listPeriode${index}`).show();
}

function showModalEvaluasi(index) {
    const data = dataKontrak[index];
    $('#permohonanEvaluasiModal').modal('show');
}

function reload() {
    loadData();
}
