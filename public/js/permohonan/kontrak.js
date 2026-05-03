let dataKontrak = false;
let filterComp = false;
let modalDoc = false;
let signaturePad = false;
let modalAdendum = false;
let modalPeriode = false;
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

    modalDoc = new ModalDocument({
        withForm: true,
        formTitle: 'Form Tanda Tangan'
    });

    modalAdendum = new AdendumInformasi();

    filterComp = new FilterComponent('list-filter', {
        jenis: 'kontrak',
        filter : {
            status : true,
            jenis_tld : true,
            no_kontrak : true,
            date_range: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());

    $(`#list-pagination`).on('click', 'a', function(e){
        e.preventDefault();
        const pageno = e.target.dataset.page;
        loadData(pageno);
    });

    modalPeriode = new ModalPeriodeKontrak();
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
            let document_kontrak = data.document_kontrak.find(d => d.jenis == 'kontrak' || d.jenis == 'KontrakPengujian');
            let status_ttd_kontrak = document_kontrak?.ttd ? true : false;
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
            let arrFind = ['tld', 'lhu','invoice'];

            for (const pengiriman of data.pengiriman) {
                let detail = pengiriman.detail.filter(detail => arrFind.includes(detail.jenis));
                if(detail.length > 0){
                    detail.map(d => detailPengiriman.push({
                        jenis: d.jenis,
                        periode: d.periode ? d.periode : (pengiriman.periode ? pengiriman.periode : 0),
                        status: pengiriman.status,
                        no_resi: pengiriman.no_resi ?? false,
                        tipe_kontrak: pengiriman.permohonan ? pengiriman.permohonan.tipe_kontrak : false
                    }));
                }
            }

            const JL = jenislayanan(data.jenis_layanan_parent, data.jenis_layanan);
            // let activePeriode = '';
            let htmlPengembalian = '';
            // for (const periode of data.periode) {
                // const dokumenAktif = periodeMapDocument(periode, data, arrFind);
                // const isComplete = cekPeriodeComplete(periode, detailPengiriman, data, dokumenAktif);

                // let jml_periode = data.periode_count;
                // if(!tmpArrSewa.includes(JL)){
                //     if(data.is_zerocek && !data.is_have_tld){
                //         jml_periode = jml_periode - 1;
                //     }
                // }
            // }
            let lastPeriodeKontrak = (data.jml_periode) == data.periode_active?.periode;

            if(lastPeriodeKontrak && role.includes('Staff Pengiriman')){
                htmlPengembalian = pengembalianTLD(data);
            } else if(lastPeriodeKontrak) {
                htmlPengembalian = pengembalianTLD(data);
            }

            let hidden = role.includes('Pelanggan') ? 'd-none' : '';

            let btnAdendum = role.includes('Pelanggan') ? `
                <li class="dropdown-item cursoron small ${data.status == 2 ? 'd-none' : ''}">
                    <a href="${base_url}/permohonan/kontrak/a/${data.kontrak_hash}">
                        <i class="bi bi-pencil"></i> Adendum
                    </a>
                </li>
            ` : '';

            let btnTTD = '';
            if(role.includes('Manager') || role.includes('General Manager')) {
                if(!status_ttd_kontrak && document_kontrak){
                    btnTTD = `
                        <div class="mb-2 text-end fs-8">
                            <button class="btn btn-sm btn-outline-primary" onclick="showDocument('${data.kontrak_hash}', '${document_kontrak?.jenis}', 'Dokumen Kontrak')">
                                <i class="bi bi-pencil"></i> Tanda Tangan
                            </button>
                        </div>
                    `;
                }
            }

            let htmlStatusKontrak = status_ttd_kontrak ? `<span class="badge bg-success-subtle fw-normal rounded-pill text-success-emphasis">Sudah Ditandatangani</span>` : `<span class="badge bg-warning-subtle fw-normal rounded-pill text-warning-emphasis">Belum Ditandatangani</span>`;
            if(role.includes('Pelanggan')){
                htmlStatusKontrak = '';
            }

            // progress kontrak
            let jml_periode = data.periode_all.jml_periode;
            let periode_selesai = data.periode.filter(d => d.periode != 0 && d.selesai == 1).length;
            let progress = Math.round((periode_selesai / jml_periode) * 100);
            let txtProgress = '';
            if(progress == 100){
                txtProgress = 'Selesai';
            } else {
                txtProgress = `${periode_selesai}/${jml_periode}`;
            }
            let successClass = progress >= 100 ? 'bg-success' : '';
            let htmlProgress = `
                <div class="d-flex align-items-center gap-1 mt-2 flex-column">
                    <span class="fw-bold">Progress Periode:</span>
                    <div class="progress w-100" role="progressbar" aria-label="Example with label" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar ${successClass}" style="width: ${progress}%"></div>
                    </div>
                    <div class="text-center">
                        ${txtProgress}
                    </div>
                </div>
            `;

            html += `
                <div class="card mb-2 smooth-height hover-effect">
                    <div class="card-body row py-2">
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <div class="gap-2">
                                <span class="badge bg-primary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.tipe_kontrak}</span>
                                <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.jenis_layanan_parent.name} - ${data.jenis_layanan.name}</span>
                                ${htmlStatusKontrak}
                            </div>
                            <div>
                                ${statusFormat('kontrak',data.status)}
                            </div>
                        </div>
                        <div class="col-md-5 col-12">
                            <div class="fs-5 my-2">
                                <div class="fw-bold">
                                    #${data.no_kontrak}
                                </div>
                                <div class="text-body-tertiary fs-7">${data.jenis_tld.name} - Layanan ${data.layanan_jasa.nama_layanan}</div>
                                <div class="text-body-tertiary fs-7 ${hidden}">
                                    <div><i class="bi bi-building-fill"></i> ${data.pelanggan.perusahaan.nama_perusahaan}</div>
                                </div>
                                <div class="d-flex gap-3 text-body-tertiary fs-7">
                                    <div><i class="bi bi-calendar-fill"></i> ${dateFormat(data.created_at, 4)}</div>
                                    <div><i class="bi bi-cash-stack"></i> ${formatRupiah(data.total_harga)}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto align-self-center">
                            ${htmlProgress}
                        </div>
                        <div class="col-auto ms-auto align-self-center">
                            ${btnTTD}
                            <div class="d-flex gap-1 justify-content-end align-items-center">
                                <div class="dropdown d-inline-block ms-2">
                                    <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-1 overflow-hidden" data-id="${data.kontrak_hash}">
                                        <li>
                                            <a class="dropdown-item cursoron small"  onclick="showDetail(this)">
                                                <i class="bi bi-eye"></i> Lihat Detail
                                            </a>
                                        </li>
                                        ${btnAdendum}
                                    </ul>
                                </div>
                            </div>
                        </div>
                        ${(() => {
                            if(data.periode_active) {
                                let html = `
                                    <div class="px-3" id="listPeriodeNow${i}">
                                        ${modalPeriode.htmlPeriode(data.periode_active, data, detailPengiriman, arrFind)}
                                        ${role.includes('Staff Pengiriman') ? htmlPengembalian : ''}
                                    </div>
                                `;
                                // menampilkan button untuk melihat periode lain jika ada lebih dari 1 periode
                                if(data.periode_all?.jml_periode > 1) {
                                    html += `
                                        <div class="text-center">
                                            <button class="btn btn-sm w-100 btn-light" onclick="showPeriode('${data.kontrak_hash}')">Lihat Periode Lain</button>
                                        </div>
                                    `;
                                 }
                                return html;
                            }
                            return '';
                        })()}
                    </div>
                </div>
            `;
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

function showDocument(id_kontrak, jenis, title){
    let url = `laporan/${jenis}/${id_kontrak}`;

    modalDoc.show(url, {
        title: title,
        formHtml: `
            <div class="card shadow-sm border-0">
                <div class="card-body p-2 text-center" id="signatureKontrak"></div>
                <div class="mt-1 text-center card-footer border-0 bg-white">
                    <button class="btn btn-sm btn-primary" id="saveSignature" onclick="saveSignature(this, '${id_kontrak}')">Simpan Tanda Tangan</button>
                </div>
            </div>
        `
    });

    signaturePad = new SignatureSelect(document.getElementById('signatureKontrak'), {
        inputId: 'signature_kontrak',
        label: "Tanda Tangan Kontrak",
        placeholder: "Silakan tanda tangani di sini",
        signerUser: userActive
    });
}

function saveSignature(obj, id_kontrak){
    let [ttdValue, ttdBy] = signaturePad.getValue();

    if(!ttdValue){
        return Swal.fire({
            icon: "warning",
            text: "Harap berikan tanda tangan terlebih dahulu.",
        });
    }

    let params = new FormData();
    params.append('ttd', ttdValue);
    params.append('ttd_by', ttdBy);
    params.append('id_kontrak', id_kontrak);

    spinner('show', $(obj));

    ajaxPost(`api/v1/kontrak/sign`, params, result => {
        Swal.fire({
            icon: "success",
            text: result.msg,
        });
        modalDoc.hide();
        loadData();
        spinner('hide', $(obj));
    }, error => {
        Swal.fire({
            icon: "error",
            text: error.responseJSON?.msg ?? 'Terjadi kesalahan saat menyimpan tanda tangan.',
        });
        spinner('hide', $(obj));
    });
}
function showPeriode(id_kontrak) {
    modalPeriode.show(id_kontrak);
}

function showAdendumInformasi(obj){
    let id_periode = $(obj).data('id');
    let periode = $(obj).data('periode');
    let url = `api/v1/kontrak/getKontrakPeriode/${id_periode}`;

    modalAdendum.show({
        url: url,
        title: 'Adendum Informasi Periode ' + periode
    });
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
    const id = $(obj).closest('.dropdown-menu').data('id');
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
                    <div class="row row-cols-2 g-2">
                        <div class="col">
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
