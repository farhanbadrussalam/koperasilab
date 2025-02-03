class ModalDocument {
    constructor(options = {}) {
        this.options = {
            modal: options.modal ?? true,
            title: options.title ?? '',
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

    show(url){
        this.loadData(url);
        $('#pdfModal').modal('show');
    }

    /**
     * Loads data into the PDF viewer by appending an iframe with the specified URL.
     * Also updates the modal label with the title from options.
     *
     * @param {string} url - The URL to load into the iframe.
     */

    loadData(url){
        $('#pdfViewer').empty();
        $('#pdfViewer').append(`<iframe src="${base_url + '/'+ url}" width="100%" height="100%" frameborder="0"></iframe>`);
        $('#pdfModalLabel').text(this.options.title);
    }

    modalCreate() {
        return `
            <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pdfModalLabel">Tampilan PDF</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div id="pdfViewer" style="height: 80vh;">
                                <!-- PDF akan dimuat di sini -->
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
            $('#pdfModal').remove();
        }
    }
}