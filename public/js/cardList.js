/**
 * Function to generate a card component given data and options.
 *
 * @param {object} data - The data to generate the card component.
 * @param {object} options - The options to generate the card component.
 * @param {string} options.btnAction - The button to display on the card component.
 *
 * @return {string} The HTML string of the card component.
 */
function cardComponent(data, options = {}) {
    const badgeClass = data.tipeKontrak == 'kontrak lama' ? 'bg-success-subtle' : 'bg-primary-subtle';
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
        <div class="card mb-2">
            <div class="card-body row align-items-center py-2 position-relative">
                <div class="col-auto">
                    <div class="">
                        <span class="badge ${badgeClass} fw-normal rounded-pill text-secondary-emphasis">${data.tipeKontrak}</span>
                        <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.jenisLayananParent} - ${data.jenisLayanan}</span>
                        <span> | ${statusFormat(data.format, data.status)}</span>
                    </div>
                    <div class="fs-5 my-2">
                        <span class="fw-bold">${data.jenisTld} - Layanan ${data.namaLayanan}</span>
                        ${data.perusahaan ? `
                            <div class="text-body-tertiary fs-7">
                                <div><i class="bi bi-building-fill"></i> ${data.perusahaan}</div>
                            </div>` : ''}
                    </div>
                    <div class="d-flex gap-3 text-body-tertiary fs-7">
                        ${data.pelanggan ? `<div><i class="bi bi-person-fill"></i> ${data.pelanggan}</div>` : ''}
                        <span><i class="bi bi-calendar-range"></i> ${htmlPeriode}</span>
                        ${data.created_at ? `<span><i class="bi bi-calendar-fill"></i> ${dateFormat(data.created_at, 4)}</span>` : ''}
                        ${data.kontrak ? `<div><i class="bi bi-file-text"></i> ${data.kontrak}</div>` : ''}
                    </div>

                    <!-- Catatan -->
                    ${htmlCatatan}
                </div>
                <div class="col-6 col-md-3 text-end ms-auto" data-id='${data.id}'>
                    ${options.btnAction ?? ''}
                </div>
            </div>
        </div>
    `;
    return elementList;
}
