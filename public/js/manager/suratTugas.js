let filterComp = false;
let signaturePad = false;
let dataPenyelia = false;
let modalDoc = new ModalDocument();
$(function () {
    loadData();

    detail = new Detail({
        jenis: 'surattugas',
        activeTab: 'proses',
        tab: {
            log: true
        },
        activeTab: 'log'
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'penyelia',
        filter : {
            status : true,
            jenis_tld : true,
            jenis_layanan : true,
            no_kontrak : true,
            perusahaan: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());

    loadEvent();
});

function loadEvent() {
    // TODO: Load event listeners if needed in the future
}

function loadData(page=1) {
    let params = {
        limit: 10,
        page: page,
        menu: 'ttd-surat',
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();
    filterValue.status && (params.filter.status = filterValue.status);
    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.perusahaan && (params.filter.id_perusahaan = filterValue.perusahaan);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        dataPenyelia = result.data;

        if (result.data.length === 0) {
            $(`#list-container`).html(htmlNoData()).show();
            $(`#list-pagination`).empty();
            $(`#list-placeholder`).hide();
            return;
        }

        const divTimelineTugas = [];
        const html = result.data.map(lhu => {
            const { cardHtml, timeline } = _renderCardItem(lhu);
            divTimelineTugas.push(timeline);
            return cardHtml;
        }).join('');

        $(`#list-container`).html(html).show();
        $(`#list-pagination`).html(createPaginationHTML(result.pagination));
        divTimelineTugas.forEach(t => t.render());
        $(`#list-placeholder`).hide();
    });
}

/**
 * Helper to render a single card item for Surat Tugas
 * @param {Object} lhu
 */
function _renderCardItem(lhu) {
    const permohonan = lhu.permohonan;
    const isTugasSigned = lhu.is_surat_tugas_signed;
    const isPengajuanSigned = lhu.is_pengajuan_signed;
    const hasTugas = lhu.penyelia_map.length > 0;
    const docPengujian = permohonan.dokumen.find(d => d.jenis === 'SuratPengujian');
    const docTugas = permohonan.dokumen.find(d => d.jenis === 'surattugas');

    let btnDocTugas = ``;
    let btnDocPengujian = ``;
    let htmlStatus = '';

    // status jobs yang aktif
    htmlStatus = statusFormat('penyelia', lhu.status);
    let aktifJobs = lhu.penyelia_map.filter(d => d.status == 1);
    aktifJobs.map(d => {
        htmlStatus += statusFormat('penyelia', d.jobs.status);
    });

    // Config: Button Surat Tugas
    let tugasBtn = {
        icon: 'bi-check2-circle',
        class: 'btn-light text-primary-emphasis',
        attr: `href="${base_url}/manager/surat_tugas/v/${lhu.penyelia_hash}"`,
        title: 'Verifikasi Surat Tugas'
    };

    if (lhu.status != 1 && hasTugas) {
        btnDocTugas = `
            <button class="btn btn-outline-primary btn-sm text-nowrap rounded-pill"
                data-url="laporan/${docTugas.jenis}/${docTugas.permohonan_hash}" data-title="Dokumen Surat Tugas"
                onclick="btnShowDoc(this)" title="Lihat Surat Tugas">
                <i class="bi bi-file-earmark-text"></i>
            </button>
        `;
        if (isTugasSigned === 1) {
            tugasBtn = {
                ...tugasBtn,
                icon: 'bi-check2-all',
                class: 'btn-light text-success',
                title: 'Surat Tugas Selesai (Signed)',
                attr: `href="${base_url}/manager/surat_tugas/s/${lhu.penyelia_hash}"`
            };
        } else if (isTugasSigned === 2) {
            tugasBtn = {
                ...tugasBtn,
                icon: 'bi-x-circle',
                class: 'btn-light text-danger',
                title: 'Surat Tugas Ditolak',
                attr: `href="${base_url}/manager/surat_tugas/s/${lhu.penyelia_hash}"`
            };
        }
    } else {
        tugasBtn = {
            ...tugasBtn,
            icon: 'bi-info-circle',
            class: 'btn-light text-secondary',
            title: 'Surat Tugas Belum Dibuat',
            attr: `disabled`
        }
    }

    let btnAction2 = `
        <div class="d-flex justify-content-between gap-1">
            <a class="btn ${tugasBtn.class} btn-sm text-nowrap rounded-pill w-100" title="${tugasBtn.title}" ${tugasBtn.attr}>
                <i class="bi ${tugasBtn.icon}"></i> Surat Tugas
            </a>
            ${btnDocTugas}
        </div>
    `;

    // Config: Button Surat Pengujian (Conditional)
    if (jenislayanan(permohonan.jenis_layanan_parent, permohonan.jenis_layanan) === 'EvaluasiTanpaKontrak') {
        let pengujianBtn = {
            icon: 'bi-check2-circle',
            class: 'btn-light text-primary-emphasis',
            attr: `onclick="verifikasiPengujian(this)"`,
            title: 'Verifikasi Surat Pengujian'
        };

        if (docPengujian) {
            btnDocPengujian = `
                <button class="btn btn-outline-primary btn-sm text-nowrap rounded-pill"
                    data-url="laporan/${docPengujian.jenis}/${docPengujian.permohonan_hash}"
                    data-title="Dokumen Surat Pengujian"
                    onclick="btnShowDoc(this)"
                    title="Lihat Surat Pengujian">
                    <i class="bi bi-file-earmark-text"></i>
                </button>
            `;

            if (isPengajuanSigned === 1) {
                pengujianBtn = {
                    ...pengujianBtn,
                    icon: 'bi-check2-all',
                    class: 'btn-light text-success',
                    title: 'Surat Pengujian Selesai (Signed)',
                    attr: `disabled`
                };
            } else if (isPengajuanSigned === 2) {
                pengujianBtn = {
                    ...pengujianBtn,
                    icon: 'bi-x-circle',
                    class: 'btn-light text-danger',
                    title: 'Surat Pengujian Ditolak',
                    attr: `disabled`
                };
            }
        } else {
            pengujianBtn = {
                ...pengujianBtn,
                icon: 'bi-info-circle',
                class: 'btn-light text-secondary',
                title: 'Surat Pengujian Belum Dibuat',
                attr: `disabled`
            };
        }

        btnAction2 = `
            <div class="d-flex justify-content-center flex-column gap-2">
                ${btnAction2}
                <div class="d-flex justify-content-between gap-1">
                    <button class="btn ${pengujianBtn.class} btn-sm text-nowrap rounded-pill w-100" title="${pengujianBtn.title}" ${pengujianBtn.attr}>
                        <i class="bi ${pengujianBtn.icon}"></i> Surat Pengujian
                    </button>
                    ${btnDocPengujian}
                </div>
            </div>
        `;
    }

    const btnAction = `
        <li>
            <a class="dropdown-item small cursor-pointer" title="Show detail" onclick="showDetail(this)">
                <i class="bi bi-info-circle me-2"></i> Detail
            </a>
        </li>
    `;

    const timeline = new Timeline({
        timeline: lhu.penyelia_map,
        status: lhu.status,
        id: lhu.penyelia_hash,
        startDate: lhu.start_date,
        endDate: lhu.end_date
    });

    const params = {
        tipeKontrak: permohonan.tipe_kontrak,
        jenisLayananParent: permohonan.jenis_layanan_parent.name,
        jenisLayanan: permohonan.jenis_layanan.name,
        format: 'penyelia',
        statusPenyelia: htmlStatus,
        jenisTld: permohonan.jenis_tld?.name ?? '-',
        namaLayanan: permohonan.layanan_jasa?.nama_layanan,
        periode: permohonan.periode,
        created_at: permohonan.created_at,
        kontrak: permohonan.kontrak?.no_kontrak,
        id: lhu.penyelia_hash,
        is_have_tld: permohonan.is_have_tld,
        is_zerocek: permohonan.is_zerocek,
        note: '',
        pelanggan: permohonan.pelanggan.name,
        divTimelineTugas: timeline
    };

    return {
        cardHtml: cardComponent(params, { btnMenuAction: btnAction, btnAction: btnAction2 }),
        timeline
    };
}

function reload() {
    loadData();
}
function showDetail(obj){
    const idPenyelia = $(obj).parent().parent().data("id");
    detail.show(`api/v1/penyelia/getById/${idPenyelia}`);
}
function clearFilter(){}

function verifikasiPengujian(obj){
    const idPenyelia = $(obj).closest('[data-id]').data('id');
    let find = dataPenyelia.find(d => d.penyelia_hash == idPenyelia);

    // jenis pengujian
    let zrcek = find.permohonan.is_zerocek ? 'Zero Cek' : '';
    let lJasa = find.permohonan.layanan_jasa.nama_layanan;
    let jTld = find.permohonan.jenis_tld.name;
    let jenisPengujian = zrcek + ' ' + lJasa + ' ' + jTld;

    // sample
    let htmlSample = `<div>${lJasa} ${jTld}</div>`;

    let kontrak = find.permohonan.kontrak;

    // template surat pengujian
    let dataSurat = find.permohonan.dokumen.find(d => d.doc_template?.name == 'SuratPengujian');
    let template = find.template_surat.find(d => d.name == 'SuratPengujian');

    let htmlPeriode = '';
    // periode
    for (const periode of kontrak.periode) {
        let startDate = dateFormat(periode.start_date, 6);
        let endDate = dateFormat(periode.end_date, 6);

        htmlPeriode += `<div>${kontrak.jumlah_kontrol} + ${kontrak.jumlah_pengguna} ${startDate} - ${endDate}</div>`;
    }

    // load pertanyaan
    let htmlPertanyaan = '<div class="list-pertanyaan">';
    for (const [i, pertanyaan] of template.data_pertanyaan.entries()) {
        // mengambil jawaban
        const foundAnswer = dataSurat.content_value?.alasan?.find(d => d.id == pertanyaan.id_pertanyaan);
        const answer = foundAnswer?.answer ?? '-'; // Menggunakan optional chaining dan nullish coalescing
        htmlPertanyaan += `
            <div class="mb-2">
                <label class="fw-bold text-dark mb-2 d-block">${i + 1}. ${pertanyaan.pertanyaan}</label>
                <div class="p-2 rounded bg-light border-start border-4 ${answer == 'siap' ? 'border-success' : 'border-danger'} shadow-sm" style="font-size: 0.9rem;">
                    ${answer}
                </div>
            </div>
        `;
    }
    htmlPertanyaan += '</div>';

    // SIGNATURE
    const canvas = $(PengujianComponent.selectors.ttd);
    signaturePad = new SignatureSelect(canvas, {
        inputId: 'managerValid',
        label: 'Nyatakan valid & Benar',
        placeholder: 'Menunggu validasi petugas...',
        signerUser: userActive
    });

    const dataPreview = {
        pemilik: find.permohonan.pelanggan.perusahaan.nama_perusahaan,
        alamat: find.permohonan.pelanggan.perusahaan.alamat[0].alamat,
        jenis_pengujian: jenisPengujian,
        samples: [htmlSample, htmlPeriode],
        pertanyaan: htmlPertanyaan
    }
    PengujianComponent.open(dataPreview, 'verify', {
        onApprove: () => { approvePengujian(idPenyelia) },
        onDecline: () => { declinePengujian(idPenyelia) }
    });
}

function approvePengujian(id){
    let [ttdValue, ttdBy] = signaturePad.getValue();
    if(!ttdValue) {
        return Swal.fire({
            icon: "warning",
            text: "Harap berikan tanda tangan terlebih dahulu.",
        });
    }
    showLoadingSwal('show');
    const params = new FormData();
    params.append('ttd', ttdValue);
    params.append('ttd_by', ttdBy);
    params.append('idPenyelia', id);
    params.append('type', 'approve');
    ajaxPost('api/v1/penyelia/approvePengujian', params, result => {
        showLoadingSwal('hide');
        Swal.fire({
            icon: "success",
            text: result.meta.message,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            PengujianComponent.hide();
            loadData();
        });
    }, error => {
        showLoadingSwal('hide');
    });
}

function declinePengujian(id){
    PengujianComponent.hide();
    showNoteAlertSwal((reason) => {
        showLoadingSwal('show');
        const params = new FormData();
        params.append('idPenyelia', id);
        params.append('type', 'decline');
        params.append('catatan', reason);
        ajaxPost('api/v1/penyelia/approvePengujian', params, result => {
            if(result.meta.code == 200) {
                showLoadingSwal('hide');
                loadData();
            }
        }, error => {
            showLoadingSwal('hide');
        });
    }, 'Tolak Surat Pengujian', 'Silahkan berikan Alasan Penolakan');
}

function btnShowDoc(obj) {
    const url = $(obj).data('url');
    const title = $(obj).data('title') || 'Dokumen';
    modalDoc.show(url, {
        title: title
    });
}
