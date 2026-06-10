<div class="modal fade" id="updateProgressModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="invoiceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="updateProgressModalLabel">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-bar-chart-steps text-primary fs-5"></i>
                    </div>
                    <span>Update Progress</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                            <label class="fw-bold text-secondary">Tanggal</label>
                            <div>
                                <input type="text" class="form-control" id="dateProgress">
                            </div>
                        </div>
                        <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                            <label for="" class="fw-bold">Status</label>
                            <div>
                                <div class="form-check form-check-inline" id="divReturnProgress">
                                    <input class="form-check-input" type="radio" name="statusProgress"
                                        id="statusReturn" value="return">
                                    <label class="form-check-label text-danger" for="statusReturn">Return</label>
                                </div>
                                <div class="form-check form-check-inline" id="divDoneProgress">
                                    <input class="form-check-input" type="radio" name="statusProgress" id="statusDone"
                                        value="done" checked>
                                    <label class="form-check-label text-success" for="statusDone">Done</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold text-secondary mb-2">Progress</label>
                            <div class="d-flex align-items-center">
                                <select name="prosesNow" id="prosesNow" class="form-select shadow-none">
                                    <option value="">Pilih proses</option>
                                </select>
                                <span class="mx-3 text-muted"><i class="bi bi-arrow-right"></i></span>
                                <input type="text" class="form-control bg-light border-0 fw-semibold"
                                    name="prosesNext" id="prosesNext" readonly>
                            </div>
                        </div>
                        <div class="col-sm-12 mb-3">
                            <label for="inputNote" class="fw-bold text-secondary">Note<span
                                    class="text-danger ms-1">*</span></label>
                            <textarea name="inputNote" id="inputNote" cols="30" rows="5" class="form-control"></textarea>
                        </div>
                        <div id="divUploadDocLhu">
                            <label for="upload_document" class="col-form-label fw-bold text-secondary">Upload Document
                                LHU<span class="text-danger ms-1">*</span></label>
                            <div id="upload_document"></div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="fw-bold text-secondary mb-2">Rincian TLD</label>
                        <div id="detailTld" class="overflow-auto pe-2" style="max-height: 28rem;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light fw-semibold px-4 py-2"
                    data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary fw-semibold px-4 py-2" onclick="simpanProgress(this)">Update</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        class ProgressModal {
            constructor() {
                this.nowSelect = false;
                this.documentLhu = false;
                this.initEventListeners();
            }

            initEventListeners() {
                $('#updateProgressModal').on('hide.bs.modal', () => {
                    this.nowSelect = false;
                });

                $(`[name="statusProgress"]`).on('click', obj => {
                    if (!this.nowSelect) return;
                    if (obj.target.value == 'return') {
                        $('#divUploadDocLhu').hide();
                        $('#prosesNext').val(this.nowSelect.prosesPrev.jobs.name);
                    } else {
                        this.nowSelect.prosesNow.jobs.upload_doc ? $('#divUploadDocLhu').show() : $(
                            '#divUploadDocLhu').hide();
                        $('#prosesNext').val(this.nowSelect.prosesNext?.jobs?.name ?? "Finish");
                    }
                });

                $('#prosesNow').on('change', obj => {
                    if (!this.nowSelect) return;
                    const prosesNow = this.nowSelect.penyelia_map.find(d => d.map_hash == obj.target.value);
                    this.setProses(prosesNow);
                });
            }

            /**
             * Open modal for updating progress of penyelia
             * If obj is passed, it will get the idPenyelia from the closest tr element
             * If idPenyelia is passed, it will get the data from the API by the idPenyelia
             * @param {object|boolean} obj - The element that triggered the modal
             * @param {string|boolean} idPenyelia - The id of the penyelia
             */
            open(obj = false, idPenyelia = false) {
                if (obj) {
                    const index = $(obj).closest('tr').data("index") ?? $(obj).parent().parent().data("index");
                    idPenyelia = dataPenyelia[index].penyelia_hash;
                }

                ajaxGet(`api/v1/penyelia/getById/${idPenyelia}`, false, result => {
                    this.nowSelect = result.data ?? false;
                    if (!this.nowSelect) return;

                    $('#statusDone').prop('checked', true);

                    this.renderJobsSelection(this.nowSelect);
                    this.initDatePicker(this.nowSelect);
                    this.renderTldList(this.nowSelect);
                    this.initUploadDocument(this.nowSelect);

                    $('#inputNote').val('');
                });
            }

            /**
             * Render list of jobs selection based on the data provided
             * The jobs are filtered based on the jobs that are currently assigned to the user
             * The first job in the list is selected by default
             * @param {object} data - The penyelia data
             */
            renderJobsSelection(data) {
                let listJobsAktif = data.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 1);

                // Urutkan pekerjaan: yang tidak memiliki point_jobs di atas, yang memiliki di bawah,
                // lalu urutkan berdasarkan 'order' di dalam masing-masing grup.
                listJobsAktif.sort((a, b) => (!!a.point_jobs - !!b.point_jobs) || (a.order - b.order));

                // Filter jobs assigned to current user
                const userJobs = listJobsAktif.filter(d =>
                    data.petugas.some(p => p.map_hash == d.map_hash && p.user_hash == userActive.user_hash)
                );

                if (userJobs.length > 0) {
                    this.setProses(userJobs[0], data.periode_used);
                    $('#updateProgressModal').modal('show');
                }

                const htmlJobs = userJobs.map((d, index) =>
                    `<option value="${d.map_hash}" ${index === 0 ? 'selected' : ''}>${d.jobs.name}</option>`
                ).join('');

                $('#prosesNow').html(htmlJobs);

            }

            /**
             * Initialize date picker for progress date input
             * The date picker is limited to the start date and end date of the current penyelia
             * The default date is set to today
             * @param {object} data - The penyelia data
             */
            initDatePicker(data) {
                $('#dateProgress').flatpickr({
                    altInput: true,
                    locale: "id",
                    dateFormat: "Y-m-d",
                    altFormat: "j F Y",
                    minDate: data.start_date,
                    maxDate: data.end_date,
                    defaultDate: 'today'
                });
            }

            /**
             * Render list of TLDs based on the data provided
             * The TLDs are filtered based on the jobs that are currently assigned to the user
             * The first TLD in the list is selected by default
             * @param {object} data - The penyelia data
             */
            renderTldList(data) {
                let htmlRincianTld = '';
                let isPeriodOne = data.permohonan.periodenow.count_tld == 1 || data.permohonan.periodenow.periode == 0;
                let periodenow = data.permohonan.periodenow.periode == 0 ? 1 : data.permohonan.periodenow.periode;
                let tipe_kontrak = data.permohonan.tipe_kontrak;
                let details = [];

                if (tipe_kontrak == "adendum") {
                    details = data.permohonan.permohonan_detail;
                } else {
                    details = data.permohonan.kontrak.kontrak_detail.filter(d => {
                        return isPeriodOne ? d.periode_tld_1 == periodenow : d.periode_tld_2 == periodenow;
                    });
                }

                details.forEach(detail => {
                    let tld = false;
                    let status = false;
                    if (tipe_kontrak == 'adendum') {
                        tld = detail.tld;
                        status = detail.status;
                    } else {
                        tld = isPeriodOne ? detail.tld_1 : detail.tld_2;
                        status = isPeriodOne ? detail.status_tld_1 : detail.status_tld_2;
                    }
                    if (!tld) return;

                    const inPenyimpanan = status == 5;

                    const cardHtml = `
                        <div class="bg-light p-3 rounded-4 mb-2 d-flex justify-content-between align-items-center border border-white">
                            <span class="fw-semibold text-dark" style="font-size: 0.9rem;">${tld.no_seri_tld}</span>
                            <span class="badge ${inPenyimpanan ? 'bg-secondary' : 'bg-success'} bg-opacity-10 text-${inPenyimpanan ? 'secondary' : 'success'} border-0 px-3 py-2" style="border-radius: 0.5rem; font-size: 0.75rem;">
                                ${inPenyimpanan ? 'Penyimpanan' : 'Aktif'}
                            </span>
                        </div>
                    `;

                    if (!detail.pengguna) {
                        htmlRincianTld = cardHtml + htmlRincianTld;
                    } else {
                        htmlRincianTld += cardHtml;
                    }
                });

                if (!htmlRincianTld) {
                    htmlRincianTld = `
                        <div class="text-center py-4 bg-light rounded-4 border border-dashed">
                            <span class="text-muted italic">Tidak ada TLD</span>
                        </div>
                    `;
                }

                $('#detailTld').html(htmlRincianTld);
            }

            /**
             * Initialize the upload document component
             * @param {object} data - The penyelia data
             */
            initUploadDocument(data) {
                if (this.documentLhu) {
                    this.documentLhu.destroy();
                    this.documentLhu = false;
                }

                this.documentLhu = new UploadComponent('upload_document', {
                    camera: false,
                    allowedFileExtensions: ['pdf'],
                    multiple: true,
                    urlUpload: {
                        url: `api/v1/penyelia/uploadDokumenLhu`,
                        urlDestroy: `api/v1/penyelia/destroyDokumenLhu`,
                        idHash: data.penyelia_hash
                    }
                });

                if (data.media && data.media.length > 0) {
                    this.documentLhu.setData(data.media);
                }
            }

            setProses(prosesNow, periode_used = false) {
                let prosesNext = false;
                let prosesPrev = false;
                if (!prosesNow.point_jobs) {
                    prosesPrev = this.nowSelect.penyelia_map.find(d => d.order == (prosesNow.order - 1));
                    prosesNext = this.nowSelect.penyelia_map.find(d => d.order == (prosesNow.order + 1));
                } else {
                    prosesPrev = this.nowSelect.penyelia_map.find(d => d.order == (prosesNow.order - 1) && d
                        .point_jobs);
                    prosesNext = this.nowSelect.penyelia_map.find(d => d.order == (prosesNow.order + 1) && d
                        .point_jobs);
                }

                !prosesPrev ? $('#divReturnProgress').hide() : null;
                prosesNow.jobs.upload_doc ? $('#divUploadDocLhu').show() : $('#divUploadDocLhu').hide();

                this.nowSelect.prosesNow = prosesNow;
                this.nowSelect.prosesPrev = prosesPrev;

                if (!periode_used && prosesNow.jobs.status == 17) {
                    this.nowSelect.prosesNext = false;

                    $('#prosesNext').val('Selesai (Tersimpan)');
                } else {
                    this.nowSelect.prosesNext = prosesNext;

                    $('#prosesNext').val(prosesNext?.jobs?.name ?? "Finish");
                }
            }

            save(obj) {
                let note = $('#inputNote').val();
                let sProgress = $(`[name="statusProgress"]:checked`).val();
                let nextJobs = sProgress == 'done' ? (this.nowSelect?.prosesNext?.map_hash ?? 3) : this.nowSelect
                    ?.prosesPrev?.map_hash;
                let nowJobs = this.nowSelect?.prosesNow?.map_hash;

                if (note == '') {
                    return Swal.fire({
                        icon: "warning",
                        text: 'Tolong masukan note!',
                    });
                }

                if (this.nowSelect?.prosesNow.jobs.upload_doc && sProgress == 'done') {
                    const document = this.documentLhu.getData();
                    if (document.length == 0) {
                        return Swal.fire({
                            icon: "warning",
                            text: 'Tolong upload dokumen!',
                        });
                    }
                }
                const form = new FormData();
                form.append('idPenyelia', this.nowSelect?.penyelia_hash);
                form.append('nextJobs', nextJobs);
                form.append('nowJobs', nowJobs);
                form.append('periodeNow', this.nowSelect.periodenow?.periode_hash);
                form.append('note', note);
                form.append('sProgress', sProgress);

                spinner('show', $(obj));
                ajaxPost(`api/v1/penyelia/actionJobProses`, form, result => {
                    spinner('hide', $(obj));
                    if (result.meta.code == 200) {
                        Swal.fire({
                            icon: "success",
                            text: 'Progress berhasil diupdate',
                        });
                        $('#updateProgressModal').modal('hide');
                        loadData();
                    } else {
                        Swal.fire({
                            icon: "error",
                            text: result.data.msg,
                        });
                    }
                }, error => {
                    console.log(error);
                    Swal.fire({
                        icon: "warning",
                        text: error.responseJSON.data.msg,
                    });
                    spinner('hide', $(obj));
                }, {
                    onErrorPopup: false
                });
            }
        }

        window.ProgressPenyelia = new ProgressModal();

        function openProgressModal(obj = false, idPenyelia = false) {
            window.ProgressPenyelia.open(obj, idPenyelia);
        }

        function simpanProgress(obj) {
            window.ProgressPenyelia.save(obj);
        }
    </script>
@endpush
