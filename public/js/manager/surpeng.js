let filterComp = false;
let signaturePad = false;
let dataSurpeng = false;
let modalDoc = false;
let thisTab = 'progress';
$(function () {
    loadData();

    modalDoc = new ModalDocument({
        withForm: true,
        formTitle: 'Form Tanda Tangan'
    });
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
        filter: {
            status: true,
            jenis_tld: true,
            jenis_layanan: true,
            no_kontrak: true,
            perusahaan: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData(1));

    $('#list-pagination').on('click', 'a', function (e) {
        e.preventDefault();
        const pageno = e.target.dataset.page;
        loadData(pageno);
    });

    loadEvent();
});

function switchLoadTab(tab) {
    thisTab = tab;
    loadData(1);
}

function loadEvent() {
    // TODO: Load event listeners if needed in the future
}

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        menu: 'ttd-surpeng',
        tab: thisTab,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();
    filterValue.status && (params.filter.status = filterValue.status);
    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.perusahaan && (params.filter.id_perusahaan = filterValue.perusahaan);

    if (Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        dataSurpeng = result.data;

        if (result.data.length === 0) {
            $(`#list-container`).html(htmlNoData()).show();
            $(`#list-pagination`).empty();
            $(`#list-placeholder`).hide();
            return;
        }

        const html = result.data.map(doc => {
            const { cardHtml } = _renderCardItem(doc);
            return cardHtml;
        }).join('');

        $(`#list-container`).html(html).show();
        $(`#list-pagination`).html(createPaginationHTML(result.pagination));

        // Update tab counts
        if (result.tab_counts) {
            if ($('#count-progress').length) $('#count-progress').text(result.tab_counts.progress || 0);
            if ($('#count-selesai').length) $('#count-selesai').text(result.tab_counts.selesai || 0);
        }

        $(`#list-placeholder`).hide();
    });
}

/**
 * Helper to render a single card item for Surat Pengantar
 * @param {Object} lhu
 */
function _renderCardItem(doc) {
    const permohonan = doc.permohonan;
    const isSurpengSigned = doc.ttd ? true : false;
    // Config: Button Surat Pengantar
    let tugasBtn = {
        icon: 'bi-check2-circle',
        class: 'btn-light text-primary-emphasis',
        attr: '',
        title: 'Verifikasi Surat Pengantar'
    };

    let docPeriodeNow = false;

    tugasBtn = {
        ...tugasBtn,
        attr: `data-url="laporan/${doc.jenis}/${doc.kontrak.kontrak_hash}/${doc.kontrak.periode_next ? 1 : doc.periode}"
        data-title="Dokumen Surat Pengantar"
        data-idhash="${doc.dokumen_hash}"
        data-ttd="0"
        onclick="btnShowDoc(this)" title="Lihat Surat Pengantar"`,
    }

    docPeriodeNow = doc.kontrak.periode.find(p => p.periode == doc.periode);

    // Config: Button Surat Pengantar
    if (isSurpengSigned) {
        tugasBtn = {
            ...tugasBtn,
            icon: 'bi-check2-all',
            class: 'btn-light text-success',
            title: 'Surat Pengantar Selesai (Signed)',
            attr: `data-url="laporan/${doc.jenis}/${doc.kontrak.kontrak_hash}/${doc.kontrak.periode_next ? 1 : doc.periode}"
                data-title="Dokumen Surat Pengantar"
                data-idhash="${doc.dokumen_hash}"
                data-ttd="1"
                onclick="btnShowDoc(this)" title="Lihat Surat Pengantar"`
        };
    }
    let btnAction2 = `
        <div class="d-flex justify-content-between gap-1">
            <button class="btn ${tugasBtn.class} btn-sm text-nowrap rounded-pill w-100" title="${tugasBtn.title}" ${tugasBtn.attr}>
                <i class="bi ${tugasBtn.icon}"></i> Surat Pengantar
            </button>
        </div>
    `;

    const btnAction = `
        <li>
            <a class="dropdown-item small cursor-pointer" title="Show detail" onclick="showDetail(this)">
                <i class="bi bi-info-circle me-2"></i> Detail
            </a>
        </li>
    `;

    const params = {
        // tipeKontrak: doc.kontrak.tipe_kontrak,
        jenisLayananParent: doc.kontrak.jenis_layanan_parent.name,
        jenisLayanan: doc.kontrak.jenis_layanan.name,
        kontrak: doc.kontrak?.no_kontrak,
        format: 'penyelia',
        statusPenyelia: '',
        jenisTld: doc.kontrak.jenis_tld?.name ?? '-',
        namaLayanan: doc.kontrak.layanan_jasa?.nama_layanan,
        periode: doc.periode,
        periodeNow: docPeriodeNow,
        created_at: doc.kontrak.created_at,
        id: doc.dokumen_hash,
        is_have_tld: doc.kontrak.is_have_tld,
        is_zerocek: doc.kontrak.is_zerocek,
        note: '',
        pelanggan: doc.kontrak.pelanggan.name
    };

    return {
        cardHtml: cardComponent(params, { btnAction: btnAction2 })
    };
}

function reload() {
    loadData(1);
}
function showDetail(obj) {
    const idPenyelia = $(obj).parent().parent().data("id");
    detail.show(`api/v1/penyelia/getById/${idPenyelia}`);
}

function clearFilter() {
    filterComp && filterComp.clear();
    loadData(1);
}

function approvePengujian(id) {
    let [ttdValue, ttdBy] = signaturePad.getValue();
    if (!ttdValue) {
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

function btnShowDoc(obj) {
    const url = $(obj).data('url');
    const title = $(obj).data('title') || 'Dokumen';
    const idHash = $(obj).data('idhash');
    const isTtd = $(obj).data('ttd');

    const options = {
        title: title
    };

    if (isTtd == '0') {
        options.withForm = true;
        options.formHtml = `
        <div class="d-flex gap-2 flex-column">
            <div class="text-center m-2" id="signatureCanvas"></div>
            <div class="mt-1 text-center">
                <button class="btn btn-sm btn-primary" id="saveSignature" onclick="saveSignature(this, '${idHash}')">Simpan Tanda Tangan</button>
            </div>
        </div>
        `;
    } else {
        options.withForm = false;
    }
    modalDoc.show(url, options);

    if (isTtd == '0') {
        signaturePad = new SignatureSelect(document.getElementById('signatureCanvas'), {
            inputId: 'signature_surpeng',
            label: "Tanda Tangan Surat Pengujian",
            placeholder: "Silakan tanda tangani di sini",
            signerUser: userActive
        });
    }
}

function saveSignature(obj, id_hash) {
    let [ttdValue, ttdBy] = signaturePad.getValue();

    if (!ttdValue) {
        return Swal.fire({
            icon: "warning",
            text: "Harap berikan tanda tangan terlebih dahulu.",
        });
    }

    let params = new FormData();
    params.append('ttd', ttdValue);
    params.append('ttd_by', ttdBy);
    params.append('id_hash', id_hash);

    spinner('show', $(obj));

    ajaxPost(`api/v1/penyelia/actionSurpeng`, params, result => {
        Swal.fire({
            icon: "success",
            text: 'Tanda tangan berhasil disimpan.',
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
