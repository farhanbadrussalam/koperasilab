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
                periode: options.tab.periode ?? false,
                // Pengiriman
                items: options.tab.items ?? false,
                bukti: options.tab.bukti ?? false,
                // Penyelia
                proses: options.tab.proses ?? false,
            },
            activeTab: options.activeTab ?? 'pengguna'
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
        this.info = {};
    }

    _createCustomEvents() {
        this.eventSimpan = new CustomEvent('detail.simpan', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
    }

    _actionAccordion(){
        let Accordion = function(el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;
             
            let links = this.el.find('.link');
      
            links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
         }
      
         Accordion.prototype.dropdown = function(e) {
            let $el = e.data.el;
            let $this = $(this);
            let $next = $this.next();
            
            $next.slideToggle();
            $this.parent().toggleClass('open');
      
            if (!e.data.multiple) {
               $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
            };
         }  
      
         let accordion = new Accordion($('#pills-tab'), false);
         if(this.options.activeTab){
            $(`#pills-${this.options.activeTab}`).click();
         }
        //  new Accordion($('#pills-tab'), false);
    }
    
    _initInformasi(){
        switch (this.options.jenis) {
            case 'surattugas':
                console.log(this.data);
                this.info = {
                    no_kontrak: this.data.permohonan.kontrak?.no_kontrak ?? '-',
                    tipe_kontrak: this.data.permohonan.tipe_kontrak ?? '-',
                    jenis_layanan: this.data.permohonan.jenis_layanan.name ?? '-',
                    jenis_layanan_parent: this.data.permohonan.jenis_layanan_parent?.name ?? '-',
                    pelanggan: this.data.permohonan.pelanggan?.name ?? '-',
                    perusahaan: this.data.permohonan.pelanggan.perusahaan?.nama_perusahaan ?? '-',
                    status: this.data.status ?? '-',
                    start_date: this.data.start_date ?? '-',
                    end_date: this.data.end_date ?? '-',
                    created_at: this.data.created_at ?? '-',
                    jenisStatus : 'surattugas'
                };
                break;

            case 'pengiriman':
                const dataPengiriman = this.data;
                this.info = {
                    no_pengiriman : dataPengiriman.id_pengiriman ?? '-',
                    no_resi : dataPengiriman.no_resi ?? 'Belum ada',
                    ekspedisi : dataPengiriman.ekspedisi?.name ?? '-',
                    no_kontrak : dataPengiriman.kontrak?.no_kontrak ?? '-',
                    tujuan : dataPengiriman.tujuan?.name ?? '-',
                    alamat : dataPengiriman.alamat?.alamat ?? '-',
                    created_at : dataPengiriman.created_at ?? '-',
                    status : dataPengiriman.status,
                    jenisStatus : 'pengiriman'
                };
                break;

            case 'permohonan':
                this.info = {
                    no_kontrak : this.data.kontrak?.no_kontrak ?? '-',
                    tipe_kontrak : this.data.tipe_kontrak ?? '-',
                    jenis_layanan : this.data.jenis_layanan.name ?? '-',
                    jenis_layanan_parent : this.data.jenis_layanan_parent?.name ?? '-',
                    pelanggan : this.data.pelanggan?.name ?? '-',
                    perusahaan : this.data.pelanggan.perusahaan?.nama_perusahaan ?? '-',
                    status : this.data.status ?? '-',
                    jmlKontrol : this.data.jumlah_kontrol ?? 0,
                    total_harga : this.data.total_harga ?? 0,
                    created_at : this.data.created_at ?? '-',
                    periodePemakaian : this.data.periode_pemakaian ?? [],
                    periodeNow : this.data.periode ?? '',
                    layananJasa : this.data.layanan_jasa?.nama_layanan ?? '',
                    jenisTld : this.data.jenis_tld?.name ?? '',
                    jenisStatus : 'permohonan'
                }
                break;
                
            case 'penyelia':
                this.info = {
                    no_kontrak : this.data.permohonan.kontrak?.no_kontrak ?? '-',
                    tipe_kontrak : this.data.permohonan.tipe_kontrak ?? '-',
                    jenis_layanan : this.data.permohonan.jenis_layanan.name ?? '-',
                    jenis_layanan_parent : this.data.permohonan.jenis_layanan_parent?.name ?? '-',
                    pelanggan : this.data.permohonan.pelanggan?.name ?? '-',
                    perusahaan : this.data.permohonan.pelanggan.perusahaan?.nama_perusahaan ?? '-',
                    status : this.data.status ?? '-',
                    jmlKontrol : this.data.permohonan.jumlah_kontrol ?? 0,
                    total_harga : this.data.permohonan.total_harga ?? 0,
                    created_at : this.data.permohonan.created_at ?? '-',
                    periodePemakaian : this.data.permohonan.periode_pemakaian ?? [],
                    periodeNow : this.data.permohonan.periode ?? '',
                    layananJasa : this.data.permohonan.layanan_jasa?.nama_layanan ?? '',
                    jenisTld : this.data.permohonan.jenis_tld?.name ?? '',
                    jenisStatus : 'penyelia'
                }
                break;

            case 'kontrak':
                this.info = {
                    no_kontrak : this.data.no_kontrak ?? '-',
                    tipe_kontrak : this.data.tipe_kontrak ?? '-',
                    jenis_layanan : this.data.jenis_layanan.name ?? '-',
                    jenis_layanan_parent : this.data.jenis_layanan_parent?.name ?? '-',
                    pelanggan : this.data.pelanggan?.name ?? '-',
                    perusahaan : this.data.pelanggan.perusahaan?.nama_perusahaan ?? '-',
                    status : this.data.status ?? '-',
                    jmlKontrol : this.data.jumlah_kontrol ?? 0,
                    total_harga : this.data.total_harga ?? 0,
                    created_at : this.data.created_at ?? '-',
                    periodePemakaian : this.data.periode_pemakaian ?? [],
                    periodeNow : this.data.periode ?? '',
                    layananJasa : this.data.layanan_jasa?.nama_layanan ?? '',
                    jenisTld : this.data.jenis_tld?.name ?? '',
                    jenisStatus : 'kontrak'
                }
                break;
            default:

                break;
        }
    }

    addData(data) {
        this.data = data;
    }

    loadData() {
        $('#container-detail').empty();

        if (this.options.information) {
            this._initInformasi();

            switch (this.options.jenis) {
                case 'pengiriman':
                    $('#container-detail').append(this.createInformationPengiriman());
                    break;
                case 'surattugas':
                    $('#container-detail').append(this.createInformationSuratTugas());
                    break;
                default:
                    $('#container-detail').append(this.createInformationPermohonan());
                    break;
            }
        }

        const hasTab = Object.values(this.options.tab).some(tab => tab);
        if (!hasTab) {
            $('#container-detail').append(`<div class="text-center text-muted mt-3 w-100">Tidak ada tab yang ditampilkan</div>`);
        } else {
            $('#container-detail').append(this.createTab());
            showPopupReload();
            this._actionAccordion();
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
    createInformationPermohonan() {
        const container = document.createElement('div');
        container.className = 'container fs-7';
        console.log(this.info)

        $('#titleDetail').text(`${this.info.layananJasa} - ${this.info.jenisTld}`);

        container.innerHTML = `
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">No kontrak</label>
                <div class="col-auto">
                    ${this.info.no_kontrak}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Jenis layanan</label>
                <div class="col-auto gap-1">
                    <span class="badge bg-${this.info.tipe_kontrak == 'kontrak lama'?'success' : 'primary'}-subtle fw-normal rounded-pill text-${this.info.tipe_kontrak == 'kontrak lama'?'success' : 'primary'}-emphasis">${this.info.tipe_kontrak}</span>
                    <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${this.info.jenis_layanan} - ${this.info.jenis_layanan_parent}</span>
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Pelanggan</label>
                <div class="col-auto">
                    ${this.info.pelanggan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Perusahaan</label>
                <div class="col-auto">
                    ${this.info.perusahaan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Status</label>
                <div class="col-auto">
                    ${statusFormat(this.info.jenisStatus, this.info.status)}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Jumlah kontrol</label>
                <div class="col-auto">
                    ${this.info.jmlKontrol}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Harga</label>
                <div class="col-auto">
                    ${formatRupiah(this.info.total_harga)}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Dibuat pada</label>
                <div class="col-auto">
                    ${dateFormat(this.info.created_at, 0)}
                </div>
            </div>
        `;

        return container;
    }
    createInformationPengiriman() {
        const container = document.createElement('div');
        container.className = 'container fs-7';
        
        $('#titleDetail').text('Detail Pengiriman');

        container.innerHTML = `
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">No pengiriman</label>
                <div class="col-auto">
                    ${this.info.no_pengiriman}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">No resi</label>
                <div class="col-auto">
                    ${this.info.no_resi}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">No kontrak</label>
                <div class="col-auto">
                    ${this.info.no_kontrak}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Status</label>
                <div class="col-auto">
                    ${statusFormat('pengiriman', this.info.status)}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Ekspedisi</label>
                <div class="col-auto">
                    ${this.info.ekspedisi}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Tujuan</label>
                <div class="col-auto">
                    ${this.info.tujuan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Alamat</label>
                <div class="col-md-6">
                    ${this.info.alamat}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Dibuat pada</label>
                <div class="col-auto">
                    ${dateFormat(this.info.created_at, 1)}
                </div>
            </div>
        `;

        return container;
    }
    createInformationSuratTugas() {
        const container = document.createElement('div');
        container.className = 'container fs-7';
        
        $('#titleDetail').text('Detail Surat Tugas');

        container.innerHTML = `
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">No kontrak</label>
                <div class="col-auto">
                    ${this.info.no_kontrak}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Jenis layanan</label>
                <div class="col-auto gap-1">
                    <span class="badge bg-${this.info.tipe_kontrak == 'kontrak lama'?'success' : 'primary'}-subtle fw-normal rounded-pill text-${this.info.tipe_kontrak == 'kontrak lama'?'success' : 'primary'}-emphasis">${this.info.tipe_kontrak}</span>
                    <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${this.info.jenis_layanan} - ${this.info.jenis_layanan_parent}</span>
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Pelanggan</label>
                <div class="col-auto">
                    ${this.info.pelanggan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Perusahaan</label>
                <div class="col-auto">
                    ${this.info.perusahaan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Status</label>
                <div class="col-auto">
                    ${statusFormat('penyelia', this.info.status)}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Tanggal mulai</label>
                <div class="col-auto">
                    ${dateFormat(this.info.start_date, 4)}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Tanggal selesai</label>
                <div class="col-auto">
                    ${dateFormat(this.info.end_date, 4)}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Dibuat pada</label>
                <div class="col-auto">
                    ${dateFormat(this.info.created_at, 1)}
                </div>
            </div>
        `;

        return container;
    }

    // membuat tab

    createTab() {
        const container = document.createElement('div');
        const tabs = {};
        this.options.tab.pengguna && (tabs.pengguna = { title: 'Pengguna', content: this.createPenggunaContent(), badge: this.data?.pengguna?.length ?? 0 });
        this.options.tab.activitas && (tabs.activitas = { title: 'Aktivitas', content: this.createAktivitasContent() });
        this.options.tab.periode && (tabs.periode = { title: 'Periode', content: this.createPeriodeContent() });
        this.options.tab.dokumen && (tabs.dokumen = { title: 'Dokumen', content: this.createDokumenContent() });
        this.options.tab.log && (tabs.log = { title: 'Log', content: this.createLogContent() });

        this.options.tab.items && (tabs.items = { title: 'Items', content: this.createItemsContent() });
        this.options.tab.bukti && (tabs.bukti = { title: 'Bukti', content: this.createBuktiContent() });

        this.options.tab.proses && (tabs.proses = { title: 'Proses Penyelia', content: this.createProsesContent() });
      
        let htmlTabNav = '';
      
        for (const tabId in tabs) {
          if (this.options.tab[tabId]) {
            const tab = tabs[tabId];
            const badge = tab.badge ? `<span class="badge text-bg-secondary">${tab.badge}</span>` : '';
      
            htmlTabNav += `
              <li role="presentation" class="bg-secondary-subtle rounded-3 mb-1 shadow-sm">
                <div class="link d-flex justify-content-between align-items-center py-2 px-3" id="pills-${tabId}">
                    <span>${tab.title} ${badge}</span> 
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="submenu bg-body-secondary p-2 rounded-bottom-3">
                    ${tab.content}
                </div>
              </li>
            `;
          }
        }
      
        if (htmlTabNav === '') {
          return `<div class="text-center text-muted mt-3 w-100">Tidak ada tab yang ditampilkan</div>`;
        }
      
        container.innerHTML = `
          <ul class="accordion-custom px-0 m-0" id="pills-tab" role="tablist">
            ${htmlTabNav}
          </ul>
        `;
      
        return container;
    }

    // Example content creation functions – replace with your actual logic
    createItemsContent() {
        return '<p>Items content</p>';
    }
    createPenggunaContent() { 
        console.log(this.data) ;
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
                            <div class="col-md-2 text-end ms-auto">
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
        let doc = ``;
        let dataDokumen = [];
        let invoiceData = false;
        switch (this.options.jenis) {
            case 'permohonan':
                dataDokumen = this.data.dokumen;
                invoiceData = this.data.invoice;
                break;
            case 'penyelia':
                dataDokumen = this.data.permohonan.dokumen;
                invoiceData = this.data.permohonan.invoice;
                break;
            default:
                break;
        }

        for (const [i,dokumen] of dataDokumen.entries()) {
            let idHash = false;
            if(dokumen.jenis == 'invoice'){
                idHash = invoiceData?.permohonan_hash;
            }else{
                idHash = this.data.permohonan_hash;
            }
            doc += `
                <div class="card mb-1">
                    <div class="card-body p-1 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <span class="fw-bolder">${dokumen.nama}</span>
                                <div class="text-body-secondary">
                                    <small>${dateFormat(dokumen.created_at, 4)}</small>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a type="button" class="btn btn-sm btn-outline-primary" target="_blank" href="${base_url}/laporan/${dokumen.jenis}/${idHash}">Lihat</a>
                        </div>
                    </div>
                </div>
            `;
        }

        if(doc == ``){
            doc = '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada dokumen</p>';
        }

        return doc;
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
    createItemsContent() {
        return '<p>Items content</p>';
    }
    createBuktiContent() {
        return '<p>Bukti content</p>';
    }
    createProsesContent() {
        return '<p>Proses content</p>';
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