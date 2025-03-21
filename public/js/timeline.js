class Timeline {
    constructor(options = {}) {
        this.options = {
            timeline: options.timeline ?? [],
            status: options.status ?? 0,
            id: options.id ?? ''
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
        $('#progresLhuModal').off('hide.bs.modal').on('hide.bs.modal', (obj) => {
            obj.target.remove();
        });
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
        const htmlTimelineParalel = this.dataTimelineParalel.map(tugas => {
            pointJobs = tugas.jobs_paralel;
            const jobActive = tugas.status === 2 ? 'active' : (tugas.status === 1 ? 'onprogress' : '');
            return `<li class="${jobActive} step0 cursor-pointer" data-idmap="${tugas.map_hash}" data-id="${this.options.id}" style="width: ${this.widthCalc}%;"><span class="px-1">${tugas.jobs.name}</span></li>`;
        }).join('');
        
        return `
        <div class="col-md-12 mt-2 pt-4 pb-0">
            <ul id="progressbar" class="text-center mb-0">
                ${htmlTimeline}
            </ul>
            <div class="rounded" style="border: 1px dashed !important;">
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
    modalTimeline(obj) {
        const id = $(obj.target).data('id');
        const idmap = $(obj.target).data('idmap');

        ajaxGet(`api/v1/penyelia/getPenyeliaMapById/${idmap}`, false, (result) => {
            const data = result.data;
            let htmlModal = `
                <div class="modal fade" id="progresLhuModal" tabindex="-1" aria-labelledby="progresLhuModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div>
                                    <h3 class="fw-bold">${data.jobs?.name ?? ''}</h2>
                                </div>
                                <div class="mt-3 d-flex justify-content-between">
                                    <label class="fw-normal">Status</label>
                                    ${(() => {
                                        switch (data.status) {
                                            case 1:
                                                return `<div class="text-primary small"><i class="bi bi-three-dots"></i> Sedang dikerjakan</div>`;
                                            case 2:
                                                return `<div class="text-success small"><i class="bi bi-check-circle"></i> Selesai</div>`;
                                            default:
                                                return `<div class="text-secondary small"><i class="bi bi-x-circle"></i> Belum dimulai</div>`;
                                        }
                                    })()}
                                </div>
                                ${
                                    data.status == 2 ? `
                                    <div class="mt-2 d-flex justify-content-between">
                                        <label class="fw-normal">Tanggal selesai</label>
                                        <div class="text-body-secondary small">${data.done_at ? dateFormat(data.done_at, 4) : '-'}</div>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-between">
                                        <label class="fw-normal">Dikerjakan oleh</label>
                                        <div class="text-body-secondary small">${data.done_by?.name ?? '-'}</div>
                                    </div>` : ''
                                }
                                <div class="mt-3">
                                    <span class="fw-normal">Petugas :</span>
                                    <ul class="list-group mt-2">
                                        ${data.petugas?.map(d => 
                                            `<li class="list-group-item small p-1">
                                                <div class="d-flex align-items-center">
                                                    <span>${d.user.name} -</span>
                                                    <span class="text-secondary ps-1">${d.user.email}</span>
                                                </div>
                                            </li>`
                                        ).join('')}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    
            $('body').append(htmlModal);
            this.render();
    
            $('#progresLhuModal').modal('show');
        });

    }

    render() {
        this._bindEventListeners();
    }

    on(eventName, callback = () => {}) {
        return document.addEventListener(eventName, callback);
    }

    destroy(){
        
    }
}