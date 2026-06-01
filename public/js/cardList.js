/**
 * Function to generate a card component for a given data and options.
 *
 * @param {object} data - The data to generate the card component.
 * @param {object} options - The options to generate the card component.
 * @param {string} options.btnAction - The button action to generate on the card component.
 * @param {string} options.btnMenuAction - The button menu action to generate on the card component.
 * @param {string} options.status - The status to display on the card component.
 * @param {string} options.format - The format of the status to display on the card component.
 *
 * @return {string} The HTML string of the card component.
 */
function cardComponent(data, options = {}) {
    let htmlTipeKontrak = '';
    if (data.tipeKontrak !== undefined && data.tipeKontrak) {
        let classBadge = '';
        switch (data.tipeKontrak) {
            case 'kontrak lama':
                classBadge = 'bg-success-subtle text-success-emphasis border-success-subtle';
                break;
            case 'adendum':
                classBadge = 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
                break;
            default:
                classBadge = 'bg-primary-subtle text-primary-emphasis border-primary-subtle';
                break;
        }
        htmlTipeKontrak = `
            <span class="badge ${classBadge} border border-info-subtle rounded-pill fw-normal px-3">
                ${data.tipeKontrak}
            </span>
        `;

        if (data.jenisLayananParent == 'Evaluasi' && data.jenisLayanan == 'Tanpa Kontrak') {
            htmlTipeKontrak = '';
        }
    }

    const htmlPeriode = data.periode !== undefined ? (() => {
        let per = data.periode == 0 ? `Zero Check` : 'Periode ' + data.periode;
        if (data.periode == 1 && data.is_have_tld && data.is_zerocek) {
            per += ' + Zero Check';
        }
        if (data.periodeNow) {
            if (data.periodeNow.periode != 0) {
                let rangeDate = range_date(data.periodeNow.start_date, data.periodeNow.end_date, 1);
                per += `<span class="text-muted">: ${rangeDate.start} - ${rangeDate.end}</span>`
            }
        }

        if (data.periode == null) {
            per = 'Periode berikutnya';
        }
        return `
            <span class="small">
                <i class="bi bi-calendar-range me-1"></i> ${per}
            </span>
        `;
    })() : '';

    const htmlCatatan = data.note ? `
        <div class="alert alert-danger mt-2 py-1 px-2 fs-8 mb-0">
            <i class="bi bi-exclamation-triangle"></i> Catatan: ${data.note}
        </div>
    ` : '';

    // `data.title` takes precedence. `??` handles `null` or `undefined` gracefully.
    const htmlTitle = data.title ?? (data.jenisTld !== undefined ? `${data.jenisTld} - Layanan ${data.namaLayanan}` : '');

    const htmlKontrak = data.kontrak !== undefined ? `
        <span class="font-monospace fw-bold text-dark">
            <i class="bi bi-hash me-1 text-secondary"></i>${data.kontrak}
        </span>
    ` : '';

    const htmlPerusahaan = data.perusahaan !== undefined ? `
        <div>
            <span>
                <i class="bi bi-building-fill"></i> ${data.perusahaan}
            </span>
        </div>
    ` : '';

    const htmlStatus = data.status !== undefined ? statusFormat(data.format, data.status) : '';

    const htmlJenisLayanan = data.jenisLayanan !== undefined ? `
        <span class="badge bg-light text-secondary border rounded-pill fw-normal px-3">
            ${data.jenisLayananParent} - ${data.jenisLayanan}
        </span>
    ` : '';

    const htmlStatusPenyelia = data.statusPenyelia !== undefined ? `<div class="my-1 d-flex align-items-center gap-1">${data.statusPenyelia}</div>` : '';

    const htmlLeftTime = data.htmlLeftTime !== undefined ? `
        <div class="my-1">
            ${data.htmlLeftTime}
        </div>
    ` : '';

    const htmlNoResi = data.no_resi !== undefined ? `
        <div>
            <div class="fw-light">No resi : ${data.no_resi ?? 'Belum ada'}</div>
        </div>
    ` : '';

    const htmlItemsPengiriman = data.items !== undefined ? (() => {
        const count = data.items?.length ?? 0;
        if (count === 0) {
            return '<span class="fw-light text-muted">Items: Belum ada</span>';
        }
        const encoded = encodeURIComponent(JSON.stringify(data.items));
        const idPengiriman = data.id ?? '';
        return `
            <span class="fw-light">
                Items : 
                <button type="button" 
                        class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0 border-primary-subtle bg-primary-subtle text-primary" 
                        style="font-size: 0.72rem; line-height: 1.5; vertical-align: middle;"
                        onclick="showItemsDetail('${encoded}', '${idPengiriman}')">
                    <i class="bi bi-eye-fill me-1"></i>${count} Items
                </button>
            </span>
        `;
    })() : '';

    const htmlAlamatPengiriman = data.alamat !== undefined ? `
         • <small class="subdesc text-body-secondary fw-light lh-sm">
            <div class="tooltip-container cursoron" data-bs-toggle="tooltip" data-bs-placement="top" title="${data.alamat.alamat}">
                Alamat ${data.alamat.jenis}
            </div>
        </small>
    ` : '';

    const htmlPelanggan = data.pelanggan !== undefined ? `
        <div class="mt-2 text-muted small" style="font-size: 0.75rem;">
            PIC: <strong>${data.pelanggan}</strong> • ${dateFormat(data.created_at, 4)}
        </div>
    ` : '';

    const htmlDurasi = data.send_at !== undefined ? `
        <div class="mt-1 d-flex align-items-center">
            <div class="row">
                <span>Dikirim</span>
                <small class="subdesc text-body-secondary fw-light lh-sm">
                    ${data.send_at ? dateFormat(data.send_at, 4) : '-'}
                </small>
            </div>
            <div class="row">
                <span>Diterima</span>
                <small class="subdesc text-body-secondary fw-light lh-sm">
                    ${data.recived_at ? dateFormat(data.recived_at, 4) : '-'}
                </small>
            </div>
        </div>
    ` : '';

    const jobsPenyelia = data.divTimelineTugas !== undefined && data.divTimelineTugas && data.divTimelineTugas.getTimeline().length > 0 ? `
        <div class="col-md-12 collapse" id="timeline-progress-${data.id}">
            ${data.divTimelineTugas.elementCreate()}
        </div>
    ` : '';

    const divInfoTugas = data.divTimelineTugas !== undefined && data.divTimelineTugas ? `
        ${data.divTimelineTugas.buttonCreate()}
    ` : '';

    const subTitle = data.subTitle !== undefined ? `
        <div class="text-muted small">
            ${data.subTitle}
        </div>
    ` : '';

    const htmlCheckbox = data.checkbox ? `
        <div class="form-check mb-0 d-flex align-items-start">
            <input class="form-check-input check-lhu cursor-pointer shadow-sm border-secondary-subtle" type="checkbox" value="${data.id}" data-index="${data.index}" style="width: 1.3em; height: 1.3em;" ${data.isChecked ? 'checked' : ''}>
        </div>
    ` : '';

    const elementList = `
        <div class="card border-1  shadow-sm rounded-3 mb-3 hover-effect transition-all">
            <div class="card-body p-3">
                <div class="row align-items-center position-relative">
                    <div class="col-lg-5 mb-3 mb-lg-0">
                        <div class="d-flex align-items-start gap-1 flex-column">
                            <h6 class="fw-bold text-dark text-truncate mb-0" style="max-width: 280px;">${htmlTitle}</h6>
                            ${htmlNoResi}
                            <div class="d-flex align-items-center flex-wrap gap-2 text-muted small">
                                ${htmlKontrak}
                            </div>
                            ${htmlPeriode}
                            ${htmlPerusahaan}
                            ${divInfoTugas}
                            ${subTitle}
                        </div>
                    </div>
                    <div class="col-lg-5 mb-3 mb-lg-0 border-start-lg ps-lg-4">
                        <small class="text-muted fw-bold d-block mb-2" style="font-size: 0.7rem;">LABEL & STATUS</small>
                        <div class="my-1">${htmlStatus}</div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            ${htmlItemsPengiriman}
                            ${htmlAlamatPengiriman}
                            ${htmlTipeKontrak}
                            ${htmlJenisLayanan}
                        </div>
                        ${htmlStatusPenyelia}
                        ${htmlLeftTime}
                        ${htmlPelanggan}
                        ${htmlDurasi}
                    </div>

                    <div class="col-lg-2 text-lg-end text-start d-flex align-items-center justify-content-end justify-content-lg-end mt-2 mt-lg-0" data-id='${data.id}' data-index='${data.index ?? ''}'>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            ${options.btnAction ?? ''}
                        </div>

                        ${options.btnMenuAction ? `
                            <div class="dropdown d-inline-block ms-2">
                                <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-1 overflow-hidden" data-id='${data.id}' data-nokontrak='${data.kontrak ?? ''}'>
                                    ${options.btnMenuAction}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                    <div class="position-absolute top-0 end-0 text-muted small w-auto">
                        ${htmlCheckbox}
                    </div>
                </div>
                <!-- Catatan -->
                ${htmlCatatan}
                ${jobsPenyelia}
            </div>
        </div>
    `;
    return elementList;
}

/**
 * Function to generate a card component for a pengguna given data and options.
 *
 * @param {object} data - The data to generate the card component.
 * @param {object} options - The options to generate the card component.
 * @param {string} options.status - The status to display on the card component.
 * @param {string} options.label_tld - The label to display on the card component.
 * @param {boolean} options.is_have_tld - Whether the card component has a TLD or not.
 *
 * @return {string} The HTML string of the card component.
 */
function cardPenggunaComponent(data, options = {}) {
    let inisial = data.name ? data.name.substring(0, 1) : 'A';
    let txtRadiasi = loadRadiasi(data.radiasi, 2);

    let htmlEval = ``;

    let txtStatus = '';
    let htmlPergantian = '';
    let btnRemove = ``;
    let btnGantiPengguna = ``;
    if (options.status == 'baru') {
        txtStatus += `
            <span class="badge bg-primary-subtle text-primary-emphasis border-primary-subtle border border-info-subtle rounded-pill fw-normal px-3">
                Baru
            </span>
        `;

        if (options.is_adendum || options.is_btn_remove) {
            btnRemove = `
                <li>
                    <a class="dropdown-item small text-danger" href="javascript:void(0)" data-id="${data.idHash}" onclick="removePengguna(this)">
                        <i class="bi bi-trash me-2" title="Hapus"></i>Hapus
                    </a>
                </li>
            `;
        }
    } else if (options.status == 'lama') {
        if (options.is_adendum) {
            btnGantiPengguna = `
                <li>
                    <a class="dropdown-item small text-warning" href="#" data-id="${data.idHash}" onclick="gantiPengguna(this)">
                        <i class="bi bi-pencil me-2" title="Ganti Pengguna"></i>Ganti Pengguna
                    </a>
                </li>
            `;
        }
    }

    if (data.pengguna_baru) {
        txtStatus += `
            <span class="badge bg-warning-subtle text-warning-emphasis border-warning-subtle border border-info-subtle rounded-pill fw-normal px-3">
                Ganti
            </span>
        `;

        if (data.pengguna_baru.pengguna_hash) {
            txtStatus += `
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-id="${data.pengguna_baru.pengguna_hash}" onclick="deletePergantian(this)" title="Delete">Batal</button>

            `;
        }

        htmlPergantian += `
            <span><i class="bi bi-arrow-right"></i></span>
            <span class="fw-bold">${data.pengguna_baru.name}</span>
        `;
    }

    if (options.is_have_tld) {
        htmlEval = `
            <hr class="my-1">
            <div class="col-12">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" id="tldNoSeri_${data.index}_pengguna" placeholder="Pilih No Seri" readonly="" value="${data.no_seri_tld ?? ''}">
                    <button type="button"
                        class="input-group-text btn btn-sm btn-outline-secondary"
                        data-id="tldNoSeri_${data.index}_pengguna" title="Change" onclick="openInventory(this, 'pengguna')">
                        <i class="bi bi-pencil"></i> Ganti
                    </button>
                </div>
            </div>
        `;
    }

    const elementList = `
        <div class="card border mb-2">
            <div class="card-body py-2 px-3">
                <div class="row align-items-center">
                    <div class="col-auto d-flex align-items-center mb-2 mb-md-0">
                        ${data.isCheckedEvaluasi ? `
                            <div class="p-2">
                                <input class="form-check-input"
                                    name="checkTldPengguna" type="checkbox"
                                    value="${data.idHash}" aria-label=""
                                    id="checkTldPengguna${data.index}">
                            </div>` : ``}

                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light text-primary fw-bold d-none justify-content-center align-items-center me-2"
                                style="width: 35px; height: 35px;">${inisial}</div>
                            <div class="gap-2>
                                <h6 class="mb-0 fw-bold text-dark small d-flex gap-2 align-items-center">${data.name} ${htmlPergantian}</h6>
                                <div>
                                    <small class="text-muted">${data.divisi}</small>
                                </div>

                                <div class="d-flex flex-wrap gap-1">
                                    ${txtRadiasi}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ms-auto col-auto text-md-end d-flex justify-content-between justify-content-md-end align-items-center gap-1">
                        ${txtStatus}
                        ${options.label_tld ? `
                            <span class="font-monospace fw-bold text-dark bg-light px-2 py-1 rounded border me-2"
                                id="tldNoSeri_${data.index}_pengguna_view">${data.no_seri_tld ? data.no_seri_tld : 'Tidak Ada'}</span>
                        ` : ``}

                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}'>
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 overflow-hidden">
                                ${!data.htmlDisabled ? `
                                    <li>
                                        <a class="dropdown-item small"
                                            href="javascript:void(0)"
                                            data-id="tldNoSeri_${data.index}_pengguna"
                                            onclick="openInventory(this, 'pengguna')">
                                            <i class="bi bi-pencil me-2"></i>Ganti TLD
                                        </a>
                                    </li>
                                ` : ``}
                                ${btnGantiPengguna}
                                <li>
                                    <a class="dropdown-item small show-popup-image" href="${data.fileKtp}">
                                        <i class="bi bi-person-badge me-2"></i>Lihat KTP
                                    </a>
                                </li>
                                ${btnRemove}
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-12">
                        ${htmlEval}
                    </div>
                </div>
            </div>
        </div>
    `;

    return elementList;
}

/**
 * Generate card component for kontrol.
 *
 * @param {object} data - The data to generate the card component.
 * @param {object} options - The options to generate the card component.
 * @param {boolean} options.is_btn_remove - Whether the card component has a remove button or not.
 * @param {boolean} options.is_have_tld - Whether the card component has a TLD or not.
 * @param {boolean} options.add_kontrol - Whether the card component has an add kontrol button or not.
 * @param {string} options.label_tld - The label to display on the card component.
 *
 * @return {string} The HTML string of the card component.
 */
function cardKontrolComponent(data, options = {}) {
    let htmlAddKontrol = '';
    let htmlEvaluasi = '';

    if (options.is_have_tld) {
        htmlEvaluasi = `<hr class="my-2 opacity-50">`;

        for ([i, rincian] of data.rincian.entries()) {
            htmlEvaluasi += `
                <div class="col-12 mb-2">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.72rem;">TLD Seri #${i + 1}</label>
                    <div class="input-group shadow-xs rounded overflow-hidden">
                        <input type="text" class="form-control form-control-sm bg-light border-end-0" id="tldNoSeri_${data.index}_${i}_kontrol" value="${rincian.tld?.no_seri_tld ?? ''}" placeholder="Pilih No Seri" readonly>
                        <button type="button" class="btn btn-sm btn-outline-primary border-start-0 px-3 fw-bold" data-id="tldNoSeri_${data.index}_${i}_kontrol" title="Ganti" onclick="openInventory(this, 'kontrol')"><i class="bi bi-pencil me-1"></i> Ganti</button>
                    </div>
                </div>
            `;
        }
    }

    if (options.add_kontrol) {
        htmlAddKontrol = `
            <div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 shadow-sm gap-2">
                <span class="small fw-bold text-muted me-1" style="font-size: 0.72rem;">Jumlah:</span>
                <button type="button" class="btn btn-xs btn-link p-0 text-danger lh-1" data-id="${data.tldHash}" onclick="changeCountKontrol('minus', ${data.rincian.length}, this)" style="font-size: 1.15rem;">
                    <i class="bi bi-dash-circle-fill"></i>
                </button>
                <span class="fw-bold text-dark px-1" style="min-width: 15px; text-align: center;">${data.rincian.length}</span>
                <button type="button" class="btn btn-xs btn-link p-0 text-primary lh-1" data-id="${data.tldHash}" onclick="changeCountKontrol('plus', ${data.rincian.length}, this)" style="font-size: 1.15rem;">
                    <i class="bi bi-plus-circle-fill"></i>
                </button>
            </div>
        `;
    }

    let txtStatus = '';
    if (options.status == 'baru') {
        txtStatus += `
            <span class="badge bg-primary-subtle text-primary-emphasis border-primary-subtle border border-info-subtle rounded-pill fw-normal px-3">
                Baru
            </span>
        `;
    }

    let hasActions = !data.htmlDisabled || options.is_btn_remove;
    let htmlActionsDropdown = '';

    if (hasActions) {
        let dropdownItems = '';
        if (!data.htmlDisabled) {
            dropdownItems += `
                <li>
                    <a class="dropdown-item small" href="javascript:void(0)" data-id="tldNoSeri_${data.index}_kontrol" onclick="openInventory(this, 'kontrol')">
                        <i class="bi bi-pencil me-2 text-primary"></i>Ganti TLD
                    </a>
                </li>
            `;
        }
        if (options.is_btn_remove) {
            dropdownItems += `
                <li>
                    <a class="dropdown-item small text-danger" href="javascript:void(0)" data-id="${data.tldHash}" onclick="deleteKontrol(this)">
                        <i class="bi bi-trash me-2"></i>Hapus Kontrol
                    </a>
                </li>
            `;
        }

        htmlActionsDropdown = `
            <div class="dropdown">
                <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 overflow-hidden">
                    ${dropdownItems}
                </ul>
            </div>
        `;
    }

    const elementList = `
    <div class="card border mb-2">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center">
                <!-- Sisi Kiri: Checkbox & Nama Kontrol -->
                <div class="col-auto d-flex align-items-center mb-2 mb-md-0">
                    ${data.isCheckedEvaluasi ? `
                        <div class="p-2">
                            <input class="form-check-input shadow-xs"
                                name="checkTldKontrol" type="checkbox"
                                value="${data.idHash}" aria-label=""
                                id="checkTldKontrol${data.index}">
                        </div>
                    ` : ``}
                    <div>
                        <h6 class="mb-0 fw-bold text-dark small d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-primary"></i> ${data.name}
                        </h6>
                    </div>
                </div>

                <!-- Sisi Kanan: Badges, Counter, Serial Number & Dropdown Aksi -->
                <div class="ms-auto col-auto text-md-end d-flex justify-content-between justify-content-md-end align-items-center gap-2">
                    <input type="hidden" value="${data.no_seri_tld ?? ''}" id="tldNoSeri_${data.index}_kontrol">
                    
                    ${txtStatus}
                    
                    ${htmlAddKontrol}

                    ${options.label_tld ? `
                        <span class="font-monospace fw-bold text-dark bg-light px-2 py-1 rounded border" 
                            id="tldNoSeri_${data.index}_kontrol_view">${data.no_seri_tld ? data.no_seri_tld : 'Tidak Ada'}</span>
                    ` : ``}

                    ${htmlActionsDropdown}
                </div>

                <!-- Sisi Bawah: Form Evaluasi Multi-TLD Kontrol (jika ada) -->
                <div class="col-md-12">
                    ${htmlEvaluasi ?? ''}
                </div>
            </div>
        </div>
    </div>
    `;

    return elementList;
}

/**
 * Global function to show shipment items details in a beautiful small modal.
 * @param {string} encodedItems - URL-encoded JSON string of the items array.
 * @param {string} idPengiriman - ID of the shipment.
 */
function showItemsDetail(encodedItems, idPengiriman) {
    const items = JSON.parse(decodeURIComponent(encodedItems));

    let rows = '';
    items.forEach((item, index) => {
        let jenis = item.jenis ? (item.jenis === 'lhu' ? 'LHU' : (item.jenis === 'tld' ? 'TLD' : item.jenis.charAt(0).toUpperCase() + item.jenis.slice(1))) : '-';
        let periodeText = '-';
        if (item.periode !== undefined && item.periode !== null) {
            periodeText = item.periode == 0 ? '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill fw-normal fs-8">Zero Check</span>' : `Periode ${item.periode}`;
        }

        rows += `
            <tr>
                <td class="text-center font-monospace small">${index + 1}</td>
                <td><strong class="text-dark small">${jenis}</strong></td>
                <td><span class="small">${periodeText}</span></td>
            </tr>
        `;
    });

    const modalHtml = `
        <div class="modal fade" id="modalItemsDetail" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-primary text-white py-3 border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold fs-6"><i class="bi bi-box-seam me-2"></i>Rincian Dokumen</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="mb-3 small text-muted text-center font-monospace">ID: ${idPengiriman}</div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">No</th>
                                        <th>Jenis</th>
                                        <th>Periode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-2">
                        <button type="button" class="btn btn-secondary btn-sm w-100 rounded-3" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove any existing modal first
    $('#modalItemsDetail').remove();
    $('body').append(modalHtml);
    $('#modalItemsDetail').modal('show');
}
