<div class="modal fade" id="modalPeriode" tabindex="-1" aria-labelledby="modalPeriodeLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalPeriodeLabel">Daftar Periode</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="listPeriodeContainer" class="d-flex flex-column gap-3">
                    <!-- List periode akan di-render di sini -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
        }

        show(id_kontrak) {
            let skeleton = '';
            for (let i = 0; i < 3; i++) {
                skeleton += `
                        <div class="border-top py-2 d-flex justify-content-start align-items-center placeholder-glow">
                            <div class="px-2 col-6 border-end">
                                <span class="placeholder col-4 mb-2"></span>
                                <div class="row row-cols-2 g-1 mt-1">
                                    <div class="col"><span class="placeholder col-8"></span></div>
                                    <div class="col"><span class="placeholder col-8"></span></div>
                                </div>
                            </div>
                            <div class="col-3 ms-3">
                                <span class="placeholder col-10"></span>
                            </div>
                            <div class="d-flex align-items-end gap-1 text-secondary flex-column ms-auto col-2">
                                <span class="placeholder col-12 mb-1"></span>
                                <span class="placeholder col-8"></span>
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
                                periode: d.periode ? d.periode : (pengiriman.periode ? pengiriman.periode : 0),
                                status: pengiriman.status,
                                no_resi: pengiriman.no_resi ?? false,
                                tipe_kontrak: pengiriman.permohonan ? pengiriman.permohonan.tipe_kontrak : false
                            }));
                        }
                    }
                    kontrak.periode.forEach(data => {
                        arrFind = ['invoice', 'tld', 'lhu'];
                        if (data.periode == 0) {
                            // menghilangkan tld di arrfind
                            arrFind = arrFind.filter(d => d != 'tld');
                        }
                        html += this.htmlPeriode(data, kontrak, detailPengiriman, arrFind, true);
                    });
                } else {
                    html = '<div class="text-center text-muted py-3">Tidak ada data periode</div>';
                }
                $(`#${this.containerId}`).html(html);
            })
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
            } = this._generateActionAndInfoHtml(data, kontrak, aktifDokumenKirim, statusKirimTld, statusKirimTldNext, isComplete);

            let textPeriode = `Periode ${data.periode}`;

            if (kontrak.is_zerocek && data.periode == 1) {
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
                htmlRangeDate = `<small class="text-body-tertiary"> - (${rangeDate.start} - ${rangeDate.end})</small>`;
            }

            let htmlAdendum = ``;
            if (data.adendum?.length > 0) {
                htmlAdendum = `<small class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" data-id="${data.periode_hash}" data-periode="${data.periode}" onclick="showAdendumInformasi(this)">${data.adendum.length} Adendum</small>`;
            }

            if (isModal) {
                // Tampilan ketika di dalam Info Modal
                return `
                        <div class="p-1 bg-light border border-secondary-subtle rounded-3 d-flex justify-content-start align-items-center mb-1 shadow-sm">
                            <div class="px-2 col-6 border-end border-secondary-subtle">
                                <span class="fw-semibold fs-6 text-primary">${textPeriode}</span>
                                ${htmlRangeDate} ${htmlAdendum}
                                <div class="row row-cols-2 g-2">
                                    ${htmlDoc}
                                </div>
                            </div>
                            <div class="col-3 ms-3">
                                ${htmlInformasi}
                            </div>
                            <div class="d-flex align-items-center gap-2 text-secondary flex-column ms-auto">
                                ${htmlAction}
                            </div>
                        </div>
                    `;
            } else {
                // Tampilan standar untuk di list
                return `
                        <div class="border-top py-2 d-flex justify-content-start align-items-center">
                            <div class="px-2 col-6 border-end">
                                <span class="fw-semibold fs-6">${textPeriode}</span>
                                ${htmlRangeDate} ${htmlAdendum}
                                <div class="row row-cols-2 g-1">
                                    ${htmlDoc}
                                </div>
                            </div>
                            <div class="col-3 ms-3">
                                ${htmlInformasi}
                            </div>
                            <div class="d-flex align-items-center gap-1 text-secondary flex-column ms-auto">
                                ${htmlAction}
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
                let findPeriode = cekStatusPeriode.find(cek => cek.periode == data.periode && cek.jenis == doc && cek.tipe_kontrak != 'adendum');

                if (doc === 'zerocek') {
                    findPeriode = cekStatusPeriode.find(cek => cek.periode == 0 && cek.jenis == 'lhu');
                }

                if (doc === 'tld') {
                    statusKirimTld = findPeriode?.status;
                    let findPeriodeNext = cekStatusPeriode.find(cek => cek.periode == data.periode + 1 && cek.jenis == 'tld' && cek.tipe_kontrak != 'adendum');
                    statusKirimTldNext = findPeriodeNext?.status;
                }

                let htmlStatusInvoice = '';
                if (doc === 'invoice') {
                    if (kontrak.is_zerocek == 1 && data.periode == 1) {
                        findPeriode = cekStatusPeriode.find(cek => cek.periode == 0 && cek.jenis == 'invoice');
                    }

                    if (data.permohonan) {
                        let statusInvoice = false;
                        if (kontrak.is_zerocek == 1 && data.periode == 1 && data.permohonan_zerocek) {
                            statusInvoice = data.permohonan_zerocek.invoice.status;
                        } else {
                            statusInvoice = data.permohonan.invoice.status;
                        }
                        htmlStatusInvoice = statusFormat('invoice', statusInvoice);
                        if (statusInvoice == 3 && role.includes('Pelanggan')) {
                            htmlStatusInvoice = `<a href="${base_url}/permohonan/pembayaran/bayar/${data.permohonan.invoice.keuangan_hash}">${htmlStatusInvoice}</a>`;
                        }
                    }
                }

                htmlDoc += `
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-normal">• ${doc[0].toUpperCase() + doc.substring(1)}</span>
                                <span class="cursoron pe-2 text-end small"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="${findPeriode?.no_resi ? 'No resi : ' + findPeriode.no_resi : ''}">
                                    ${statusFormat('pengiriman', findPeriode?.status)}
                                </span>
                            </div>
                            <div class="small ms-2">
                                ${htmlStatusInvoice}
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

        _generateActionAndInfoHtml(data, kontrak, aktifDokumenKirim, statusKirimTld, statusKirimTldNext, isComplete) {
            const isPelanggan = role.includes('Pelanggan');
            const isPengiriman = role.includes('Staff Pengiriman');
            let htmlAction = ``;
            let htmlInformasi = ``;

            let htmlBtnEvaluasi = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/permohonan/kontrak/e/${kontrak.kontrak_hash}/${data.periode_hash}"><i class="bi bi-file-earmark-text"></i> Evaluasi</a>`;

            let periodeNext = kontrak.periode.find(d => d.periode == data.periode + 1);
            let htmlBtnTld = ``;
            if (periodeNext) {
                htmlBtnTld = `<a class="btn btn-sm btn-outline-primary" href="${base_url}/staff/pengiriman/permohonan/kirim/${kontrak.kontrak_hash}/${periodeNext.periode_hash}"><i class="bi bi-send-fill"></i> Kirim TLD</a>`;
            }

            let htmlPermohonan = ``;
            let htmlBtnSend = ``;
            if (data.permohonan && !isComplete) {
                htmlPermohonan = `
                    <div class="d-flex flex-column justify-content-center align-items-end">
                        <div class="fs-8">${data.permohonan.jenis_layanan_parent.name} - ${data.permohonan.jenis_layanan.name}</div>
                        <div>${statusFormat('permohonan', data.permohonan.status)}</div>
                    </div>`;

                htmlBtnSend = `
                        <a class="btn btn-outline-primary btn-sm" href="${base_url}/staff/pengiriman/permohonan/kirim/${data.permohonan.permohonan_hash}">
                            <i class="bi bi-send-fill"></i> Kirim Dokumen
                        </a>
                    `;
            }

            if (isPelanggan) {
                if (periodeNext) {
                    htmlInformasi = `
                            <div class="d-flex flex-column text-center small">
                                <div>Tld periode ${periodeNext.periode}</div>
                                <div>${statusFormat('pengiriman', statusKirimTldNext)}</div>
                            </div>
                        `;
                }

                if (kontrak.is_zerocek == 1 && data.periode == 1) {
                    let findPeriodeZerocek = kontrak.periode.find(cek => cek.periode == 0);
                    if (findPeriodeZerocek?.status == 1) {
                        htmlAction += htmlBtnEvaluasi;
                    } else {
                        htmlAction += htmlPermohonan;
                    }
                } else {
                    if (kontrak.no_kontrak == 'S-0003/JKRL/V/2026') {
                        // console.log(kontrak);
                    }
                    if (!data.permohonan) {
                        htmlAction += htmlBtnEvaluasi;
                    } else {
                        htmlAction += htmlPermohonan;
                    }
                }
            } else {
                let htmlStatusPenyelia = '';
                let tldSelesai = false;
                let penyelia2 = false;
                if (periodeNext) {
                    penyelia2 = kontrak.periode.find(cek => cek.periode == periodeNext.periode - 2 && cek.periode != 0);
                }

                if (penyelia2) {
                    tldSelesai = cekPenyelia(penyelia2?.penyelia, 'Pelabelan TLD');
                    if (penyelia2.penyelia && !tldSelesai) {
                        htmlStatusPenyelia = `
                                <div class="d-flex flex-column gap-1">
                                    <span>Penyelia periode ${penyelia2.periode}</span>
                                    <div class="badge bg-warning-subtle fw-normal rounded-pill text-warning-emphasis">
                                        Proses Belum Selesai
                                    </div>
                                </div>
                            `;
                    }
                } else {
                    tldSelesai = true;
                }

                if (aktifDokumenKirim.includes('tld')) {
                    if (statusKirimTld == 2) {
                        if (tldSelesai) {
                            let actionNext = '';
                            if (!statusKirimTldNext) {
                                actionNext = htmlBtnTld;
                            } else {
                                actionNext = statusFormat('pengiriman', statusKirimTldNext);
                            }

                            if (periodeNext) {
                                htmlAction = `
                                        <div class="d-flex flex-column text-center">
                                            <div>Tld periode ${periodeNext.periode}</div>
                                            <div>${actionNext}</div>
                                        </div>
                                    `;
                            }
                        } else {
                            if (periodeNext) {
                                htmlAction = `
                                        <div class="d-flex flex-column text-center small">
                                            <div>Tld periode ${periodeNext.periode}</div>
                                            <div>${htmlStatusPenyelia}</div>
                                        </div>
                                    `;
                            }
                        }
                    }
                }

                if (isPengiriman) {
                    htmlAction = htmlBtnSend + htmlAction;
                }

                if (data.permohonan?.lhu) {
                    let aktifJobs = data.permohonan.lhu.penyelia_map.filter(d => d.status == 1);
                    htmlInformasi += '<div class="d-inline-flex flex-column gap-1">';
                    htmlInformasi += `<span class="fw-semibold">Informasi LAB</span>`;
                    aktifJobs.map(d => {
                        htmlInformasi += statusFormat('penyelia', d.jobs.status);
                    });
                    if (aktifJobs.length == 0) {
                        htmlInformasi += statusFormat('penyelia', data.permohonan.lhu.status);
                    }
                    htmlInformasi += '</div>';
                }
            }

            return {
                htmlAction,
                htmlInformasi
            };
        }
    }
</script>
@endpush