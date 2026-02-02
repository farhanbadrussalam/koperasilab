/**
 * Function to generate a card component given data and options.
 *
 * @param {object} data - The data to generate the card component.
 * @param {object} options - The options to generate the card component.
 * @param {string} options.btnAction - The button to display on the card component.
 * @param {string} options.btnMenuAction - The menu button to display on the card component.
 *
 * @return {string} The HTML string of the card component.
 */
function cardComponent(data, options = {}) {
    const badgeClass = data.tipeKontrak == 'kontrak lama' ? 'bg-success-subtle text-success-emphasis border-success-subtle' : 'bg-primary-subtle text-primary-emphasis border-primary-subtle';
    let htmlPeriode = !data.periode ? `Zero cek` : 'Periode ' + data.periode;
    if(data.periode && data.is_have_tld && data.is_zerocek) {
        htmlPeriode += ' + Zero cek';
    }
    let htmlCatatan = ``;
    if(data.note) {
        htmlCatatan += `
            <div class="alert alert-danger mt-2 py-1 px-2 fs-8 mb-0">
                <i class="bi bi-exclamation-triangle"></i> Catatan: ${data.note}
            </div>
        `;
    }

    let jobsPenyelia = ``;
    if(data.divTimelineTugas){
        jobsPenyelia += data.divInfoTugas;
        jobsPenyelia += `
            <div class="col-md-12 collapse" id="timeline-progress-${data.id}">
                ${data.divTimelineTugas.elementCreate()}
            </div>
        `;
    }
    const elementList = `
        <div class="card border-1  shadow-sm rounded-3 mb-3 hover-effect transition-all">
            <div class="card-body p-3">
                <div class="row align-items-center">

                    <div class="col-lg-5 mb-3 mb-lg-0">
                        <div class="d-flex align-items-start gap-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" style="max-width: 280px;">${data.jenisTld} - Layanan ${data.namaLayanan}</h6>

                                <div class="d-flex align-items-center flex-wrap gap-2 text-muted small">
                                    ${data.kontrak ? `
                                        <span class="font-monospace fw-bold text-dark">
                                            <i class="bi bi-hash me-1 text-secondary"></i>${data.kontrak}
                                        </span>
                                    ` : ''}

                                    <span>
                                        <i class="bi bi-calendar-range me-1"></i> ${htmlPeriode}
                                    </span>

                                </div>
                                ${data.perusahaan ? `
                                    <div>
                                        <i class="bi bi-building-fill"></i> ${data.perusahaan}
                                    </div>
                                ` : ``}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 mb-3 mb-lg-0 border-start-lg ps-lg-4">
                        <small class="text-muted fw-bold d-block mb-2" style="font-size: 0.7rem;">LABEL & STATUS</small>
                        <div class="my-1">${statusFormat(data.format, data.status)}</div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge ${badgeClass} border border-info-subtle rounded-pill fw-normal px-3">
                                ${data.tipeKontrak}
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill fw-normal px-3">
                                ${data.jenisLayananParent} - ${data.jenisLayanan}
                            </span>
                        </div>
                        ${data.statusPenyelia ? `<div class="my-1">${data.statusPenyelia}</div>` : ``}
                        ${data.htmlLeftTime ? `
                            <div class="my-1">
                                ${data.htmlLeftTime}
                            </div>
                        ` : ``}
                        <div class="mt-2 text-muted small" style="font-size: 0.75rem;">
                            PIC: <strong>${data.pelanggan}</strong> • ${dateFormat(data.created_at, 4)}
                        </div>
                    </div>

                    <div class="col-lg-2 text-lg-end text-start d-flex align-items-center justify-content-end" data-id='${data.id}' data-index='${data.index ?? ''}'>
                        <div>
                            ${options.btnAction ?? ''}
                        </div>

                        ${options.btnMenuAction ? `
                            <div class="dropdown d-inline-block ms-2">
                                <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-1 overflow-hidden" data-id='${data.id}'>
                                    ${options.btnMenuAction}
                                </ul>
                            </div>
                        ` : ''}
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

function cardPenggunaComponent(data, options = {}) {
    let inisial = data.name ? data.name.substring(0, 1) : 'A';
    let txtRadiasi = '';
    data.radiasi?.map(nama_radiasi => txtRadiasi += `
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"
            style="font-size: 0.7rem;">
            ${nama_radiasi}
        </span>
    `)

    const elementList = `
        <div class="card border mb-2 hover-shadow-sm transition-all">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-5 d-flex align-items-center mb-2 mb-md-0">
                        ${data.isCheckedEvaluasi ? `
                            <div class="p-2">
                                <input class="form-check-input"
                                    name="checkTldPengguna" type="checkbox"
                                    value="${data.idHash}" aria-label=""
                                    id="checkTldPengguna${data.index}">
                            </div>` : ``}

                        <span class="fw-bold text-muted me-3" style="width: 20px;">${data.index + 1}</span>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light text-primary fw-bold d-flex justify-content-center align-items-center me-2"
                                style="width: 35px; height: 35px;">${inisial}</div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark small">${data.name}</h6>
                                <small class="text-muted d-md-none">${data.divisi}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="d-flex flex-wrap gap-1">
                            ${txtRadiasi}
                        </div>
                    </div>

                    <div class="col-md-3 text-md-end d-flex justify-content-between justify-content-md-end align-items-center">
                        <span class="font-monospace fw-bold text-dark bg-light px-2 py-1 rounded border me-2"
                            id="tldNoSeri_${data.index}_pengguna_view">${data.no_seri_tld ? data.no_seri_tld : 'Tidak Ada'}</span>

                        <input type="hidden"
                            class="form-control rounded-start"
                            value="${data.no_seri_tld}"
                            id="tldNoSeri_${data.index}_pengguna" placeholder="Pilih No Seri" readonly>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown">
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
                                <li>
                                    <a class="dropdown-item small show-popup-image" href="${data.fileKtp}">
                                        <i class="bi bi-person-badge me-2"></i>Lihat KTP
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    return elementList;
}

function cardKontrolComponent(data, options = {}) {
    const elementList = `
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                ${data.isCheckedEvaluasi ? `
                    <div class="p-2">
                        <input class="form-check-input"
                            name="checkTldKontrol" type="checkbox"
                            value="${data.tldHash}" aria-label="Checkbox for following text input"
                            id="checkTldKontrol${data.index}">
                    </div>
                ` : ``}
                <div>
                    <h6 class="mb-0 fw-bold text-dark small">${data.name}</h6>
                    <small class="text-muted d-none">Unit Pembanding</small>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <input type="hidden" class="form-control rounded-start" value="${data.no_seri_tld}" id="tldNoSeri_${data.index}_kontrol" placeholder="Pilih No Seri" readonly>
                <span class="font-monospace fw-bold text-dark bg-white px-3 py-1 rounded border shadow-sm" id="tldNoSeri_${data.index}_kontrol_view">${data.no_seri_tld ?? 'No Seri'}</span>
                ${!data.htmlDisabled ? `
                    <button type="button"
                        class="btn btn-sm btn-link text-decoration-none ms-2"
                        data-id="tldNoSeri_${data.index}_kontrol"
                        onclick="openInventory(this, 'kontrol')">Ganti</button>
                ` : ``}
            </div>
        </div>
    `;

    return elementList;
}
