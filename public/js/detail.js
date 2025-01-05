class Detail {
    constructor(options = {}) {
        this.options = {
            modal: options.modal ?? true
        }

        this._initializeProperties();
        this._createCustomEvents();

        if(this.options.modal){
            $('body').append(this.modalCreate());
        }
        
        this._bindEventListeners();
    }

    _initializeProperties() {
        this.dataKeuangan = null;
    }

    _createCustomEvents() {
        this.eventSimpan = new CustomEvent('detail.simpan', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
    }

    addData(data) {
        this.dataKeuangan = data;
    }

    show() {
        $('#offcanvasDetail').offcanvas('show');
    }

    modalCreate() {
        return `
            <div class="offcanvas offcanvas-end custom-offcanvas" tabindex="-1" id="offcanvasDetail" aria-labelledby="offcanvasDetail">
                <div class="offcanvas-header border-bottom py-1">
                    <div class="d-flex flex-column">
                        <h5 class="offcanvas-title fw-semibold" id="offcanvasDetail">Layanan TLD - Soca (Mata)</h5>
                        <div class="text-body-tertiary fs-6">
                            <small>Details</small>
                            <div class="vr"></div>
                            <small>#E-0001/JKRL/XII/2024</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body px-0 py-1">
                    <div class="row-gap-1 px-3 text-end" id="div-detail-badge">
                        <span class="badge bg-success-subtle fw-normal rounded-pill text-success-emphasis">Kontrak lama</span>
                        <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">Evaluasi - Dengan Kontrak</span>
                    </div>
                    <div class="px-3">
                        <div class="mt-2 mb-3 fw-semibold">Pelanggan</div>
                        <div class="d-flex justify-content-between">
                            <div class="row align-items-center">
                                <div class="col-auto pe-0"><i class="bi bi-building"></i></div>
                                <div class="col-auto px-1 lh-1">
                                    <div class="">PT Sejahtera</div>
                                    <small class="text-body-tertiary">P-001</small>
                                </div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-auto pe-0"><i class="bi bi-person-check-fill"></i></div>
                                <div class="col-auto px-1 lh-1">
                                    <div class="">Supri</div>
                                    <small class="text-body-tertiary">Supir</small>
                                </div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-auto pe-0"><i class="bi bi-envelope"></i></div>
                                <div class="col-auto px-1 lh-1">
                                    <small class="">pelanggan@gmail.com</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    on(eventName, callback = () => {}) {
        return document.addEventListener(eventName, callback);
    }

    destroy(){
        if(this.options.modal){
            $('#offcanvasDetail').remove();
        }
    }
}