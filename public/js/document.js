class ModalDocument {
    constructor(options = {}) {
        this.options = {
            modal: options.modal ?? true,
            title: options.title ?? '',
            withForm: options.withForm ?? false, // Opsi untuk menampilkan form di kanan
            formTitle: options.formTitle ?? 'Form TTD', // Judul form
        };

        this._initializeProperties();
        this._createCustomEvents();

        if(this.options.modal){
            $('body').append(this.modalCreate());
        }

        this._bindEventListeners();
    }

    _initializeProperties() {
        this.data = null;
    }

    _createCustomEvents() {
        // this.eventSimpan = new CustomEvent('detail.simpan', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
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
        if(this.options.withForm && options.formHtml) this.setFormContent(options.formHtml);
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

    loadData(url){
        const $container = $('#pdfViewer');
        $container.empty();

        // Tambahkan loading spinner (menggunakan class Bootstrap)
        $container.append(`
            <div id="pdfLoading" class="d-flex justify-content-center align-items-center" style="height: 100%;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only"></span>
                </div>
            </div>
        `);

        const $iframe = $(`<iframe src="${base_url + '/' + url}" width="100%" height="100%" frameborder="0" style="display:none;"></iframe>`);

        // Event listener ketika iframe selesai memuat konten
        $iframe.on('load', function() {
            $('#pdfLoading').remove();
            $(this).fadeIn(); // Tampilkan iframe dengan efek smooth
        });

        $container.append($iframe);
        $('#pdfModalLabel').text(this.options.title);
    }

    setFormContent(html) {
        $('#formContainer').html(html);
    }

    modalCreate() {
        const modalSize = this.options.withForm ? 'modal-xl' : 'modal-lg';

        let bodyContent = '';
        if (this.options.withForm) {
            bodyContent = `
                <div class="row g-0">
                    <div class="col-lg-8 border-end">
                        <div id="pdfViewer" style="height: 80vh;">
                            <!-- PDF akan dimuat di sini -->
                        </div>
                    </div>
                    <div class="col-lg-4 bg-light" style="height: 80vh; overflow-y: auto;">
                        <div class="p-3">
                            <h6 class="fw-bold mb-3">${this.options.formTitle}</h6>
                            <div id="formContainer">
                                <!-- Form TTD atau input lainnya -->
                            </div>
                        </div>
                    </div>
                </div>`;
        } else {
            bodyContent = `
                <div id="pdfViewer" style="height: 80vh;">
                    <!-- PDF akan dimuat di sini -->
                </div>`;
        }

        return `
            <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
                <div class="modal-dialog ${modalSize} modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pdfModalLabel">Tampilan PDF</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            ${bodyContent}
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
            $('#pdfModal').modal('hide');
            $('#pdfModal').remove();
        }
    }
}
