<div class="modal fade" id="modalPeriode" tabindex="-1" aria-labelledby="modalPeriodeLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom py-3">
                <h1 class="modal-title fs-5 fw-bold text-dark d-flex align-items-center gap-2" id="modalPeriodeLabel">
                    <i class="bi bi-calendar3 text-primary fs-4"></i> Daftar Periode Kontrak
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light bg-opacity-25">
                <div id="listPeriodeContainer" class="d-flex flex-column gap-3">
                    <!-- List periode akan di-render di sini -->
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary px-4 rounded-pill"
                    data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        class ModalPeriodeKontrak {
            constructor(options = {}) {
                this.modalId = options.modalId ?? 'modalPeriode';
                this.containerId = options.containerId ?? 'listPeriodeContainer';
                this.isPelanggan = role.includes('Pelanggan');
                this.isPengiriman = role.includes('Staff Pengiriman');
            }

            show(id_kontrak) {
                let skeleton = '';
                for (let i = 0; i < 3; i++) {
                    skeleton += `
                        <div class="card border border-light-subtle rounded-3 shadow-sm mb-3 placeholder-glow">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6 border-end border-light-subtle py-2">
                                        <span class="placeholder col-4 mb-2 d-block" style="height: 1.2rem;"></span>
                                        <div class="row row-cols-1 row-cols-sm-2 g-2">
                                            <div class="col"><div class="placeholder col-12 py-3 rounded-2" style="height: 38px;"></div></div>
                                            <div class="col"><div class="placeholder col-12 py-3 rounded-2" style="height: 38px;"></div></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 border-end border-light-subtle py-2 px-md-3">
                                        <span class="placeholder col-6 mb-2 d-block" style="height: 0.8rem;"></span>
                                        <span class="placeholder col-8 d-block" style="height: 1.2rem;"></span>
                                    </div>
                                    <div class="col-md-3 py-2 px-md-3">
                                        <span class="placeholder col-6 mb-2 d-block" style="height: 0.8rem;"></span>
                                        <span class="placeholder col-10 d-block rounded-pill" style="height: 31px;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                $(`#${this.containerId}`).html(skeleton);
                $(`#${this.modalId}`).modal('show');

                let html = '';

                ajaxGet(`api/v1/kontrak/getById/${id_kontrak}`, false, res => {
                    let kontrak = res.data;

                    if (kontrak?.periode?.length > 0) {
                        let detailPengiriman = [];
                        let arrFind = ['invoice', 'tld', 'lhu'];

                        for (const pengiriman of kontrak.pengiriman) {
                            let detail = pengiriman.detail.filter(detail => arrFind.includes(detail.jenis));
                            if (detail.length > 0) {
                                detail.map(d => detailPengiriman.push({
                                    jenis: d.jenis,
                                    periode: d.periode ? d.periode : (pengiriman.periode ?
                                        pengiriman.periode : 0),
                                    status: pengiriman.status,
                                    no_resi: pengiriman.no_resi ?? false,
                                    tipe_kontrak: pengiriman.permohonan ? pengiriman.permohonan
                                        .tipe_kontrak : false,
                                    permohonan_hash: pengiriman.permohonan ? pengiriman.permohonan
                                        .permohonan_hash : false
                                }));
                            }
                        }
                        let permohonanZerocek = null;
                        if (kontrak.is_zerocek == 1) {
                            if (kontrak.is_have_tld == 1) {
                                let periodOne = kontrak.periode.find(p => p && p.periode == 1);
                                if (periodOne && periodOne.permohonan) {
                                    permohonanZerocek = periodOne.permohonan;
                                }
                            } else {
                                let periodZero = kontrak.periode.find(p => p && p.periode == 0);
                                if (periodZero && periodZero.permohonan) {
                                    permohonanZerocek = periodZero.permohonan;
                                }
                            }
                        }

                        kontrak.periode.forEach(data => {
                            if (!data) return;
                            if (data.periode == 1 && kontrak.is_zerocek == 1) {
                                data.permohonan_zerocek = permohonanZerocek;
                            }
                            if (data.periode == 0) {
                                if (kontrak.is_zerocek != 1 || kontrak.is_have_tld == 1) {
                                    return; // Skip rendering periode 0 separately (e.g. Rule 3 or zerocek = false)
                                }
                            }
                            arrFind = ['tld', 'lhu', 'invoice'];
                            html += this.htmlPeriode(data, kontrak, detailPengiriman, arrFind, true);
                        });
                    } else {
                        html = '<div class="text-center text-muted py-3">Tidak ada data periode</div>';
                    }
                    $(`#${this.containerId}`).html(html);
                });
            }

            htmlPeriode(data, kontrak, cekStatusPeriode, arrFind, isModal = false) {
                let aktifDokumenKirim = periodeMapDocument(data, kontrak, arrFind);
                // Gunakan fungsi isPeriodeComplete untuk mengecek status
                let isComplete = cekPeriodeComplete(data, cekStatusPeriode, kontrak, aktifDokumenKirim);
                const {
                    htmlDoc,
                    statusKirimTld,
                    statusKirimTldNext
                } = this._generateDocumentHtml(data, kontrak, cekStatusPeriode, aktifDokumenKirim);
                const {
                    htmlAction,
                    htmlInformasi
                } = this._generateActionAndInfoHtml(data, kontrak, aktifDokumenKirim, statusKirimTld,
                    statusKirimTldNext, isComplete, isModal, cekStatusPeriode);

                let textPeriode = `Periode ${data.periode}`;

                if (kontrak.is_zerocek && data.periode == 1 && kontrak.is_have_tld == 1) {
                    textPeriode += ' + Zero Check';
                } else if (data.periode == 0) {
                    textPeriode = 'Periode Zero Check';
                }

                if (data.status == 2) { // Status 2 == Pengembalian
                    textPeriode = 'Pengembalian TLD';
                }

                let htmlRangeDate = ``;
                if (data.periode != 0) {
                    let rangeDate = range_date(data.start_date, data.end_date, 1);
                    htmlRangeDate =
                        `<small class="text-secondary small fw-medium ms-1"><i class="bi bi-calendar-range text-muted me-1"></i>(${rangeDate.start} - ${rangeDate.end})</small>`;
                }

                let htmlAdendum = ``;
                if (data.adendum?.length > 0) {
                    htmlAdendum = `
                        <button class="btn btn-sm btn-outline-warning rounded-pill px-2 ms-2 fw-semibold d-inline-flex align-items-center gap-1"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseAdendum${isModal ? '-modal' : ''}-${data.periode_hash}"
                                aria-expanded="false"
                                aria-controls="collapseAdendum${isModal ? '-modal' : ''}-${data.periode_hash}">
                            <i class="bi bi-journal-text"></i>
                            <span>${data.adendum.length} Adendum</span>
                            <i class="bi bi-chevron-down small ms-1"></i>
                        </button>
                    `;
                }

                let periodeActive = kontrak.periode_active?.periode;

                if (isModal) {
                    // Tampilan ketika di dalam Info Modal
                    return `
                        <div class="card border border-light-subtle rounded-3 shadow-sm mb-1">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <!-- Column 1: Info & Dokumen (col-md-6) -->
                                    <div class="col-md-6 border-end border-light-subtle py-2">
                                        <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                                            <span class="fw-bold fs-6 text-primary-emphasis">${textPeriode}</span>
                                            ${htmlRangeDate}
                                            ${htmlAdendum}
                                        </div>
                                        <div class="row row-cols-1 row-cols-sm-2 g-2">
                                            ${htmlDoc}
                                        </div>
                                    </div>
                                    
                                    <!-- Column 2: Informasi (col-md-3) -->
                                    <div class="col-md-3 border-end border-light-subtle py-2 px-md-3 my-2 my-md-0">
                                        ${this.isPengiriman ? `<span class="text-uppercase small fw-bold text-muted d-block mb-2">Informasi</span>` : ''}
                                        <div class="d-flex flex-column gap-1" style="max-height: 100px; overflow-y: auto; padding-right: 5px;">
                                            ${htmlInformasi || '<span class="text-muted small italic"><i class="bi bi-info-circle"></i> Tidak ada info</span>'}
                                        </div>
                                    </div>
                                    
                                    <!-- Column 3: Tindakan (col-md-3) -->
                                    <div class="col-md-3 py-2 px-md-3 d-flex flex-column align-items-md-start align-items-start gap-1">
                                        <span class="text-uppercase small fw-bold text-muted d-block mb-2 w-100">Tindakan</span>
                                        <div class="d-flex flex-column w-100 gap-2">
                                            ${htmlAction || '<span class="text-muted small italic"><i class="bi bi-slash-circle"></i> Tidak ada tindakan</span>'}
                                        </div>
                                    </div>
                                </div>
                                ${this._generateAdendumCollapseHtml(data, cekStatusPeriode, isModal, periodeActive)}
                            </div>
                        </div>
                    `;
                } else {
                    // Tampilan standar untuk di list
                    return `
                        <div class="card border border-light-subtle rounded-3 shadow-sm mb-1">
                            <div class="card-body p-3 py-0">
                                <div class="row align-items-center">
                                    <!-- Column 1: Info & Dokumen (col-md-6) -->
                                    <div class="col-md-6 border-end border-light-subtle py-2">
                                        <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                                            <span class="fw-bold fs-6 text-primary-emphasis">${textPeriode}</span>
                                            ${htmlRangeDate}
                                        </div>
                                        <div class="row row-cols-1 row-cols-sm-2 g-2">
                                            ${htmlDoc}
                                        </div>
                                    </div>
                                    
                                    <!-- Column 2: Informasi (col-md-3) -->
                                    <div class="col-md-3 border-end border-light-subtle py-2 px-md-3 my-2 my-md-0">
                                        ${this.isPengiriman ? `<span class="text-uppercase small fw-bold text-muted d-block mb-2">Informasi</span>` : ''}
                                        <div class="d-flex flex-column-reverse gap-1" style="max-height: 100px; overflow-y: auto; padding-right: 5px;">
                                            ${htmlInformasi || '<span class="text-muted small italic"><i class="bi bi-info-circle"></i> Tidak ada info</span>'}
                                        </div>
                                    </div>
                                    
                                    <!-- Column 3: Tindakan (col-md-3) -->
                                    <div class="col-md-3 py-2 px-md-3 d-flex flex-column align-items-md-start align-items-start gap-1">
                                        <span class="text-uppercase small fw-bold text-muted d-block mb-2 w-100">Tindakan</span>
                                        <div class="d-flex flex-column w-100 gap-2">
                                            ${htmlAction || '<span class="text-muted small italic"><i class="bi bi-slash-circle"></i> Tidak ada tindakan</span>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }

            _generateDocumentHtml(data, kontrak, cekStatusPeriode, aktifDokumenKirim) {
                let htmlDoc = ``;
                let statusKirimTld = false;
                let statusKirimTldNext = false;

                for (const doc of aktifDokumenKirim) {
                    let findPeriode = cekStatusPeriode.find(cek => cek.periode == data.periode && cek.jenis == doc &&
                        cek.tipe_kontrak != 'adendum');
                    if (doc === 'zerocek') {
                        let zerocekLhuPeriode = kontrak.is_have_tld == 1 ? 1 : 0;
                        findPeriode = cekStatusPeriode.find(cek => cek.periode == zerocekLhuPeriode && cek.jenis ==
                            'lhu');
                    }

                    let htmlRange = '';
                    if (doc === 'tld') {
                        if (data.periode == 0) {
                            findPeriode = cekStatusPeriode.find(cek => cek.periode == 1 && cek.jenis == 'tld' &&
                                cek.tipe_kontrak != 'adendum');
                        }
                        statusKirimTld = findPeriode?.status;
                        let findPeriodeNext = cekStatusPeriode.find(cek => cek.periode == data.periode + 1 && cek
                            .jenis == 'tld' && cek.tipe_kontrak != 'adendum');
                        statusKirimTldNext = findPeriodeNext?.status;

                        if (kontrak.periode_next) {
                            let periodeNext = kontrak.periode_next[0];

                            let rangeDate = range_date(periodeNext.start_date, periodeNext.end_date, 1);
                            htmlRange =
                                `<small class="text-secondary small fw-medium ms-1">(${rangeDate.start} - ${rangeDate.end})</small>`;
                        }
                    } else {
                        // && kontrak.is_zerocek == 0 (saya hapus kondisi ini karna berpengaruh ketika di periode 2 dan TLD periode 3 sudah di kirim tapi tombol Send TLD nya tetap muncul)
                        if (data.periode == 2 && kontrak.is_have_tld == 1) {
                            let findPeriodeNext = cekStatusPeriode.find(cek => cek.periode == data.periode + 1 && cek
                                .jenis == 'tld' && cek.tipe_kontrak != 'adendum');
                            statusKirimTldNext = findPeriodeNext?.status;
                        }
                    }

                    let htmlStatusInvoice = '';
                    if (doc === 'invoice') {
                        if (kontrak.is_zerocek == 1 && kontrak.is_have_tld == 1 && data.periode == 1) {
                            findPeriode = cekStatusPeriode.find(cek => cek.periode == 1 && cek.jenis ==
                                'invoice');
                        }

                        let statusInvoice = false;
                        let invoiceObj = null;

                        if (kontrak.is_zerocek == 1 && kontrak.is_have_tld == 1 && data.periode == 1 && data
                            .permohonan_zerocek?.invoice) {
                            statusInvoice = data.permohonan_zerocek.invoice.status;
                            invoiceObj = data.permohonan_zerocek.invoice;
                        } else if (data.permohonan?.invoice) {
                            statusInvoice = data.permohonan.invoice.status;
                            invoiceObj = data.permohonan.invoice;
                        }

                        if (invoiceObj) {
                            htmlStatusInvoice = statusFormat('invoice', statusInvoice);
                            if (statusInvoice == 3 && role.includes('Pelanggan')) {
                                htmlStatusInvoice =
                                    `<a href="${base_url}/permohonan/pembayaran/bayar/${invoiceObj.keuangan_hash}">${htmlStatusInvoice}</a>`;
                            }
                        }
                    }

                    let iconClass = 'bi-file-earmark';
                    if (doc === 'invoice') iconClass = 'bi-receipt-cutoff';
                    else if (doc === 'tld') iconClass = 'bi-file-binary';
                    else if (doc === 'lhu') iconClass = 'bi-file-earmark-check';
                    else if (doc === 'zerocek') iconClass = 'bi-shield-check';

                    let docTitle = doc === 'tld' ? 'TLD' : (doc === 'lhu' ?
                        'LHU' : (doc === 'zerocek' ?
                            'LHU ZeroCheck' : doc[0].toUpperCase() + doc.substring(1)));

                    htmlDoc += `
                        <div class="col">
                            <div class=" p-2 bg-light rounded-2 border border-light-subtle ${htmlStatusInvoice || htmlRange ? '' : ' h-100'}">
                                <div class="d-flex justify-content-between align-items-center h-100">
                                    <span class="fw-medium text-dark small"><i class="bi ${iconClass} text-muted me-2"></i>${docTitle}</span>
                                    <span class="cursoron pe-2 text-end small"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="${findPeriode?.no_resi ? 'No resi : ' + findPeriode.no_resi : ''}">
                                        ${statusFormat('pengiriman', findPeriode?.status)}
                                    </span>
                                </div>
                                ${htmlRange ? `<div class="fs-7">${htmlRange}</div>` : ''}
                                ${htmlStatusInvoice ? `<div class="fs-7">${htmlStatusInvoice}</div>` : ''}
                            </div>
                        </div>
                    `;
                }

                return {
                    htmlDoc,
                    statusKirimTld,
                    statusKirimTldNext
                };
            }

            _generateActionAndInfoHtml(data, kontrak, aktifDokumenKirim, statusKirimTld, statusKirimTldNext, isComplete,
                isModal = false, cekStatusPeriode = []) {

                let htmlAction = ``;
                let htmlInformasi = ``;

                let showEvaluasi = true;
                if (data.status == 2) {
                    showEvaluasi = false;
                } else if (isModal && kontrak.periode_active && data.periode > kontrak.periode_active.periode) {
                    showEvaluasi = false;
                }

                let htmlBtnEvaluasi = ``;
                if (showEvaluasi) {
                    htmlBtnEvaluasi =
                        `<a class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs" href="${base_url}/permohonan/kontrak/e/${kontrak.kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text me-1"></i>Evaluasi</a>`;
                }

                let periodeNext = kontrak.periode.find(d => d.periode == data.periode + 1);
                let htmlBtnTld = ``;
                if (periodeNext) {
                    if (data.status == 1 || data.status == 2) { // Status 1 == Periodik
                        htmlBtnTld =
                            `<a class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs" href="${base_url}/staff/pengiriman/permohonan/kirim/${kontrak.kontrak_hash}/${periodeNext.periode_hash}"><i class="bi bi-send-fill me-1"></i>Kirim TLD</a>`;
                    }
                } else if (data.status == 2) { // Status 2 == Pengembalian
                    htmlBtnTld =
                        `<a class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs" href="${base_url}/staff/pengiriman/permohonan/kirim/${kontrak.kontrak_hash}/${data.periode_hash}"><i class="bi bi-send-fill me-1"></i>Kirim TLD</a>`;
                }

                let targetPermohonan = data.permohonan;
                let targetComplete = isComplete;

                let htmlPermohonan = ``;
                let htmlBtnSend = ``;
                if (targetPermohonan && !targetComplete) {
                    htmlPermohonan = `
                    <div class="d-flex flex-column justify-content-center align-items-start">
                        <div class="small fw-semibold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">${targetPermohonan.jenis_layanan_parent.name} - ${targetPermohonan.jenis_layanan.name}</div>
                        <div>${statusFormat('permohonan', targetPermohonan.status)}</div>
                    </div>`;

                    htmlBtnSend = `
                        <a class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs mb-2" href="${base_url}/staff/pengiriman/permohonan/kirim/${targetPermohonan.permohonan_hash}">
                            <i class="bi bi-send-fill me-1"></i>Kirim Dokumen
                        </a>
                    `;
                }

                let statusPengirimanTLD = false;
                if (periodeNext && !isModal) {
                    let findPengirimanTLD = cekStatusPeriode.find(d => d.jenis == 'tld' && d.periode == periodeNext
                        .periode);
                    htmlInformasi = `
                        <div class="d-flex flex-column text-start small">
                            <span class="text-secondary small fw-medium mb-1">TLD Periode ${periodeNext.status == 1 ? periodeNext.periode : "Pengembalian"}</span>
                            <div>${statusFormat('pengiriman', findPengirimanTLD?.status)}</div>
                        </div>
                    `;

                    statusPengirimanTLD = findPengirimanTLD?.status;
                    if (kontrak.is_zerocek == 0 && kontrak.is_have_tld == 1 && data.periode == 1) {
                        htmlInformasi = ``;
                        statusPengirimanTLD = false;
                    }

                    if (periodeNext.periode == 2 && kontrak.layanan == 'KontrakEvaluasi' && kontrak.is_have_tld == 1) {
                        htmlInformasi = ``;
                    }
                }

                if (this.isPelanggan) {
                    if (kontrak.is_zerocek == 1 && data.periode == 1) {
                        let findPeriodeZerocek = kontrak.periode.find(cek => cek.periode == 0);
                        if (findPeriodeZerocek?.status == 1 && targetPermohonan == null) {
                            htmlAction += htmlBtnEvaluasi;
                        } else {
                            htmlInformasi += htmlPermohonan;
                        }
                    } else {
                        if (!targetPermohonan && statusPengirimanTLD == 2) {
                            htmlAction += htmlBtnEvaluasi;
                        } else {
                            htmlInformasi += htmlPermohonan;
                        }
                    }
                } else {
                    let htmlStatusPenyelia = '';
                    let tldSelesai = false;
                    let penyelia2 = false;
                    if (periodeNext) {
                        penyelia2 = kontrak.periode.find(cek => cek.periode == periodeNext.periode - 2 && cek.periode !=
                            0);
                        if (penyelia2) {
                            tldSelesai = cekPenyelia(penyelia2?.penyelia, 'Pelabelan TLD');
                            if (penyelia2.penyelia && !tldSelesai) {
                                htmlStatusPenyelia = `
                                    <div class="d-flex flex-column gap-1 align-items-start">
                                        <span class="text-secondary small fw-medium" style="font-size: 0.75rem;">Penyelia Periode ${penyelia2.periode}</span>
                                        <div class="badge bg-warning-subtle fw-normal rounded-pill text-warning-emphasis">
                                            Proses Belum Selesai
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            tldSelesai = true;
                        }
                    } else {
                        tldSelesai = false;
                    }

                    if (aktifDokumenKirim.includes('tld')) {
                        if (statusKirimTld == 2) {
                            if (tldSelesai) {
                                if (!statusKirimTldNext && periodeNext) {
                                    htmlAction = `
                                        <div class="d-flex flex-column text-start gap-1">
                                            <span class="text-secondary small fw-medium" style="font-size: 0.75rem;">TLD Periode ${periodeNext.status == 1 ? periodeNext.periode : "Pengembalian"}</span>
                                            <div>${htmlBtnTld}</div>
                                        </div>
                                    `;
                                    htmlInformasi = '';
                                }
                            } else {
                                if (periodeNext) {
                                    htmlAction = `
                                        <div class="d-flex flex-column text-start gap-1">
                                            <span class="text-secondary small fw-medium" style="font-size: 0.75rem;">TLD Periode ${periodeNext.status == 1 ? periodeNext.periode : "Pengembalian"}</span>
                                            <div>${htmlStatusPenyelia}</div>
                                        </div>
                                    `;
                                }
                            }
                        } else if (data.status == 2) {
                            if (tldSelesai) {
                                htmlAction = `
                                    <div class="d-flex flex-column text-start gap-1">
                                        <span class="text-secondary small fw-medium" style="font-size: 0.75rem;">TLD Periode Pengembalian</span>
                                        <div>${htmlBtnTld}</div>
                                    </div>
                                `;
                            }
                        }
                    } else {
                        // if (data.periode == 2 && kontrak.is_zerocek == 0 && kontrak.is_have_tld == 1) {
                        if (data.periode == 2 && kontrak.layanan == 'KontrakEvaluasi') {
                            if (!statusKirimTldNext && periodeNext) {
                                if (tldSelesai) {
                                    htmlAction = `
                                        <div class="d-flex flex-column text-start gap-1">
                                            <span class="text-secondary small fw-medium" style="font-size: 0.75rem;">TLD Periode ${periodeNext.status == 1 ? periodeNext.periode : "Pengembalian"}</span>
                                            <div>${htmlBtnTld}</div>
                                        </div>
                                    `;
                                } else {
                                    if (periodeNext) {
                                        htmlAction = `
                                        <div class="d-flex flex-column text-start gap-1">
                                            <span class="text-secondary small fw-medium" style="font-size: 0.75rem;">TLD Periode ${periodeNext.status == 1 ? periodeNext.periode : "Pengembalian"}</span>
                                            <div>${htmlStatusPenyelia}</div>
                                        </div>
                                    `;
                                    }
                                }

                                htmlInformasi = '';
                            }
                        }
                    }

                    if (targetPermohonan) {
                        htmlInformasi += htmlPermohonan;
                        if (targetPermohonan.status == 1) {
                            htmlBtnSend = ``;
                        }
                    }

                    if (this.isPengiriman) {
                        htmlAction = htmlBtnSend + htmlAction;
                    }

                    if (targetPermohonan?.lhu) {
                        let aktifJobs = targetPermohonan.lhu.penyelia_map.filter(d => d.status == 1);
                        htmlInformasi += '<div class="d-inline-flex flex-column gap-1 align-items-start">';
                        htmlInformasi +=
                            `<span class="fw-semibold text-dark small mb-1"><i class="bi bi-info-circle text-primary me-1"></i>Informasi LAB</span>`;
                        aktifJobs.map(d => {
                            htmlInformasi += statusFormat('penyelia', d.jobs.status);
                        });
                        if (aktifJobs.length == 0) {
                            htmlInformasi += statusFormat('penyelia', targetPermohonan.lhu.status);
                        }
                        htmlInformasi += '</div>';
                    }
                }

                return {
                    htmlAction,
                    htmlInformasi
                };
            }

            _generateAdendumCollapseHtml(data, cekStatusPeriode = [], isModal = false, periodeActive = null) {
                if (!data.adendum || data.adendum.length === 0) return '';

                let html = `
                    <div class="collapse mt-3 border-top pt-3" id="collapseAdendum${isModal ? '-modal' : ''}-${data.periode_hash}">
                        <div class="d-flex flex-column gap-2">
                `;

                data.adendum.forEach((adendum, index) => {
                    let jmlPergantian = adendum.permohonan_detail?.filter(detail => detail.type === 'ganti')
                        .length ?? 0;
                    let jmlPenambahan = adendum.permohonan_detail?.filter(detail => detail.type === 'baru')
                        .length ?? 0;

                    // Cari pengiriman LHU adendum
                    let findLhuAdendum = cekStatusPeriode.find(cek =>
                        cek.permohonan_hash === adendum.permohonan_hash &&
                        cek.jenis === 'lhu'
                    );

                    // Cari pengiriman Invoice adendum (hanya jika ada penambahan)
                    let findInvoiceAdendum = jmlPenambahan > 0 ? cekStatusPeriode.find(cek =>
                        cek.permohonan_hash === adendum.permohonan_hash &&
                        cek.jenis === 'invoice'
                    ) : null;

                    // Cari pengiriman TLD adendum
                    let findTldAdendum = cekStatusPeriode.find(cek =>
                        cek.permohonan_hash === adendum.permohonan_hash &&
                        cek.jenis === 'tld'
                    ) ?? null;

                    let htmlInvoice = '';
                    let htmlTld = '';
                    let htmlLhu = '';
                    if (jmlPenambahan > 0) {
                        let statusInvoice = findInvoiceAdendum ? findInvoiceAdendum.status : 0;
                        let textStatusInvoice = statusFormat('pengiriman', statusInvoice);
                        let statusInv = '';
                        if (adendum.invoice) {
                            statusInv = statusFormat('invoice', adendum.invoice.status);
                            if (adendum.invoice.status == 3 && role.includes('Pelanggan')) {
                                statusInv =
                                    `<a href="${base_url}/permohonan/pembayaran/bayar/${adendum.invoice.keuangan_hash}">${statusInv}</a>`;
                            }
                        }
                        htmlInvoice = `
                            <div class="col-3">
                                <div class="p-2 bg-light rounded-2 border border-light-subtle h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium text-dark small"><i class="bi bi-receipt-cutoff text-muted me-2"></i>Invoice</span>
                                        <span class="small">${textStatusInvoice}</span>
                                    </div>
                                    ${statusInv}
                                </div>
                            </div>
                        `;

                    }
                    if (adendum.is_periode_berjalan) {
                        let statusTld = findTldAdendum ? findTldAdendum.status : 0;
                        let textStatusTld = statusFormat('pengiriman', statusTld);

                        htmlTld = `
                        <div class="col-3">
                            <div class="p-2 bg-light rounded-2 border border-light-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium text-dark small"><i class="bi bi-cpu text-muted me-2"></i>TLD</span>
                                    <span class="small">${textStatusTld}</span>
                                </div>
                            </div>
                        </div>
                        `;
                    }

                    if (adendum.is_zerocek == 1) {
                        let statusLhu = findLhuAdendum ? findLhuAdendum.status : 0;
                        htmlLhu = `
                            <div class="col-3">
                                <div class="p-2 bg-light rounded-2 border border-light-subtle h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium text-dark small"><i class="bi bi-file-earmark-check text-muted me-2"></i>LHU</span>
                                        <span class="small">${statusFormat('pengiriman', statusLhu)}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    let periodeNow = adendum.periodenow;
                    let bulan_mulai = adendum.bulan_mulai;

                    // Mengambil nama bulan ke-2 dari range date periodeNow (adendum) dan data (periode)
                    let mulai_adendum = '';
                    if (periodeNow) {
                        mulai_adendum = findDate(periodeNow.start_date, bulan_mulai);
                    }

                    html += `
                        <div class="p-3 bg-warning bg-opacity-10 border border-warning-subtle rounded-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-file-earmark-text-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">Adendum #${index + 1}</h6>
                                        <small class="text-secondary"><i class="bi bi-calendar-event me-1"></i>${mulai_adendum}</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="d-flex gap-1">
                                        <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle fw-normal">
                                            Ganti: ${jmlPergantian}
                                        </span>
                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-normal">
                                            Baru: ${jmlPenambahan}
                                        </span>
                                    </div>
                                    <div class="fw-bold text-primary-emphasis">${formatRupiah(adendum.total_harga)}</div>
                                    <a class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-xs d-flex align-items-center gap-1"
                                        target="_blank" href="${base_url}/laporan/adendum/${adendum.permohonan_hash}">
                                        <i class="bi bi-file-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-4 mt-2 border-top pt-2 flex-wrap">
                                ${htmlInvoice}
                                ${htmlLhu}
                                ${htmlTld}
                            </div>
                        </div>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;
                return html;
            }
        }
    </script>
@endpush
