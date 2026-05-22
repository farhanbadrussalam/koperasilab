class ModalDocument {
    constructor(options = {}) {
        this.options = {
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
        $('body').on('click', '#btnDownloadPdf', this.download.bind(this));
        $('body').on('click', '#btnPrintPdf', this.print.bind(this));
        $('body').on('click', '#btnEditPdfForm', this.showForm.bind(this));
        $('body').on('click', '#btnCloseForm', this.hideForm.bind(this));
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
        this.loadData(url);
        $('#pdfModal').modal('show');
        if (options.formHtml) {
            this.setFormContent(options.formHtml)
        };
    }

    hide() {
        $('#pdfModal').modal('hide');
    }

    /**
     * Loads data into the PDF viewer by appending an iframe with the specified URL.
     * Also updates the modal label with the title from options.
     *
     * @param {string} url - The URL to load into the iframe.
     */

    loadData(url) {
        this.currentUrl = url;
        const $container = $('#pdfViewer');
        $container.empty();

        // Tambahkan progress bar (menggunakan class Bootstrap)
        $container.append(`
            <div id="pdfLoading" class="d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
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
                    $('#pdfLoading').remove();
                    $(this).fadeIn();
                });

                // Fallback jika event load iframe tidak terpicu oleh plugin PDF browser
                setTimeout(() => {
                    if ($('#pdfLoading').length > 0) {
                        $('#pdfLoading').remove();
                        $iframe.fadeIn();
                    }
                }, 5000);

                $container.append($iframe);
            })
            .catch(() => {
                $('#pdfLoading').remove();
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

        $('#pdfModalLabel').text(this.options.title);
    }

    setFormContent(html) {
        $('#formContainer').html(html);
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
        const iframe = $('#pdfViewer iframe')[0];
        if (iframe) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    }

    showForm() {
        $('#pdfModalDialog').removeClass('modal-lg').addClass('modal-xl');
        $('#pdfCol').removeClass('col-12').addClass('col-lg-8 border-end');

        // Menunggu transisi selesai (300ms) baru menampilkan form
        setTimeout(() => {
            $('#formCol').fadeIn(250);
        }, 300);

        this.options.withForm = true;
    }

    hideForm() {
        // Sembunyikan form dengan fadeOut, setelah selesai baru perkecil modal
        $('#formCol').fadeOut(200, () => {
            $('#pdfCol').removeClass('col-lg-8 border-end').addClass('col-12');
            $('#pdfModalDialog').removeClass('modal-xl').addClass('modal-lg');
        });

        this.options.withForm = false;
    }

    modalCreate() {
        const modalSize = this.options.withForm ? 'modal-xl' : 'modal-lg';
        const pdfColClass = this.options.withForm ? 'col-lg-8 border-end' : 'col-12';
        const formStyle = this.options.withForm ? '' : 'display: none;';
        const btnEdit = this.options.isCanEdit ? `
            <button type="button" class="btn btn-sm btn-warning ms-2" id="btnEditPdfForm">
                <i class="bi bi-pencil"></i> Edit
            </button>
        ` : ``;
        const btnClose = this.options.isCanEdit ? `
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" id="btnCloseForm" aria-label="Close"></button>
        ` : ``;

        return `
            <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
                <div class="modal-dialog ${modalSize} modal-dialog-centered" id="pdfModalDialog" style="transition: max-width 0.3s ease;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pdfModalLabel">Tampilan PDF</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="d-flex p-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="btnPrintPdf">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split rounded" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span><i class="bi bi-download"></i> Download</span>
                                    </button>
                                    <ul class="dropdown-menu overflow-hidden">
                                        <li><a class="dropdown-item" href="#" id="btnDownloadPdf" data-type="full">Download PDF</a></li>
                                        <li><a class="dropdown-item" href="#" id="btnDownloadPdf" data-type="original">Download Original</a></li>
                                    </ul>
                                </div>
                                ${btnEdit}
                            </div>
                            <div class="row g-0">
                                <div class="${pdfColClass}" id="pdfCol" style="transition: all 0.3s ease;">
                                    <div id="pdfViewer" style="height: 80vh;">
                                        <!-- PDF akan dimuat di sini -->
                                    </div>
                                </div>
                                <div class="col-lg-4 p-3 pt-1" id="formCol" style="height: 80vh; overflow-y: auto; ${formStyle}">
                                    <div class="p-3 bg-light rounded-3 shadow h-100 position-relative">
                                        ${btnClose}
                                        <h6 class="fw-bold mb-3">${this.options.formTitle}</h6>
                                        <div id="formContainer">
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
            $('#pdfModal').modal('hide');
            $('#pdfModal').remove();
        }
    }
}
