class SignatureSelect {
    constructor(container, options = {}) {
        // Handle jQuery object, selector string, or DOM element
        if (typeof jQuery !== 'undefined' && container instanceof jQuery) {
            this.container = container[0];
        } else if (typeof container === 'string') {
            this.container = document.querySelector(container);
        } else {
            this.container = container;
        }

        this.options = {
            inputId: 'validasiFrontDesk',
            label: 'Nyatakan Valid & Lengkap',
            placeholderText: 'Menunggu validasi petugas...',
            signerUser: false,
            defaultSig: false,
            signedDate: false,
            width: '100%',
            height: '150px',
            persentage: true,
            onToggle: null, // Callback function when toggled
            ...options
        };

        this.checked = false;

        if (this.container) {
            this.init();
        } else {
            console.error('SignatureSelect: Container element not found.');
        }

        if(this.options.defaultSig){
            $(`#signature-select-${this.options.inputId}`).addClass('d-none');
            this.toggle(true);
        }
    }

    init() {
        this.render();
        this.bindEvents();
    }

    render() {
        const { inputId, label, placeholderText, signerUser, signedDate } = this.options;

        this.container.innerHTML = `
            <div class="d-flex justify-content-center mb-4" id="signature-select-${inputId}">
                <div class="form-check form-switch custom-switch">
                    <input class="form-check-input fs-4" type="checkbox" id="${inputId}">
                    <label class="form-check-label pt-1 fw-semibold ms-2" for="${inputId}">
                        ${label}
                    </label>
                </div>
            </div>

            <div class="position-relative d-flex align-items-center justify-content-center">
                <div id="ttd-placeholder-${inputId}" class="border-2 border-dashed border-secondary border-opacity-25 rounded-3 p-4 w-100 bg-light">
                    <div class="text-muted opacity-50">
                        <i class="fas fa-signature fa-2x mb-2"></i>
                        <small class="d-block">${placeholderText}</small>
                    </div>
                </div>

                <div id="ttd-signed-${inputId}" class="d-none border-2 border-success border rounded-3 p-3 w-100 bg-soft-success position-relative">
                    <div class="position-absolute top-50 start-50 translate-middle opacity-25" style="z-index: 0;">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>

                    <div style="position: relative; z-index: 1;" class="text-center">
                        <div id="content-ttd-${inputId}"></div>

                        <hr class="my-2 border-success opacity-50 w-50 mx-auto">

                        <p class="fw-bold text-dark small mb-0 text-uppercase" id="content-ttd-name-${inputId}">${signerUser.name}</p>
                        <small class="text-success" style="font-size: 10px;">
                            Divalidasi pada: <span id="content-ttd-date-${inputId}">${signedDate}</span>
                        </small>
                    </div>
                </div>
            </div>
        `;
    }

    bindEvents() {
        const checkbox = this.container.querySelector(`#${this.options.inputId}`);
        checkbox.addEventListener('change', (e) => {
            this.toggle(e.target.checked);
            if (typeof this.options.onToggle === 'function') {
                this.options.onToggle(e.target.checked);
            }

            this.checked = e.target.checked;
        });
    }

    getValue() {
        if (this.checked) {
            return [this.options.signerUser?.ttd_hash, this.options.signerUser?.user_hash];
        } else {
            return [false, false];
        }
    }

    toggle(isChecked) {
        const placeholder = this.container.querySelector(`#ttd-placeholder-${this.options.inputId}`);
        const signed = this.container.querySelector(`#ttd-signed-${this.options.inputId}`);

        if (isChecked) {
            placeholder.classList.add('d-none');
            signed.classList.remove('d-none');
            this.renderCanvas();
            this.updateData({
                signedDate: this.options.signedDate ? dateFormat(this.options.signedDate, 2) : dateFormat(new Date(), 2)
            });
        } else {
            placeholder.classList.remove('d-none');
            signed.classList.add('d-none');

            // Hapus tanda tangan
            this.container.querySelector(`#content-ttd-${this.options.inputId}`).innerHTML = '';
        }
    }

    updateData(data) {
        if (data.managerContent !== undefined) {
            this.container.querySelector(`#content-ttd-${this.options.inputId}`).innerHTML = data.managerContent;
        }
        if (data.signerUser !== undefined) {
            this.container.querySelector(`#content-ttd-name-${this.options.inputId}`).innerText = data.signerUser.name;
        }
        if (data.signedDate !== undefined) {
            this.container.querySelector(`#content-ttd-date-${this.options.inputId}`).innerText = data.signedDate;
        }
    }

    renderCanvas(){
        const {signerUser, width, height, persentage, inputId} = this.options;

        if(signerUser.ttd_image || this.options.defaultSig){
            return signature(this.container.querySelector(`#content-ttd-${inputId}`), {
                defaultSig: this.options.defaultSig ? this.options.defaultSig : signerUser.ttd_image,
                width: width,
                height: height,
                persentage: persentage
            });
        } else {
            this.container.querySelector(`#content-ttd-${inputId}`).innerHTML = `
                <p class="text-danger small mb-0 text-center">
                    <i class="fas fa-exclamation-triangle"></i> Anda belum mengatur TTD Digital<br>
                    <a href="${base_url}/userProfile#ttd" class="text-primary">Silahkan klik disini</a>
                </p>
            `;
        }
    }
}
