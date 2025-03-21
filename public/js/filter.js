class FilterComponent {
    constructor(idElement, options = {}) {
        this.selfElement = $(`#${idElement}`);

        this._initializeProperties(options);
        this._createCustomEvents();
        this._bindEventListeners();

        this.loadFilter();
    }

    _initializeProperties(options) {
        this.data = null;
        this.jenis = options.jenis ?? '';
        this.options = {
            filter: Object.fromEntries(Object.entries(options.filter).filter(([key, value]) => value === true))
        };

        this.selfElement.addClass('w-100 d-flex flex-wrap my-3 gap-2');
    }

    _createCustomEvents() {
        this.eventChange = new CustomEvent('filter.change', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
    }

    _setupFilter(filterName) {
        const self = this;
        if(filterName == 'status'){
            $('#filterStatus').select2({
                theme: "bootstrap-5",
                placeholder: 'All Status',
                allowClear: true
            }).on('select2:select', function(e) {
                document.dispatchEvent(self.eventChange);
            }).on('select2:clear', function(e) {
                document.dispatchEvent(self.eventChange);
                // Tutup Select2 setelah sedikit delay
                setTimeout(() => {
                    $(this).select2('close');
                }, 10); // Penundaan kecil agar Select2 menutup dengan benar
            });
        }

        if(filterName == 'jenis_tld'){
            $('#filterJenisTld').select2({
                theme: "bootstrap-5",
                placeholder: 'All Jenis TLD',
                allowClear: true
            }).on('select2:select', function(e) {
                document.dispatchEvent(self.eventChange);
            }).on('select2:clear', function(e) {
                document.dispatchEvent(self.eventChange);
                // Tutup Select2 setelah sedikit delay
                setTimeout(() => {
                    $(this).select2('close');
                }, 10); // Penundaan kecil agar Select2 menutup dengan benar
            });
        }

        if(filterName == 'jenis_layanan'){
            let jenisChild = $('#filterJenisLayananChild').select2({
                theme: "bootstrap-5",
                placeholder: 'All',
                allowClear: true
            }).on('select2:select', function(e) {
                document.dispatchEvent(self.eventChange);
            }).on('select2:clear', function(e) {
                document.dispatchEvent(self.eventChange);
                // Tutup Select2 setelah sedikit delay
                setTimeout(() => {
                    $(this).select2('close');
                }, 10); // Penundaan kecil agar Select2 menutup dengan benar
            });

            $('#filterJenisLayanan').select2({
                theme: "bootstrap-5",
                placeholder: 'All Jenis Layanan',
                allowClear: true,
            }).on('select2:select', function(e) {
                ajaxGet(`api/v1/permohonan/getChildJenisLayanan/${e.params.data.id}`, {isFilter: true}, (result) => {
                    jenisChild.empty().append('<option value="">All</option>');
                    result.data.child.forEach((list) => {
                        jenisChild.append(`<option value="${list.jenis_layanan_hash}">${list.name}</option>`);
                    });
        
                    jenisChild.trigger('change');
                });
        
                document.dispatchEvent(self.eventChange);
            }).on('select2:clear', function(e) {
                jenisChild.empty().append('<option value="">All</option>').trigger('change');
                document.dispatchEvent(self.eventChange);
        
                // Tutup Select2 setelah sedikit delay
                setTimeout(() => {
                    $(this).select2('close');
                    $(jenisChild).select2('close');
                }, 10); // Penundaan kecil agar Select2 menutup dengan benar
            });
        }

        if(filterName == 'no_kontrak'){
            $('#filterSearchKontrak').select2({
                theme: "bootstrap-5",
                placeholder: 'Search No Kontrak',
                allowClear: true,
                ajax: {
                    url: `${base_url}/api/v1/kontrak/search`,
                    dataType: 'json',
                    type: 'GET',
                    processing: true,
                    serverSide: true,
                    delay: 250,
                    headers: {
                        'Authorization': `Bearer ${bearer}`,
                        'Content-Type': 'application/json'
                    },
                    data: function(params) {
                        let queryParams = {
                            no_kontrak: params.term
                        }
                        return queryParams;
                    },
                    processResults: function(response) {
                        return {
                            results: response.data && response.data.map((list) => {
                                return {
                                    id: list.kontrak_hash,
                                    text: list.no_kontrak
                                }
                            })
                        }
                    }
                }
            }).on('select2:select', function(e) {
                document.dispatchEvent(self.eventChange);
            }).on('select2:clear', function(e) {
                document.dispatchEvent(self.eventChange);
                // Tutup Select2 setelah sedikit delay
                setTimeout(() => {
                    $(this).select2('close');
                }, 10); // Penundaan kecil agar Select2 menutup dengan benar
            });
        }

        if(filterName == 'perusahaan'){
            $('#filterPerusahaan').select2({
                theme: "bootstrap-5",
                placeholder: 'All Perusahaan',
                allowClear: true,
                ajax: {
                    url: `${base_url}/api/v1/filter/getPerusahaan`,
                    dataType: 'json',
                    type: 'GET',
                    processing: true,
                    serverSide: true,
                    delay: 250,
                    headers: {
                        'Authorization': `Bearer ${bearer}`,
                        'Content-Type': 'application/json'
                    },
                    data: function(params) {
                        let queryParams = {
                            perusahaan: params.term
                        }
                        return queryParams;
                    },
                    processResults: function(response) {
                        return {
                            results: response.data && response.data.map((list) => {
                                return {
                                    id: list.perusahaan_hash,
                                    text: list.nama_perusahaan
                                }
                            })
                        }
                    }
                }
            }).on('select2:select', function(e) {
                document.dispatchEvent(self.eventChange);
            }).on('select2:clear', function(e) {
                document.dispatchEvent(self.eventChange);
                // Tutup Select2 setelah sedikit delay
                setTimeout(() => {
                    $(this).select2('close');
                }, 10); // Penundaan kecil agar Select2 menutup dengan benar
            });
        }
    }

    loadFilter() {
        this.selfElement.empty();

        this.createFilter(html => {
            if (!html) {
                this.selfElement.append(`<div class="text-center text-muted mt-3 w-100">Tidak ada filter yang ditampilkan</div>`);
            } else {
                this.selfElement.append(html);
            }
        });
    }

    createFilter(callback) {
        this.options.filter.jenis_tld && this.createJenisTldContent(html => callback(html), Object.keys(this.options.filter).indexOf('jenis_tld'));
        this.options.filter.status && this.createStatusContent(html => callback(html), Object.keys(this.options.filter).indexOf('status'));
        this.options.filter.jenis_layanan && this.createJenisLayananContent(html => callback(html), Object.keys(this.options.filter).indexOf('jenis_layanan'));
        this.options.filter.no_kontrak && this.createNoKontrakContent(html => callback(html), Object.keys(this.options.filter).indexOf('no_kontrak'));
        this.options.filter.perusahaan && this.createPerusahaanContent(html => callback(html), Object.keys(this.options.filter).indexOf('perusahaan'));
    }

    createStatusContent(callback, index) {
        const self = this;
        let params  = {
            jenis: this.jenis
        };
        ajaxGet(`api/v1/filter/getStatus`, params, result => {
            let html = `
                <div class="col-3 order-${index+1}">
                    <select name="filterStatus" id="filterStatus" class="form-select form-select-sm">
                        <option value="" selected>All</option>
                        ${result.data.map(item => `<option value="${item.id}">${item.name}</option>`).join('')}
                    </select>
                </div>
            `;
            callback(html);
            self._setupFilter('status');
        })
    }

    createJenisTldContent(callback, index) {
        const self = this;
        ajaxGet(`api/v1/filter/getJenisTld`, false, result => {
            let html = `
                <div class="col-3 order-${index+1}">
                    <select name="filterJenisTld" id="filterJenisTld" class="form-select form-select-sm">
                        <option value="" selected>All</option>
                        ${result.data.map(item => `<option value="${item.jenis_tld_hash}">${item.name}</option>`).join('')}
                    </select>
                </div>
            `;
            callback(html);
            self._setupFilter('jenis_tld');
        });
    }

    createJenisLayananContent(callback, index) {
        const self = this;
        ajaxGet(`api/v1/filter/getJenisLayanan`, false, result => {
            let html = `
                <div class="col-3 order-${index+1}">
                    <select name="filterJenisLayanan" id="filterJenisLayanan" class="form-select form-select-sm">
                        <option value="" selected>All</option>
                        ${result.data.map(item => `<option value="${item.jenis_layanan_hash}">${item.name}</option>`).join('')}
                    </select>
                </div>
                <div class="col-2 order-${index+1}">
                    <select name="filterJenisLayananChild" id="filterJenisLayananChild" class="form-select form-select-sm">
                        <option value="" selected>All</option>
                    </select>
                </div>
            `;
            callback(html);
            self._setupFilter('jenis_layanan');
        });
    }

    createNoKontrakContent(callback, index) {
        const self = this;
        let html = `
            <div class="col-3 order-${index+1}">
                <select name="filterSearchKontrak" id="filterSearchKontrak" class="form-select form-select-sm">
                    <option value="" selected>All</option>
                </select>
            </div>
        `;
        callback(html);
        self._setupFilter('no_kontrak');
    }

    createPerusahaanContent(callback, index) {
        const self = this;
        let html = `
            <div class="col-3 order-${index+1}">
                <select name="filterPerusahaan" id="filterPerusahaan" class="form-select form-select-sm">
                    <option value="" selected>All</option>
                </select>
            </div>
        `;
        callback(html);
        self._setupFilter('perusahaan');
    }

    getAllValue(){
        let allValue = {};

        this.options.filter.jenis_tld && (allValue.jenis_tld = this.getValue('jenis_tld'));
        this.options.filter.status && (allValue.status = this.getValue('status'));
        this.options.filter.jenis_layanan && (allValue.jenis_layanan = this.getValue('jenis_layanan'));
        this.options.filter.jenis_layanan && (allValue.jenis_layanan_child = this.getValue('jenis_layanan_child'));
        this.options.filter.no_kontrak && (allValue.no_kontrak = this.getValue('no_kontrak'));
        this.options.filter.perusahaan && (allValue.perusahaan = this.getValue('perusahaan'));

        return allValue;
    }

    /**
     * Get the value of a filter by its name
     * @param {string} filterName The name of the filter
     * @returns {string} The value of the filter
     */
    getValue(filterName) {
        if (filterName == 'status') return $('#filterStatus').val();
        if (filterName == 'jenis_tld') return $('#filterJenisTld').val();
        if (filterName == 'jenis_layanan') return $('#filterJenisLayanan').val();
        if (filterName == 'jenis_layanan_child') return $('#filterJenisLayananChild').val();
        if (filterName == 'no_kontrak') return $('#filterSearchKontrak').val();
        if (filterName == 'perusahaan') return $('#filterPerusahaan').val();
    }

    clear() {
        this.options.filter.status && ($('#filterStatus').val('').trigger('change'));
        this.options.filter.jenis_tld && ($('#filterJenisTld').val('').trigger('change'));
        this.options.filter.jenis_layanan && ($('#filterJenisLayanan').val('').trigger('change'));
        this.options.filter.jenis_layanan_child && ($('#filterJenisLayananChild').val('').trigger('change'));
        this.options.filter.no_kontrak && ($('#filterSearchKontrak').val('').trigger('change'));
        this.options.filter.perusahaan && ($('#filterPerusahaan').val('').trigger('change'));
    }
    
    on(eventName, callback = () => { }) {
        return document.addEventListener(eventName, callback);
    }
}
