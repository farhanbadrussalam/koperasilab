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
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="adendumInformasiModalLabel text-dark">
                                <i class="bi bi-info-circle me-2 text-primary"></i>Adendum Informasi
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="containerLoading" class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only"></span>
                                </div>
                            </div>
                            <div id="containerContent" class="p-1">
                                <!-- Konten adendum informasi akan dimuat di sini -->
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
        if (!data.adendum || data.adendum.length === 0) {
            $('#containerContent').html(`
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-x fs-1 text-body-tertiary"></i>
                    <p class="mt-2 text-secondary">Tidak ada data adendum ditemukan.</p>
                </div>
            `);
            return;
        }

        let contentHtml = '<div class="d-flex flex-column gap-3 w-100">';
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
                        <span class="text-muted small d-block mb-1">Invoice</span>
                        <div class="cursoron hover-1">
                            ${statusFormat('pengiriman', statusInvoice?.status)}
                        </div>
                    </div>
                `;
            }

            contentHtml += `
                <div class="card border border-light-subtle shadow-sm hover-shadow-transition">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold mb-1 text-primary">Adendum #${index + 1}</h6>
                                <div class="text-body-tertiary small">
                                    <i class="bi bi-calendar-event me-1"></i>${dateFormat(adendum.created_at, 4)}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-5 text-dark">${formatRupiah(adendum.total_harga)}</div>
                                <div class="d-flex gap-2 justify-content-end mt-1">
                                    <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle fw-normal">
                                        Ganti: ${jmlPergantian}
                                    </span>
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-normal">
                                        Baru: ${jmlPenambahan}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 border-top pt-3">
                            ${htmlInvoice}
                            <div>
                                <span class="text-muted small d-block mb-1">LHU</span>
                                <span class="cursoron hover-1">
                                    ${statusFormat('pengiriman', statusLhu?.status)}
                                </span>
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
