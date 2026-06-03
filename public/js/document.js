class ModalDocument {
    constructor(options = {}) {
        this.options = {
            id: options.id ?? 'pdfModal',
            modal: options.modal ?? true,
            title: options.title ?? '',
            withForm: options.withForm ?? false, // Opsi untuk menampilkan form di kanan
            formTitle: options.formTitle ?? 'Form TTD', // Judul form
            isCanEdit: options.isCanEdit ?? false
        };

        this._initializeProperties();
        this._createCustomEvents();

        if (this.options.modal) {
            $('body').append(this.modalCreate());
        }

        this._bindEventListeners();
    }

    _initializeProperties() {
        this.data = null;
        this.currentUrl = null;
    }

    _createCustomEvents() {
        // this.eventSimpan = new CustomEvent('detail.simpan', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
        const id = this.options.id;
        $('body').on('click', `#${id}BtnDownloadPdf`, this.download.bind(this));
        $('body').on('click', `#${id}BtnPrintPdf`, this.print.bind(this));
        $('body').on('click', `#${id}BtnEditPdfForm`, this.showForm.bind(this));
        $('body').on('click', `#${id}BtnCloseForm`, this.hideForm.bind(this));
    }

    /**
     * Menampilkan modal dan memuat PDF
     * @param {string} url - URL PDF
     * @param {string|null} formHtml - HTML opsional untuk diisi ke dalam formContainer
     */
    show(url, options = {}) {
        this.options = {
            ...this.options,
            ...options
        };
        if (this.options.withForm) {
            this.showForm();
        }
        this.loadData(url);
        $(`#${this.options.id}`).modal('show');
        if (options.formHtml) {
            this.setFormContent(options.formHtml)
        };
    }

    hide() {
        $(`#${this.options.id}`).modal('hide');
    }

    /**
     * Loads data into the PDF viewer by appending an iframe with the specified URL.
     * Also updates the modal label with the title from options.
     *
     * @param {string} url - The URL to load into the iframe.
     */

    loadData(url) {
        this.currentUrl = url;
        const id = this.options.id;
        const $container = $(`#${id}Viewer`);
        $container.empty();

        // Tambahkan progress bar (menggunakan class Bootstrap)
        $container.append(`
            <div id="${id}Loading" class="d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
                <div class="w-50">
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                    </div>
                    <p class="text-center mt-2 text-muted">Memuat dokumen, mohon tunggu...</p>
                </div>
            </div>
        `);

        const fullUrl = base_url + '/' + url + '#view=FitH';

        // Validasi URL sebelum memuat ke iframe
        fetch(fullUrl, { method: 'HEAD' })
            .then(response => {
                if (!response.ok) throw new Error();

                const $iframe = $(`<iframe src="${fullUrl}" width="100%" height="100%" frameborder="0" style="display:none;"></iframe>`);

                $iframe.on('load', function () {
                    $(`#${id}Loading`).remove();
                    $(this).fadeIn();
                });

                // Fallback jika event load iframe tidak terpicu oleh plugin PDF browser
                setTimeout(() => {
                    if ($(`#${id}Loading`).length > 0) {
                        $(`#${id}Loading`).remove();
                        $iframe.fadeIn();
                    }
                }, 5000);

                $container.append($iframe);
            })
            .catch(() => {
                $(`#${id}Loading`).remove();
                $container.append(`
                    <div class="d-flex flex-column justify-content-center align-items-center h-100 text-center p-3">
                        <i class="bi bi-file-earmark-exclamation text-danger" style="font-size: 3rem;"></i>
                        <h6 class="mt-3">Gagal Memuat PDF</h6>
                        <p class="text-muted small">Dokumen tidak dapat diakses atau terjadi kesalahan pada server.</p>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                        </button>
                    </div>
                `);
            });

        $(`#${id}Label`).text(this.options.title);
    }

    setFormContent(html) {
        $(`#${this.options.id}FormContainer`).html(html);
    }

    download(obj) {
        if (!this.currentUrl) return;
        const type = $(obj.target).data('type');
        const link = document.createElement('a');
        link.href = base_url + '/' + this.currentUrl + '?dl=1&type=' + type; // Tambahkan query parameter untuk mendownload'
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    print() {
        const iframe = $(`#${this.options.id}Viewer iframe`)[0];
        if (iframe) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    }

    showForm() {
        const id = this.options.id;
        $(`#${id}Dialog`).removeClass('modal-lg').addClass('modal-xl');
        $(`#${id}PdfCol`).removeClass('col-12').addClass('col-lg-8 border-end');

        // Menunggu transisi selesai (300ms) baru menampilkan form
        setTimeout(() => {
            $(`#${id}FormCol`).fadeIn(250);
        }, 300);

        this.options.withForm = true;
    }

    hideForm() {
        const id = this.options.id;
        // Sembunyikan form dengan fadeOut, setelah selesai baru perkecil modal
        $(`#${id}FormCol`).fadeOut(200, () => {
            $(`#${id}PdfCol`).removeClass('col-lg-8 border-end').addClass('col-12');
            $(`#${id}Dialog`).removeClass('modal-xl').addClass('modal-lg');
        });

        this.options.withForm = false;
    }

    modalCreate() {
        const id = this.options.id;
        const modalSize = this.options.withForm ? 'modal-xl' : 'modal-lg';
        const pdfColClass = this.options.withForm ? 'col-lg-8 border-end' : 'col-12';
        const formStyle = this.options.withForm ? '' : 'display: none;';
        const btnEdit = this.options.isCanEdit ? `
            <button type="button" class="btn btn-sm btn-warning ms-2" id="${id}BtnEditPdfForm">
                <i class="bi bi-pencil"></i> Edit
            </button>
        ` : ``;
        const btnClose = this.options.isCanEdit ? `
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" id="${id}BtnCloseForm" aria-label="Close"></button>
        ` : ``;

        return `
            <div class="modal fade" id="${id}" tabindex="-1" aria-labelledby="${id}Label" aria-hidden="true">
                <div class="modal-dialog ${modalSize} modal-dialog-centered" id="${id}Dialog" style="transition: max-width 0.3s ease;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="${id}Label">Tampilan PDF</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="d-flex p-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="${id}BtnPrintPdf">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split rounded" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span><i class="bi bi-download"></i> Download</span>
                                    </button>
                                    <ul class="dropdown-menu overflow-hidden">
                                        <li><a class="dropdown-item" href="#" id="${id}BtnDownloadPdf" data-type="full">Download PDF</a></li>
                                        <li><a class="dropdown-item" href="#" id="${id}BtnDownloadPdf" data-type="original">Download Original</a></li>
                                    </ul>
                                </div>
                                ${btnEdit}
                            </div>
                            <div class="row g-0">
                                <div class="${pdfColClass}" id="${id}PdfCol" style="transition: all 0.3s ease;">
                                    <div id="${id}Viewer" style="height: 80vh;">
                                        <!-- PDF akan dimuat di sini -->
                                    </div>
                                </div>
                                <div class="col-lg-4 p-3 pt-1" id="${id}FormCol" style="height: 80vh; overflow-y: auto; ${formStyle}">
                                    <div class="p-3 bg-light rounded-3 shadow h-100 position-relative">
                                        ${btnClose}
                                        <h6 class="fw-bold mb-3">${this.options.formTitle}</h6>
                                        <div id="${id}FormContainer">
                                            <!-- Form TTD atau input lainnya -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    on(eventName, callback = () => { }) {
        return document.addEventListener(eventName, callback);
    }

    destroy() {
        if (this.options.modal) {
            $(`#${this.options.id}`).modal('hide');
            $(`#${this.options.id}`).remove();
        }
    }
}
