class Timeline {
    constructor(options = {}) {
        this.options = {
            timeline: options.timeline ?? [],
            status: options.status ?? 0,
            id: options.id ?? '',
            start_date: options.startDate ?? '',
            end_date: options.endDate ?? '',
        };

        this._initializeProperties();
        this._createCustomEvents();
        this._bindEventListeners();
    }

    _initializeProperties() {
        this.dataTimeline = this.options.timeline.filter(tugas => !tugas.point_jobs);
        this.dataTimelineParalel = this.options.timeline.filter(tugas => tugas.point_jobs);
        this.widthCalc = 100 / this.dataTimeline.length;
        this.widthCalcParalel = 100 / this.dataTimelineParalel.length;
    }

    _createCustomEvents() {
        // this.eventSimpan = new CustomEvent('detail.simpan', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
        $('.step0').off('click').on('click', this.modalTimeline.bind(this));
        $(document).off('click', '.btn-show-hide-progress').on('click', '.btn-show-hide-progress', this.showHideProgress.bind(this));
        $('#progresLhuModal').off('hide.bs.modal').on('hide.bs.modal', (obj) => {
            obj.target.remove();
        });
    }

    getTimeline() {
        return this.options.timeline;
    }

    addData(data) {
        this.dataTimeline = data;
    }

    elementCreate() {
        const htmlTimeline = this.dataTimeline.map(tugas => {
            const jobActive = tugas.status === 2 ? 'active' : (tugas.status === 1 ? 'onprogress' : '');
            return `<li class="${jobActive} step0 cursor-pointer" data-idmap="${tugas.map_hash}" data-id="${this.options.id}" style="width: ${this.widthCalc}%;"><span class="px-1">${tugas.jobs.name}</span></li>`;
        }).join('');

        let pointJobs = false;
        let isTrackStopped = false; // Flag untuk menandai jalur sudah dihentikan
        const htmlTimelineParalel = this.dataTimelineParalel.map(tugas => {
            pointJobs = tugas.jobs_paralel;
            let jobActive = tugas.status === 2 ? 'active' : (tugas.status === 1 ? 'onprogress' : '');
            if (isTrackStopped) {
                jobActive = 'stopped';
            }
            // Jika status tugas ini is_stopped true, flag dinyalakan
            if (tugas.is_stopped) {
                isTrackStopped = true;
            }
            return `<li class="${jobActive} step0 cursor-pointer" data-idmap="${tugas.map_hash}" data-id="${this.options.id}" style="width: ${this.widthCalc}%;"><span class="px-1">${tugas.jobs.name}</span></li>`;
        }).join('');

        return `
        <div class="col-md-12 mt-2 pb-0">
            <div class="hr-text my-2">Alur Progres</div>
            <ul id="progressbar" class="text-center mb-0">
                ${htmlTimeline}
            </ul>
            <div class="rounded ${pointJobs ? '' : 'd-none'}" style="border: 1px dashed !important;">
                <div class="text-center fs-6">
                    Proses setelah ${pointJobs?.name ?? ''}
                </div>
                <ul id="progressbar" class="text-center mb-0">
                    ${htmlTimelineParalel}
                </ul>
            </div>
        </div>
        `;
    }
    buttonCreate() {
        const rangeDate = range_date(this.options.start_date, this.options.end_date, 3);

        let htmlProgress = ``;
        if (this.options.timeline.length > 0) {
            htmlProgress = `
                <div>
                    <a class="py-1 text-decoration-none btn-show-hide-progress fw-semibold fs-8" href="#timeline-progress-${this.options.id}" data-bs-toggle="collapse"
                    >Lihat Progress LAB<i class="bi bi-chevron-down ms-2"></i></a>
                </div>
            `;
        }
        return `
            <div class="d-flex flex-column">
                <div class="text-muted small">
                    <i class="bi bi-clock"></i> <span class="fw-semibold">Pelaksanaan LAB:</span> ${rangeDate.start} - ${rangeDate.end}
                </div>
                ${htmlProgress}
            </div>
        `;
    }

