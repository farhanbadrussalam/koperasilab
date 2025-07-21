class Media {
    constructor(options = {}) {
        this._initializeProperties();
        this._createCustomEvents();
        this._bindEventListeners();
    }

    _initializeProperties() {}
    _createCustomEvents() {}
    _bindEventListeners() {}

    loadData() {

    }
    modalCreate() {}
    on(eventName, callback = () => {}) {}
    destroy() {}
}
