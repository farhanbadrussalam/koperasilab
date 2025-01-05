class Delivery {
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
        this.dataPengiriman = null;
    }

    _createCustomEvents() {
        this.eventSimpan = new CustomEvent('delivery.simpan', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
    }

    addData(data) {
        this.dataPengiriman = data;
    }

    show() {
        $('#offcanvasDelivery').offcanvas('show');
    }

    modalCreate() {
        return ``;
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