    showHideProgress(e) {
        const $el = $(e.currentTarget);
        // Jeda sebentar agar Bootstrap selesai memperbarui atribut aria-expanded
        setTimeout(() => {
            const isExpanded = $el.attr('aria-expanded') === 'true';
            if (isExpanded) {
                $el.html('Tutup Progress LAB<i class="bi bi-chevron-up ms-2"></i>');
            } else {
                $el.html('Lihat Progress LAB<i class="bi bi-chevron-down ms-2"></i>');
            }
        }, 50);
    }

    /**
     * Helper untuk membuat badge status yang lebih modern
     */
    _getStatusBadge(status) {
        switch (status) {
            case 1:
                return `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-medium"><i class="bi bi-three-dots me-1"></i> Sedang dikerjakan</span>`;
            case 2:
                return `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-medium"><i class="bi bi-check-circle me-1"></i> Selesai</span>`;
            default:
                return `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fw-medium"><i class="bi bi-hourglass me-1"></i> Belum dimulai</span>`;
        }
    }

    modalTimeline(obj) {
        const $el = $(obj.currentTarget);

        if ($el.hasClass('stopped')) {
            Swal.fire({
                icon: 'info',
                title: 'Proses Dihentikan',
                text: 'Proses ini tidak dapat dilanjutkan karena tidak ada periode layanan berikutnya (N+2).',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#3085d6',
            });
            return;
        }

        const idmap = $el.data('idmap');
        const $iconContainer = $el.find('span');
        const originalHtml = $iconContainer.html();

        // 1. Tampilkan loading spinner dan nonaktifkan klik sementara
        $iconContainer.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        $el.css('pointer-events', 'none');

        ajaxGet(`api/v1/penyelia/getPenyeliaMapById/${idmap}`, false, (result) => {
            // 2. Kembalikan state elemen asli setelah data didapat
            $iconContainer.html(originalHtml);
            $el.css('pointer-events', 'auto');

            const data = result.data;

            const statusBadge = this._getStatusBadge(data.status);

            const petugasHtml = data.petugas?.length > 0
                ? data.petugas.map(d => `
                    <li class="list-group-item d-flex align-items-center border-0 px-0 py-2">
                        <div class="avatar avatar-xs bg-light text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-person fs-6"></i>
                        </div>
                        <div class="lh-sm">
                            <div class="fw-semibold small text-dark">${d.user.name}</div>
                            <div class="text-muted extra-small" style="font-size: 0.75rem;">${d.user.email}</div>
                        </div>
                    </li>
                `).join('')
                : '<li class="list-group-item border-0 px-0 py-2 text-muted small italic">Tidak ada petugas ditunjuk</li>';

            const completionDetails = data.status == 2 ? `
                <div class="bg-light border rounded-3 p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Tanggal Selesai</span>
                        <span class="fw-medium small text-dark">${data.done_at ? dateFormat(data.done_at, 4) : '-'}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Dikerjakan oleh</span>
                        <span class="fw-medium small text-dark">${data.done_by?.name ?? '-'}</span>
                    </div>
                </div>
            ` : '';

            const htmlModal = `
                <div class="modal fade" id="progresLhuModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-bottom-0 pb-0">
                                <h5 class="modal-title fw-bold text-dark">${data.jobs?.name ?? 'Detail Progres'}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-3">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <span class="text-uppercase text-muted fw-bold small" style="letter-spacing: 0.5px; font-size: 0.7rem;">Status</span>
                                    ${statusBadge}
                                </div>

                                ${completionDetails}

                                <div class="mt-2">
                                    <span class="text-uppercase text-muted fw-bold small d-block mb-2" style="letter-spacing: 0.5px; font-size: 0.7rem;">Petugas Pelaksana</span>
                                    <ul class="list-group list-group-flush border-top">
                                        ${petugasHtml}
                                    </ul>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0">
                                <button type="button" class="btn btn-light btn-sm px-3 text-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#progresLhuModal').remove();
            $('body').append(htmlModal);
            this.render();

            $('#progresLhuModal').modal('show');
        });

    }

    render() {
        this._bindEventListeners();
    }

    on(eventName, callback = () => { }) {
        return document.addEventListener(eventName, callback);
    }

    destroy() {

    }
}
