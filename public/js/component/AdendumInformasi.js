class AdendumInformasi {
    constructor(options){
        this.options = options;

        this._initializeProperties();
        this._createModal();
        this._bindEventListeners();
    }

    _initializeProperties(){

    }

    _bindEventListeners(){
        $('#btnAdendumInformasi').on('click', this.handleAdendumInformasi.bind(this));
    }

    handleAdendumInformasi(e){

    }

    _createModal(){
        const modalHtml = `
            <div class="modal fade" id="adendumInformasiModal" tabindex="-1" aria-labelledby="adendumInformasiModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="adendumInformasiModalLabel">Adendum Informasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="containerLoading" class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only"></span>
                                </div>
                            </div>
                            <div id="containerContent">
                                <!-- Konten adendum informasi akan dimuat di sini -->
                                <p>Konten adendum informasi akan ditampilkan di sini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHtml);
    }

    show(options = {}){
        this.options = { ...this.options, ...options };

        $('#containerLoading').addClass('d-flex').removeClass('d-none');
        $('#containerContent').removeClass('d-flex').addClass('d-none');

        $('#adendumInformasiModalLabel').text(this.options.title || 'Adendum Informasi');

        ajaxGet(this.options.url, false, (response) => {
            this.setFormContent(response.data);
            $('#containerLoading').removeClass('d-flex').addClass('d-none');
            $('#containerContent').addClass('d-flex').removeClass('d-none');
        });

        $('#adendumInformasiModal').modal('show');
    }

    setFormContent(data) {
        let contentHtml = '<div class="d-flex flex-column gap-2 w-100">';
        let pengiriman = data.pengiriman || [];
        for (const [index, adendum] of data.adendum.entries()) {
            let jmlPergantian = adendum.permohonan_detail.filter(detail => detail.type === 'ganti').length;
            let jmlPenambahan = adendum.permohonan_detail.filter(detail => detail.type === 'baru').length;

            let statusLhu = pengiriman.find(p => p.detail.find(d => d.jenis == 'lhu') && p.permohonan_hash == adendum.permohonan_hash);
            let htmlInvoice = '';
            if(jmlPenambahan > 0){
                let statusInvoice = pengiriman.find(p => p.detail.find(d => d.jenis == 'invoice') && p.permohonan_hash == adendum.permohonan_hash);
                htmlInvoice = `
                    <div>
                        <span class="fw-normal">• Invoice</span>
                        <small class="cursoron hover-1 pe-2">
                            ${statusFormat('pengiriman', statusInvoice?.status)}
                        </small>
                    </div>
                `;
            }

            contentHtml += `
                <div class="border-bottom d-flex justify-content-between align-items-center">
                    <div class="p-2">
                        <span class="fw-semibold fs-6">Adendum ${index + 1}</span>
                        <div >
                            <small class="fs-6 text-body-tertiary">${dateFormat(adendum.created_at, 4)}</small>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            ${htmlInvoice}
                            <div>
                                <span class="fw-normal">• Lhu</span>
                                <small class="cursoron hover-1 pe-2">
                                    ${statusFormat('pengiriman', statusLhu?.status)}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="p-2 text-end">
                        <span class="fw-semibold fs-6">${formatRupiah(adendum.total_harga)}</span>
                        <div class="d-flex gap-2 flex-wrap">
                            <div>
                                <span class="fw-normal">Pergantian :</span>
                                <small class="ps-1">${jmlPergantian}</small>
                            </div>
                            <div>
                                <span class="fw-normal">Penambahan :</span>
                                <small class="ps-1">${jmlPenambahan}</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        contentHtml += '</div>';

        $('#containerContent').html(contentHtml);
    }

    destroy(){
        $('#adendumInformasiModal').modal('hide');
        $('#adendumInformasiModal').remove();
    }

    on(eventName, callback = () => {}) {
        return document.addEventListener(eventName, callback);
    }
}
