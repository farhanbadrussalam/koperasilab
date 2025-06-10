class FormValidation {
    constructor(idForm, options = {}) {
        this.selfForm = $(`#${idForm}`);
        this.options = options;

        this._initializeProperties();
    }

    _initializeProperties() {
        this.selfForm.parsley();
    }

    validate() {
        return this.selfForm.parsley().validate();
    }

    reset() {
        this.selfForm.parsley().reset();
        // reset class
        this.selfForm.find('input').removeClass('is-invalid is-valid');
        this.selfForm.find('select').removeClass('is-invalid is-valid');
        this.selfForm.find('textarea').removeClass('is-invalid is-valid');
    }
}
