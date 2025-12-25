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

                                    ${data.perusahaan ? `
                                        <span>
                                            <i class="bi bi-building-fill"></i> ${data.perusahaan}
                                        </span>
                                    ` : ``}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3 mb-lg-0 border-start-lg ps-lg-4">
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
                        <div class="mt-2 text-muted small" style="font-size: 0.75rem;">
                            PIC: <strong>${data.pelanggan}</strong> • ${dateFormat(data.created_at, 4)}
                        </div>
                    </div>

                    <div class="col-lg-1 text-lg-end text-start d-flex align-items-center justify-content-end" data-id='${data.id}'>
                        ${options.btnAction ?? ''}

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
            </div>
        </div>
    `;
    return elementList;
}
