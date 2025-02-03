class Detail {
    constructor(options = {}) {
        this.options = {
            modal: options.modal ?? true,
            information: options.information ?? true,
            jenis: options.jenis ?? 'permohonan',
            tab: {
                pengguna: options.tab.pengguna ?? false,
                activitas: options.tab.activitas ?? false,
                dokumen: options.tab.dokumen ?? false,
                log: options.tab.log ?? false,
                periode: options.tab.periode ?? false
            }
        }

        this._initializeProperties();
        this._createCustomEvents();

        if(this.options.modal){
            $('body').append(this.modalCreate());
        }
        
        this._bindEventListeners();
    }

    _initializeProperties() {
        this.data = null;
        this.activeTab = 'pengguna';
    }

    _createCustomEvents() {
        this.eventSimpan = new CustomEvent('detail.simpan', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
    }

    setTab(tab) {
        this.activeTab = tab;
    }

    addData(data) {
        this.data = data;
    }

    loadData(){
        $('#container-detail').empty();
        if(this.options.information){
            $('#container-detail').append(this.createInformation());
        }

        // cek jika di this.options.tab false semua
        if(!this.options.tab.pengguna && !this.options.tab.activitas && !this.options.tab.dokumen && !this.options.tab.log){
            $('#container-detail').append(`<div class="text-center text-muted mt-3 w-100">Tidak ada tab yang ditampilkan</div>`);
        }else{
            $('#container-detail').append(this.createTab());
            showPopupReload();
            // mengaktifkan tab sesuai dengan this.activeTab
            $(`#pills-${this.activeTab}-tab`).tab('show');
        }
    }

    show(url) {
        $('#offcanvasDetail').offcanvas('show');
        this.loadDataAjax(url);
    }

    loadDataAjax(url){
        $('#titleDetail').text('Detail');
        $('#mainContent').hide();
        $('#loadingDetail').show();
        spinner('show', $('#loadingDetail'), {
            width: '100px',
            height: '100px'
        });
        ajaxGet(url, false, result => {
            this.addData(result.data);
            this.loadData();
            spinner('hide', $('#loadingDetail'));
            $('#mainContent').show();
            $('#loadingDetail').hide();
        }, error => {
            spinner('hide', $('#loadingDetail'));
            $('#loadingDetail').hide();
        });
    }

    // membuat informasi
    createInformation() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        const data = this.data;

        let noKontrak = data.kontrak?.no_kontrak ?? '-';
        let tipeKontrak = data.tipe_kontrak ?? '-';
        let jenis_layanan = data.jenis_layanan?.name ?? '-';
        let jenis_layanan_parent = data.jenis_layanan_parent?.name ?? '-';
        let pelanggan = data.pelanggan?.name ?? '-';
        let perusahaan = data.pelanggan.perusahaan?.nama_perusahaan ?? '-';
        let status = data.status ?? '-';
        let jmlKontrol = data.jumlah_kontrol ?? 0;
        let total_harga = data.total_harga ?? 0;
        let created_at = data.created_at ?? '-';
        let periodePemakaian = data.periode_pemakaian ?? [];
        let periodeNow = data.periode ?? '';
        let layananJasa = data.layanan_jasa?.nama_layanan ?? '';
        let jenisTld = data.jenis_tld?.name ?? '';

        

        $('#titleDetail').text(`${layananJasa} - ${jenisTld}`);

        container.innerHTML = `
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">No kontrak</label>
                <div class="col-auto">
                    ${noKontrak}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Jenis layanan</label>
                <div class="col-auto gap-1">
                    <span class="badge bg-${tipeKontrak == 'kontrak lama'?'success' : 'primary'}-subtle fw-normal rounded-pill text-${tipeKontrak == 'kontrak lama'?'success' : 'primary'}-emphasis">${tipeKontrak}</span>
                    <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${jenis_layanan} - ${jenis_layanan_parent}</span>
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Pelanggan</label>
                <div class="col-auto">
                    ${pelanggan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Perusahaan</label>
                <div class="col-auto">
                    ${perusahaan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Status</label>
                <div class="col-auto">
                    ${statusFormat(this.options.jenis, status)}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Jumlah kontrol</label>
                <div class="col-auto">
                    ${jmlKontrol}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Harga</label>
                <div class="col-auto">
                    ${formatRupiah(total_harga)}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Dibuat pada</label>
                <div class="col-auto">
                    ${dateFormat(created_at, 0)}
                </div>
            </div>
        `;

        return container;
    }

    createTab() {
        const container = document.createElement('div');
        const tabs = {
          pengguna: { title: 'Pengguna', content: this.createPenggunaContent(), badge: this.data?.pengguna?.length ?? 0 },
          activitas: { title: 'Aktivitas', content: this.createAktivitasContent() },
          dokumen: { title: 'Dokumen', content: this.createDokumenContent() },
          log: { title: 'Log', content: this.createLogContent() },
          periode: { title: 'Periode', content: this.createPeriodeContent() }
        };
      
        let htmlTabNav = '';
        let htmlTabContent = '';
      
        for (const tabId in tabs) {
          if (this.options.tab[tabId]) {
            const tab = tabs[tabId];
            const badge = tab.badge ? `<span class="badge text-bg-secondary">${tab.badge}</span>` : '';
      
            htmlTabNav += `
              <li class="nav-item" role="presentation">
                <button class="nav-link custom-nav-link" id="pills-${tabId}-tab" data-bs-toggle="pill" data-bs-target="#pills-${tabId}" type="button" role="tab" aria-controls="pills-${tabId}" aria-selected="false">${tab.title} ${badge}</button>
              </li>
            `;
      
            htmlTabContent += `
              <div class="tab-pane fade" id="pills-${tabId}" role="tabpanel" aria-labelledby="pills-${tabId}-tab">
                ${tab.content}
              </div>
            `;
          }
        }
      
        if (htmlTabNav === '') {
          return `<div class="text-center text-muted mt-3 w-100">Tidak ada tab yang ditampilkan</div>`;
        }
      
        container.innerHTML = `
          <ul class="nav nav-pills custom-nav-pills mb-3 shadow-sm" id="pills-tab" role="tablist">
            ${htmlTabNav}
          </ul>
          <div class="tab-content custom-tab-content p-3" id="pills-tabContent">
            ${htmlTabContent}
        `;
      
        return container;
    }

    // Example content creation functions – replace with your actual logic
    createPenggunaContent() {  
        if(this.data.pengguna && this.data.pengguna.length > 0){
            let html = '';
            for (const [i,pengguna] of this.data.pengguna.entries()) {
                let txtRadiasi = '';
                pengguna.radiasi?.map(nama_radiasi => txtRadiasi += `<span class="badge rounded-pill text-bg-secondary me-1 mb-1">${nama_radiasi}</span>`);
                
                html += `
                    <div class="card mb-2 shadow-sm fs-8">
                        <div class="card-body row align-items-center py-1">
                            <div class="col-auto lh-sm d-flex align-items-center">
                                <span class="col-form-label me-2">${i + 1}</span>
                                <div class="mx-2">
                                    <div>${pengguna.nama}</div>
                                    <small class="text-body-secondary fw-light">${pengguna.posisi}</small>
                                    <div class="d-flex flex-wrap">
                                        ${txtRadiasi}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 ms-auto">
                                ${[1,2].includes(pengguna.status) ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-danger">Inactive</span>'}
                            </div>
                            <div class="col-md-2 text-end">
                                <a class="btn btn-sm btn-outline-secondary show-popup-image" href="${base_url}/storage/${pengguna.media.file_path}/${pengguna.media.file_hash}" title="Show ktp"><i class="bi bi-file-person-fill"></i></a>
                            </div>
                        </div>
                    </div>
                `;
            }
            return html;
        }else {
            return '<p>Tidak ada data pengguna</p>'; 
        }
    }
    createAktivitasContent() { 
        return '<p>Aktivitas content</p>'; 
    }
    createDokumenContent() { 
        return '<p>Dokumen content</p>'; 
    }
    createLogContent() { 
        return '<p>Log content</p>'; 
    }
    createPeriodeContent(){
        let htmlPeriode = '';
        let data = this.data;
        if(this.data.tipeKontrak == 'kontrak lama'){
            let findPeriode = data.kontrak?.periode.find(periode => periode.periode == data.periode);
            htmlPeriode = `
                <div class="card mb-1">
                    <div class="card-body p-1 px-3">
                        <div>Periode ${data.periode}</div>
                        <div class="text-body-secondary">
                            <small>${dateFormat(findPeriode.start_date, 4)} - ${dateFormat(findPeriode.end_date, 4)}</small>
                        </div>
                    </div>
                </div>
            `;
        }else{
            for (const [i,periode] of data.periode_pemakaian.entries()) {
                htmlPeriode += `
                    <div class="card mb-1">
                        <div class="card-body p-1 px-3">
                            <div>Periode ${i + 1}</div>
                            <div class="text-body-secondary">
                                <small>${dateFormat(periode.start_date, 4)} - ${dateFormat(periode.end_date, 4)}</small>
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        return `
            ${htmlPeriode}
        `;
    }

    modalCreate() {
        return `
            <div class="offcanvas offcanvas-end custom-offcanvas" tabindex="-1" id="offcanvasDetail" aria-labelledby="offcanvasDetail">
                <div class="offcanvas-header border-bottom py-1">
                    <div>
                        <h3 class="fw-semibold mb-2" id="titleDetail">Detail</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div id="loadingDetail" class="m-auto"></div>
                <div id="mainContent" class="offcanvas-body p-2">
                    <div class="pt-2" id="container-detail">

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
            $('#offcanvasDetail').remove();
        }
    }
}