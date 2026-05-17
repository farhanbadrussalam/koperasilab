<div class="modal fade" id="updateProgressKolektifModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="updateProgressKolektifModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="updateProgressKolektifModalLabel">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-ui-checks-grid text-primary fs-5"></i>
                    </div>
                    <span>Update Progress Kolektif</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 bg-info bg-opacity-10 d-flex align-items-center mb-4">
                    <i class="bi bi-info-circle-fill fs-4 text-info me-3"></i>
                    <div>
                        <div class="fw-bold text-info-emphasis">Memperbarui <span id="countSelectedModal" class="badge bg-info text-white mx-1">0</span> data sekaligus</div>
                        <div id="jobCountsInfo" class="mt-1 mb-2 d-flex flex-wrap gap-2"></div>
                        <small class="text-info-emphasis">Pastikan semua data yang dipilih akan diupdate ke status dan proses yang sama.</small>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                            <label class="fw-bold text-secondary">Tanggal</label>
                            <div>
                                <input type="text" class="form-control" id="dateProgressKolektif">
                            </div>
                        </div>
                        <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                            <label for="" class="fw-bold text-secondary">Status</label>
                            <div>
                                <div class="form-check form-check-inline" id="divReturnProgressKolektif">
                                    <input class="form-check-input" type="radio" name="statusProgressKolektif" id="statusReturnKolektif" value="return">
                                    <label class="form-check-label text-danger" for="statusReturnKolektif">Return</label>
                                </div>
                                <div class="form-check form-check-inline" id="divDoneProgressKolektif">
                                    <input class="form-check-input" type="radio" name="statusProgressKolektif" id="statusDoneKolektif" value="done" checked>
                                    <label class="form-check-label text-success" for="statusDoneKolektif">Done</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold text-secondary mb-2">Proses</label>
                            <div class="d-flex align-items-center">
                                <select name="prosesNowKolektif" id="prosesNowKolektif" class="form-select shadow-none">
                                    <option value="">Pilih proses</option>
                                </select>
                                <span class="mx-3 text-muted"><i class="bi bi-arrow-right"></i></span>
                                <input type="text" class="form-control bg-light border-0 fw-semibold" name="prosesNextKolektif" id="prosesNextKolektif" placeholder="Next Step" value="Proses Selanjutnya" readonly>
                            </div>
                        </div>
                        <div class="col-sm-12 mb-3">
                            <label for="inputNoteKolektif" class="fw-bold text-secondary">Note<span class="text-danger ms-1">*</span></label>
                            <textarea name="inputNoteKolektif" id="inputNoteKolektif" cols="30" rows="4" class="form-control" placeholder="Tambahkan catatan untuk semua data terpilih..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="fw-bold text-secondary mb-2">Data Terpilih</label>
                        <div id="detailSelectedLhu" class="overflow-auto pe-2" style="max-height: 25rem;">
                            <!-- Data akan dimuat melalui JS -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light fw-semibold px-4 py-2" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary fw-semibold px-4 py-2" onclick="simpanProgressKolektif(this)">Update Semua</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    class ProgressKolektifModal {
        constructor() {
            this.selectedData = [];
            this.initEventListeners();
        }

        initEventListeners() {
            $(`[name="statusProgressKolektif"]`).on('click', obj => {
                // Dummy logic for UI stage 1
                if (obj.target.value == 'return') {
                    $('#prosesNextKolektif').val("Proses Sebelumnya");
                } else {
                    $('#prosesNextKolektif').val("Proses Selanjutnya");
                }
            });
            
            $('#prosesNowKolektif').on('change', obj => {
                this.selectedData.forEach((data, index) => {
                    data.penyelia_map.forEach((map, index) => {
                        if (map.jobs_hash == obj.target.value) {
                            if(map.order == 1) {
                                $('#divReturnProgressKolektif').hide();
                                $('#statusReturnKolektif').prop('checked', false);
                            } else {
                                $('#divReturnProgressKolektif').show();
                            }
                        }
                    });
                })
            });
        }

        open() {
            if(selectedItems.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Pilih minimal satu data untuk diupdate'
                });
                return;
            }

            this.selectedData = [];
            // Retrieve selected rows using global selectedItems
            selectedItems.forEach(id => {
                if(selectedDataMap && selectedDataMap[id]) {
                    this.selectedData.push(selectedDataMap[id]);
                }
            });

            $('#countSelectedModal').text(this.selectedData.length);
            
            this.renderSelectedList();
            this.initDatePicker();
            this.renderJobsSelection();

            $('#inputNoteKolektif').val('');
            $('#statusDoneKolektif').prop('checked', true);
            
            $('#updateProgressKolektifModal').modal('show');
        }

        renderJobsSelection() {
            let htmlJobs = `<option value="" selected disabled>-- Pilih Pekerjaan --</option>`;
            this.jobCounts = new Map();
            this.uniqueJobs = new Map();
            if (typeof listJobs !== 'undefined' && listJobs.length > 0) {
                // Collect all active jobs from all selected data and make them distinct
                
                this.selectedData.forEach(data => {
                    if (data.penyelia_map) {
                        let activeJobs = data.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 1);
                        activeJobs.forEach(aj => {
                            if (!this.uniqueJobs.has(aj.jobs_hash)) {
                                this.uniqueJobs.set(aj.jobs_hash, aj.jobs.name);
                            }
                            
                            // Count how many data in this job stage
                            let currentCount = this.jobCounts.get(aj.jobs_hash) || 0;
                            this.jobCounts.set(aj.jobs_hash, currentCount + 1);
                        });
                    }
                });
                
                if(this.uniqueJobs.size > 0) {
                    let htmlCounts = '';
                    this.uniqueJobs.forEach((name, hash) => {
                        htmlJobs += `<option value="${hash}">${name}</option>`;
                        
                        let count = this.jobCounts.get(hash);
                        htmlCounts += `<span class="badge bg-white text-info border border-info-subtle fw-normal">${name}: <strong>${count}</strong></span>`;
                    });
                    $('#jobCountsInfo').html(htmlCounts);
                } else {
                    $('#jobCountsInfo').html('');
                }
            }
            $('#prosesNowKolektif').html(htmlJobs);
        }

        initDatePicker() {
            $('#dateProgressKolektif').flatpickr({
                altInput: true,
                locale: "id",
                dateFormat: "Y-m-d",
                altFormat: "j F Y",
                defaultDate: 'today'
            });
        }

        renderSelectedList() {
            let htmlList = '';
            
            this.selectedData.forEach((data, index) => {
                let permohonan = data.permohonan;
                let namaLayanan = permohonan.layanan_jasa?.nama_layanan || '-';
                let noKontrak = permohonan.kontrak.no_kontrak || '-';
                
                htmlList += `
                    <div class="bg-light p-3 rounded-4 mb-2 border border-white">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="fw-semibold text-dark" style="font-size: 0.85rem;">${namaLayanan}</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border-0 px-2 py-1" style="font-size: 0.7rem;">
                                <i class="bi bi-hash me-1"></i>${noKontrak}
                            </span>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-building me-1"></i> ${permohonan.pelanggan.perusahaan.nama_perusahaan}
                        </div>
                    </div>
                `;
            });

            if (!htmlList) {
                htmlList = `
                    <div class="text-center py-4 bg-light rounded-4 border border-dashed">
                        <span class="text-muted italic">Tidak ada data</span>
                    </div>
                `;
            }

            $('#detailSelectedLhu').html(htmlList);
        }

        save(obj) {
            let note = $('#inputNoteKolektif').val();
            let prosesNow = $('#prosesNowKolektif').val();
            let sProgress = $(`[name="statusProgressKolektif"]:checked`).val();

            if (!prosesNow) {
                return Swal.fire({
                    icon: "warning",
                    text: 'Tolong pilih proses yang akan diupdate!',
                });
            }

            if(!sProgress) {
                return Swal.fire({
                    icon: "warning",
                    text: 'Tolong pilih status yang akan diupdate!',
                });
            }

            if (note == '') {
                return Swal.fire({
                    icon: "warning",
                    text: 'Tolong masukan note!',
                });
            }

            let arrIdPenyelia = [];
            this.selectedData.forEach((data) => {
                arrIdPenyelia.push(data.penyelia_hash);
            });

            let prosesName = this.uniqueJobs.get(prosesNow);
            let prosesCount = this.jobCounts.get(prosesNow);
            
            Swal.fire({
                icon: "warning",
                html: `Anda akan mengupdate <strong>${prosesCount}</strong> data <strong>${prosesName}</strong> dengan status <strong>${sProgress == 'done' ? 'Selesai' : 'Proses'}</strong>!`,
                showCancelButton: true,
                confirmButtonText: 'Iya',
                cancelButtonText: 'Tidak',
                customClass: {
                    confirmButton: 'btn btn-outline-success mx-1',
                    cancelButton: 'btn btn-outline-danger mx-1'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                spinner('show', $(obj));
    
                const formData = new FormData();
                arrIdPenyelia.forEach((id) => {
                    formData.append('arrIdPenyelia[]', id);
                });
                formData.append('note', note);
                formData.append('idJobs', prosesNow);
                formData.append('sProgress', sProgress);
    
                ajaxPost(`api/v1/penyelia/actionJobProsesKolektif`, formData, result => {
                    $('#updateProgressKolektifModal').modal('hide');
                    selectedItems = [];
                    $('.check-lhu').prop('checked', false);
                    $('#checkAllLhu').prop('checked', false);
                    spinner('hide', $(obj));
                    updateCollectiveContainer();
                    if(typeof loadData === 'function') {
                        loadData();
                    }
                }, error => {
                    spinner('hide', $(obj));
                })
            })
        }
    }

    window.ProgressKolektif = new ProgressKolektifModal();

    function openProgressModalKolektif() {
        window.ProgressKolektif.open();
    }

    function simpanProgressKolektif(obj) {
        window.ProgressKolektif.save(obj);
    }
</script>
@endpush
