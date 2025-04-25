class Inventory_tld {
    constructor(options = {}) {
        this._initializeProperties(options);

        if(this.canShow){
            $('body').append(this.modalCreate);
        }
        
        this._bindEventListeners();
    }
    _initializeProperties(options) {
        this.canShow = options.preview || false;
        this.page = 1;
        this.limit = 5;
        this.selectedArr = [];
        this.formTldSelected = false;
        this.jenisSelected = false;
    }

    _bindEventListeners() {
        this.eventSelected = 'inventory.selected';

        const self = this;
        $('#pagination-inventory-tld').on('click', 'a', function (e) {
            e.preventDefault();
            self.page = e.target.dataset.page;
            self._loadData();
        });
    }

    _loadData(){
        // filter
        const params = {
            page: this.page,
            limit: this.limit
        };
        const self = this;

        this.jenisSelected && (params.jenis = this.jenisSelected);
        
        $('#placeholder-inventory-tld').show();
        $('#list-inventory-tld').hide();
        $('#list-inventory-tld').empty();
        ajaxGet(`api/v1/tld/getData`, params, result => {
            for (const tld of result.data) {
                let checked = '<button type="button" class="btn btn-outline-primary btn-sm btn-pilih-tld" data-tld-hash="'+tld.tld_hash+'">Pilih</button>';
                let find = this.selectedArr.find(d => d.tld == tld.tld_hash);
                if(find){
                    checked = '<span class="text-success"><i class="bi bi-check"></i> Terpilih</span>';
                }
                
                if(tld.status == 1){
                    checked = '';
                }

                $('#list-inventory-tld').append(`
                    <div class="card mb-2 shadow-sm">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="card-title mb-0">${tld.no_seri_tld}</div>
                                    <small class="card-subtitle mb-0">
                                        ${tld.status == 1 ? '<span class="badge rounded text-bg-success">Digunakan</span>' : `<span class="badge rounded text-bg-danger">Tidak Digunakan</span>`}
                                    </small>
                                </div>
                                <div class="px-2 col-2 text-start">
                                    <div class="card-title mb-0">${tld.jenis}</div>
                                </div>
                                <div class="text-end col-2">
                                    ${checked}
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }

            // add pagination
            $('#pagination-inventory-tld').html(createPaginationHTML(result.pagination));

            $('.btn-pilih-tld').on('click', function (e) {
                e.preventDefault();
                const tldFind = result.data.find(d => d.tld_hash == $(this).data('tld-hash'));
                document.dispatchEvent(new CustomEvent(self.eventSelected, {
                    detail: {
                        data_tld: tldFind,
                        selected: self.formTldSelected
                    }
                }));
                $('#modal-inventory-tld').modal('hide');
            });

            $('#placeholder-inventory-tld').hide();
            $('#list-inventory-tld').show();
        })
    }

    show(id, arr = [], jenis = false){
        this.page = 1;
        this.selectedArr = arr; 
        this.formTldSelected = id;
        this.jenisSelected = jenis;
        this._loadData();
        $('#modal-inventory-tld').modal('show');
    }

    modalCreate(){
        return `
            <div class="modal fade" id="modal-inventory-tld" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body row justify-content-center">
                            <div class="d-flex justify-content-between mb-3" id="modal-inventory-tld-action">
                                <h1 class="modal-title fs-5" id="">Inventory TLD</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div id="filter-inventory-tld">

                            </div>
                            <div class="" id="list-inventory-tld"></div>
                            <div class="body-placeholder" id="placeholder-inventory-tld">
                                <div class="placeholder-glow mb-1">
                                    <span class="placeholder placeholder-lg col-12 rounded" style="height: 40px;"></span>
                                </div>
                                <div class="placeholder-glow mb-1">
                                    <span class="placeholder placeholder-lg col-12 rounded" style="height: 40px;"></span>
                                </div>
                                <div class="placeholder-glow mb-1">
                                    <span class="placeholder placeholder-lg col-12 rounded" style="height: 40px;"></span>
                                </div>
                                <div class="placeholder-glow mb-1">
                                    <span class="placeholder placeholder-lg col-12 rounded" style="height: 40px;"></span>
                                </div>
                                <div class="placeholder-glow mb-1">
                                    <span class="placeholder placeholder-lg col-12 rounded" style="height: 40px;"></span>
                                </div>
                            </div>
                            <div aria-label="Page navigation example" id="pagination-inventory-tld"></div>
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
        $('#modal-inventory-tld').remove();
    }
}