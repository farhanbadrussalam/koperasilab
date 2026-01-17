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
                dokumen_lhu: options.tab.dokumen_lhu ?? false,
                log: options.tab.log ?? false,
                periode: options.tab.periode ?? false,
                tld: options.tab.tld ?? false,
                // Pengiriman
                items: options.tab.items ?? false,
                bukti: options.tab.bukti ?? false,
                // Penyelia
                proses: options.tab.proses ?? false,
                // Perusahaan
                alamat: options.tab.alamat ?? false,
                karyawan: options.tab.karyawan ?? false,
                surat_kuasa: options.tab.surat_kuasa ?? false
            },
            activeTab: options.activeTab ?? false
        }

        this._initializeProperties();
        this._createCustomEvents();

        if (this.options.modal) {
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
        // ketika modal ditutup
        $('#offcanvasDetail').on('hidden.bs.offcanvas', () => {
            this._initializeProperties();
            $('#container-detail').empty();
            $('#loadingDetail').empty();
        });
    }

    _actionAccordion() {
        let Accordion = function (el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

            let links = this.el.find('.link');

            links.on('click', { el: this.el, multiple: this.multiple }, this.dropdown)
        }

        Accordion.prototype.dropdown = function (e) {
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
        if (this.options.activeTab) {
            $(`#pills-${this.options.activeTab}`).click();
        }
        //  new Accordion($('#pills-tab'), false);
    }

    _actionSwiper() {
        new Swiper('.swiper-bukti-pengiriman', {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            zoom: true,
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
            pagination: {
                el: ".swiper-pagination",
            },
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
        });

        new Swiper('.swiper-bukti-penerima', {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            zoom: true,
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            navigation: { nextEl: ".swiper-button-next-penerima", prevEl: ".swiper-button-prev-penerima" },
            pagination: {
                el: ".swiper-pagination-penerima",
            },
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
        });
    }

    _initInformasi() {
        switch (this.options.jenis) {
            case 'surattugas':
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
                    jenisStatus: 'surattugas'
                };
                break;

            case 'pengiriman':
                const dataPengiriman = this.data;
                this.info = {
                    no_pengiriman: dataPengiriman.id_pengiriman ?? '-',
                    no_resi: dataPengiriman.no_resi ?? 'Belum ada',
                    ekspedisi: dataPengiriman.ekspedisi?.name ?? '-',
                    no_kontrak: dataPengiriman.kontrak?.no_kontrak ?? '-',
                    tujuan: dataPengiriman.tujuan?.name ?? '-',
                    alamat: dataPengiriman.alamat?.alamat ?? '-',
                    created_at: dataPengiriman.created_at ?? '-',
                    status: dataPengiriman.status,
                    jenisStatus: 'pengiriman'
                };
                break;

            case 'permohonan':
                this.info = {
                    no_kontrak: this.data.kontrak?.no_kontrak ?? '-',
                    tipe_kontrak: this.data.tipe_kontrak ?? '-',
                    jenis_layanan: this.data.jenis_layanan.name ?? '-',
                    jenis_layanan_parent: this.data.jenis_layanan_parent?.name ?? '-',
                    pelanggan: this.data.pelanggan?.name ?? '-',
                    perusahaan: this.data.pelanggan.perusahaan?.nama_perusahaan ?? '-',
                    status: this.data.status ?? '-',
                    jmlKontrol: this.data.jumlah_kontrol ?? 0,
                    total_harga: this.data.total_harga ?? 0,
                    created_at: this.data.created_at ?? '-',
                    periodePemakaian: this.data.periode_pemakaian ?? [],
                    periodeNow: this.data.periode ?? '',
                    layananJasa: this.data.layanan_jasa?.nama_layanan ?? '',
                    jenisTld: this.data.jenis_tld?.name ?? '',
                    jenisStatus: 'permohonan',
                    pengguna: this.data.permohonan_pengguna ?? [],
                    is_have_tld: this.data.is_have_tld ?? false,
                    is_zerocek: this.data.is_zerocek ?? false
                }
                break;

            case 'penyelia':
                this.info = {
                    no_kontrak: this.data.permohonan.kontrak?.no_kontrak ?? '-',
                    tipe_kontrak: this.data.permohonan.tipe_kontrak ?? '-',
                    jenis_layanan: this.data.permohonan.jenis_layanan.name ?? '-',
                    jenis_layanan_parent: this.data.permohonan.jenis_layanan_parent?.name ?? '-',
                    pelanggan: this.data.permohonan.pelanggan?.name ?? '-',
                    perusahaan: this.data.permohonan.pelanggan.perusahaan?.nama_perusahaan ?? '-',
                    status: this.data.status ?? '-',
                    jmlKontrol: this.data.permohonan.jumlah_kontrol ?? 0,
                    total_harga: this.data.permohonan.total_harga ?? 0,
                    created_at: this.data.permohonan.created_at ?? '-',
                    periodePemakaian: this.data.permohonan.periode_pemakaian ?? [],
                    periodeNow: this.data.permohonan.periode ?? '',
                    layananJasa: this.data.permohonan.layanan_jasa?.nama_layanan ?? '',
                    jenisTld: this.data.permohonan.jenis_tld?.name ?? '',
                    jenisStatus: 'penyelia'
                }
                break;

            case 'kontrak':
                this.info = {
                    no_kontrak: this.data.no_kontrak ?? '-',
                    tipe_kontrak: this.data.tipe_kontrak ?? '-',
                    jenis_layanan: this.data.jenis_layanan.name ?? '-',
                    jenis_layanan_parent: this.data.jenis_layanan_parent?.name ?? '-',
                    pelanggan: this.data.pelanggan?.name ?? '-',
                    perusahaan: this.data.pelanggan.perusahaan?.nama_perusahaan ?? '-',
                    status: this.data.status ?? '-',
                    jmlKontrol: this.data.jumlah_kontrol ?? 0,
                    total_harga: this.data.total_harga ?? 0,
                    created_at: this.data.created_at ?? '-',
                    periodePemakaian: this.data.periode_pemakaian ?? [],
                    periodeNow: this.data.periode ?? '',
                    layananJasa: this.data.layanan_jasa?.nama_layanan ?? '',
                    jenisTld: this.data.jenis_tld?.name ?? '',
                    jenisStatus: 'kontrak'
                }
                break;
            case 'perusahaan':
                this.info = {
                    nama_perusahaan: this.data.nama_perusahaan ?? '-',
                    kode_perusahaan: this.data.kode_perusahaan ?? 'Belum memiliki Kode',
                    npwp_perusahaan: this.data.npwp_perusahaan ?? '-',
                    email: this.data.email ?? '-',
                    alamat: this.data.alamat ?? [],
                    jenisStatus: 'perusahaan',
                    karyawan: this.data.users ?? [],
                    suratkuasa: this.data?.suratkuasa ?? [],
                    id: this.data.perusahaan_hash
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
                case 'perusahaan':
                    $('#container-detail').append(this.createInformationPerusahaan());
                    break;
                case 'history_pic':
                    $('#container-detail').append(this.createInformationHistoryPic());
                    break;
                case 'kontrak':
                    $('#container-detail').append(this.createInformationKontrak());
                    break;
                default:
                    $('#container-detail').append(this.createInformationPermohonan());
                    break;
            }
        }

        $('#container-detail').append('<hr/>');

        const hasTab = Object.values(this.options.tab).some(tab => tab);
        if (!hasTab) {
            // $('#container-detail').append(`<div class="text-center text-muted mt-3 w-100">Tidak ada tab yang ditampilkan</div>`);
            $('#container-detail').append(``);
        } else {
            $('#container-detail').append(this.createTab());
            showPopupReload();
            this._actionAccordion();
            this._actionSwiper();
        }
    }

    show(url) {
        $('#offcanvasDetail').offcanvas('show');
        this.loadDataAjax(url);
    }

    loadDataAjax(url) {
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

        $('#titleDetail').text(`${this.info.layananJasa} - ${this.info.jenisTld}`);

        let htmlPeriode = !this.info.periodeNow ? `Zero cek` : 'Periode ' + this.info.periodeNow;
        if(this.info.periodeNow && this.info.is_have_tld && this.info.is_zerocek) {
            htmlPeriode += ' + Zero cek';
        }
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
                    <span class="badge bg-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-subtle fw-normal rounded-pill text-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-emphasis">${this.info.tipe_kontrak}</span>
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
                <label class="text-body-tertiary mb-1 col-md-4">Periode</label>
                <div class="col-auto">
                    ${htmlPeriode}
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
                    <span class="badge bg-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-subtle fw-normal rounded-pill text-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-emphasis">${this.info.tipe_kontrak}</span>
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
    createInformationPerusahaan() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $('#titleDetail').text('Detail Perusahaan');

        container.innerHTML = `
            <div class="row mb-2">
                <input type="hidden" name="id_perusahaan" id="detail_id_hash" value="${this.info.id}">
                <label class="text-body-tertiary mb-1 col-md-4">Nama</label>
                <div class="col-auto">
                    ${this.info.nama_perusahaan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">Kode perusahaan</label>
                <div class="col-auto">
                    ${this.info.kode_perusahaan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">NPWP</label>
                <div class="col-auto">
                    ${this.info.npwp_perusahaan}
                </div>
            </div>
            <div class="row mb-2">
                <label class="text-body-tertiary mb-1 col-md-4">E-mail</label>
                <div class="col-auto">
                    ${this.info.email}
                </div>
            </div>
        `;

        return container;
    }
    createInformationHistoryPic() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        let findPic = this.data.find(pic => pic.status == 1);
        let html = ``;
        let createLI = function(pic){
            return `
                <div class="d-flex mb-2 position-relative" style="z-index: 1;">
                    <div class="me-3">
                        ${pic.status == 1 ? `
                            <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center shadow-sm border border-4 border-white"
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        ` : `
                            <div class="rounded-circle bg-white border border-2 border-secondary d-flex justify-content-center align-items-center mt-2 mx-auto"
                                style="width: 15px; height: 15px;">
                            </div>
                        `}
                    </div>
                    <div class="flex-grow-1">
                        <div class="card shadow-sm card-hover ${ pic.status == 1 ? 'border-0 bg-primary-subtle' : 'border-1 bg-white' } mb-1">
                            <div class="card-body p-3 py-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        ${pic.status == 1 ? `
                                            <small class="text-primary fw-bold text-uppercase" style="font-size: 0.7rem;">Mulai Menjabat</small>
                                            <div class="text-dark fw-bold" style="font-size: 0.9rem;">
                                                ${dateFormat(pic.created_at, 4)}
                                            </div>
                                        ` : `
                                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Berakhir pada</small>
                                            <div class="text-secondary fw-bold" style="font-size: 0.9rem;">
                                                ${dateFormat(pic.selesai_at, 4)}
                                            </div>
                                        `}
                                    </div>

                                    ${pic.status == 1 ? `<span class="badge bg-primary rounded-pill">PIC Saat Ini</span>` : ''}
                                </div>

                                <div class="d-flex align-items-center mt-2">
                                    <div class="rounded-circle ${ pic.status == 1 ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' } d-flex justify-content-center align-items-center me-3 fw-bold"
                                        style="width: 35px; height: 35px; min-width: 35px;">
                                        ${ pic.name.charAt(0).toUpperCase() }
                                    </div>

                                    <div style="overflow: hidden;">
                                        <h6 class="mb-0 fw-bold ${ pic.status == 1 ? 'text-primary' : 'text-dark' } text-truncate">
                                            ${ pic.name }
                                        </h6>
                                        <div class="d-flex align-items-center text-muted small mt-1">
                                            <i class="bi bi-envelope me-2"></i>
                                            <span class="text-truncate">${ pic.email }</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        if(findPic){
            html += createLI(findPic);
        }
        for (const pic of this.data) {
            if(pic.status != 1) {
                html += createLI(pic);
            }
        }

        $('#titleDetail').text('History PIC');

        container.innerHTML = `
            <div class="timeline">
                ${html}
            </div>
        `;

        return container;
    }
    createInformationKontrak() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $('#titleDetail').text('Informasi Kontrak');

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
                    <span class="badge bg-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-subtle fw-normal rounded-pill text-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-emphasis">${this.info.tipe_kontrak}</span>
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

    // membuat tab

    createTab() {
        const container = document.createElement('div');
        const tabs = {};
        this.options.tab.pengguna && (tabs.pengguna = { title: 'Pengguna', content: this.createPenggunaContent(), badge: this.data?.pengguna?.length ?? 0, icon: 'bi bi-person-circle' });
        this.options.tab.activitas && (tabs.activitas = { title: 'Aktivitas', content: this.createAktivitasContent(), icon: 'bi bi-activity' });
        this.options.tab.periode && (tabs.periode = { title: 'Periode', content: this.createPeriodeContent(), icon: 'bi bi-calendar-check' });
        this.options.tab.dokumen && (tabs.dokumen = { title: 'Dokumen', content: this.createDokumenContent(), icon: 'bi bi-folder2-open' });
        this.options.tab.dokumen_lhu && (tabs.dokumen_lhu = { title: 'Dokumen LHU', content: this.createDokumenLhuContent(), icon: 'bi bi-file-earmark-text' });
        this.options.tab.log && (tabs.log = { title: 'Log', content: this.createLogContent(), icon: 'bi bi-journal-text' });
        this.options.tab.tld && (tabs.tld = { title: 'TLD', content: this.createTldContent(), icon: 'bi bi-diagram-3' });

        this.options.tab.items && (tabs.items = { title: 'Items', content: this.createItemsContent(), icon: 'bi bi-box-seam' });
        this.options.tab.bukti && (tabs.bukti = { title: 'Bukti', content: this.createBuktiContent(), icon: 'bi bi-check2-square', maxHeight: '50vh' });

        this.options.tab.proses && (tabs.proses = { title: 'Proses Penyelia', content: this.createProsesContent(), icon: 'bi bi-gear-wide-connected' });

        this.options.tab.alamat && (tabs.alamat = { title: 'Alamat', content: this.createAlamatContent(), icon: 'bi bi-geo-alt' });
        this.options.tab.karyawan && (tabs.karyawan = { title: `PIC (${this.info?.karyawan?.length ?? 0})`, content: this.createKaryawanContent(), icon: 'bi bi-people-fill' });
        this.options.tab.surat_kuasa && (tabs.surat_kuasa = { title: 'Surat Kuasa', content: this.createSuratKuasaContent(), icon: 'bi bi-file-earmark-text' });

        let htmlTabNav = '';

        for (const tabId in tabs) {
            if (this.options.tab[tabId]) {
                const tab = tabs[tabId];
                const badge = tab.badge ? `<span class="badge text-bg-secondary">${tab.badge}</span>` : '';

                htmlTabNav += `
                <li role="presentation" class="rounded-3 mb-1 shadow-sm border">
                    <div class="link d-flex justify-content-between align-items-center py-2 px-3 hover-3" id="pills-${tabId}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="${tab.icon}"></span>
                            <span>${tab.title} ${badge}</span>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="submenu p-2 rounded-bottom-3 overflow-auto overflow-x-hidden border-top" style="max-height: ${tab.maxHeight ? tab.maxHeight : '30vh'}">
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
        if (this.info.pengguna && this.info.pengguna.length > 0) {
            let html = '';
            for (const [i, item] of this.info.pengguna.entries()) {
                let txtRadiasi = '';
                const pengguna = item.pengguna;
                pengguna.radiasi?.map(d => txtRadiasi += `<span class="badge rounded-pill text-bg-secondary me-1 mb-1">${d.nama_radiasi}</span>`);

                let btnMedia = '';
                if(pengguna.media_ktp){
                    btnMedia = `
                        <a class="btn btn-sm btn-outline-secondary show-popup-image" href="${base_url}/storage/${pengguna.media_ktp.file_path}/${pengguna.media_ktp.file_hash}" title="Show ktp"><i class="bi bi-file-person-fill"></i></a>
                    `;
                }

                html += `
                    <div class="card border-bottom border-0 fs-8 mb-1 hover-3">
                        <div class="card-body row align-items-center py-1">
                            <div class="col-md-10 lh-sm d-flex align-items-center">
                                <span class="col-form-label me-2">${i + 1}</span>
                                <div class="mx-2">
                                    <div>${pengguna.name}</div>
                                    <small class="text-body-secondary fw-light">${pengguna.divisi?.name ?? '-'}</small>
                                    <div class="d-flex flex-wrap">
                                        ${txtRadiasi}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-end ms-auto">
                                ${btnMedia}
                            </div>
                        </div>
                    </div>
                `;
            }
            return html;
        } else {
            return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada data pengguna</p>';
        }
    }
    createAktivitasContent() {
        return '<p>Aktivitas content</p>';
    }
    createDokumenContent() {
        let doc = ``;
        let dataDokumen = [];
        let invoiceData = false;
        let dataPermohonan = false;
        switch (this.options.jenis) {
            case 'permohonan':
                this.data.kontrak?.document_kontrak && (dataDokumen = this.data.kontrak.document_kontrak);
                dataDokumen = dataDokumen.concat(this.data.dokumen);
                invoiceData = this.data.invoice;
                dataPermohonan = this.data;
                break;
            case 'penyelia':
                this.data.permohonan.kontrak?.document_kontrak && (dataDokumen = this.data.permohonan.kontrak.document_kontrak);
                dataDokumen = dataDokumen.concat(this.data.permohonan.dokumen);
                invoiceData = this.data.permohonan.invoice;
                dataPermohonan = this.data.permohonan;
                break;
            default:
                break;
        }
        let exceptDoc = [];
        if(role.includes('Pelanggan')){
            exceptDoc = [
                'surattugas'
            ];
        }
        for (const [i, dokumen] of dataDokumen.entries()) {
            let idHash = false;
            if(exceptDoc.includes(dokumen.jenis)) continue;
            switch (dokumen.jenis) {
                case 'invoice':
                    idHash = invoiceData?.keuangan_hash;
                    break;
                case 'kontrak':
                    idHash = dataPermohonan.kontrak.kontrak_hash;
                    break;
                case 'KontrakPengujian':
                    idHash = dataPermohonan.kontrak.kontrak_hash;
                    break;
                default:
                    idHash = dataPermohonan.permohonan_hash;
                    break;
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

        if (doc == ``) {
            doc = '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada dokumen</p>';
        }

        return doc;
    }

    createDokumenLhuContent() {
        if(!this.data.media) return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada dokumen</p>';

        return printMedia(this.data.media, false, { size: true, date: true, isHtml: true });
    }
    createLogContent() {
        const dataLog = [];
        switch (this.options.jenis) {
            case 'penyelia':
            case 'surattugas':
                this.data.logs?.forEach(log => {
                    dataLog.push({
                        message: log.description,
                        created_at: log.created_at
                    });
                });

                this.data.penyelia_map?.forEach(mapItem => {
                    mapItem.logs?.forEach(log => {
                        if (['created', 'start'].includes(log.description)) return;

                        const message = log.description === 'finish'
                            ? `Proses ${mapItem.jobs.name} selesai`
                            : '';

                        dataLog.push({
                            message,
                            created_at: log.created_at,
                            user: logs.causer?.name,
                            note: mapItem.note
                        });
                    });
                });

                // order by created_at
                dataLog.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
            default:
                this.data.logs?.forEach(log => {
                    let properties = JSON.parse(log.properties);

                    let note = properties?.note || '';
                    dataLog.push({
                        message: log.description,
                        created_at: log.created_at,
                        note: note
                    });
                });
                break;
        }
        let htmlPointLog = ``;
        dataLog.map((log, i) => {
            htmlPointLog += `
                <div class="tl-item ${i == 0 ? 'active' : ''}">
                    <div class="tl-dot border-primary"></div>
                    <div class="tl-content lh-1 w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-2 w-50">
                                ${log.message}
                                <div class="fw-bold mt-1">${log.user || ''}</div>
                                ${log.note ? `<div class="text-muted mt-1">Note : ${log.note}</div>` : ''}
                            </div>
                            <div class="text-muted text-end">${dateFormat(log.created_at, 1)}</div>
                        </div>
                    </div>
                </div>
            `
        });

        if(dataLog.length == 0){
            return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada log</p>';
        }

        return `
            <div class="timeline">
                ${htmlPointLog}
            </div>
        `;
    }
    createPeriodeContent() {
        let htmlPeriode = '';
        let data = this.data;
        if (this.data.tipe_kontrak == 'kontrak lama') {
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
        } else {
            for (const [i, periode] of data.periode_pemakaian.entries()) {
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
        let data = this.data;
        let rincian = data.detail;
        let list = ``;
        for (const detail of rincian) {
            let htmlPeriode = !detail.periode ? 'Zero cek' : `Periode ${detail.periode}`;

            list += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-uppercase">${detail.jenis}</h6>
                        <small class="text-muted">${htmlPeriode}</small>
                    </div>
                    ${detail.list_tld?.length > 0 ? `
                        <span class="badge bg-primary rounded-pill">${detail.list_tld.length} TLD</span>
                    ` : ''}
                </li>
            `;
        }
        return `
            <ul class="list-group list-group-flush">
                ${list}
            </ul>
        `;
    }
    createBuktiContent() {
        let mediaPengiriman = this.data.media_pengiriman ?? [];
        let mediaPenerima = this.data.media_penerima ?? [];

        let swiperPengiriman = ``;
        for (const media of mediaPengiriman) {
            swiperPengiriman += `
                <div class="swiper-slide">
                    <div class="swiper-zoom-container p-2">
                        <img src="${base_url}/storage/${media.file_path}/${media.file_hash}" class="object-fit-cover rounded" alt="Bukti Kirim">
                    </div>
                </div>
            `;
        }

        let swiperPenerima = ``;
        for (const media of mediaPenerima) {
            swiperPenerima += `
                <div class="swiper-slide">
                    <div class="swiper-zoom-container p-2">
                        <img src="${base_url}/storage/${media.file_path}/${media.file_hash}" class="object-fit-cover rounded" alt="Bukti Kirim">
                    </div>
                </div>
            `;
        }

        return `
            <div class="card-body">
                <ul class="nav nav-tabs nav-fill mb-3" id="buktiTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pengiriman-tab" data-bs-toggle="tab" data-bs-target="#pengiriman" type="button" role="tab">
                            <i class="bi bi-box-seam me-1"></i> Pengiriman
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="penerimaan-tab" data-bs-toggle="tab" data-bs-target="#penerimaan" type="button" role="tab">
                            <i class="bi bi-check2-circle me-1"></i> Penerimaan
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="buktiTabContent">

                    <div class="tab-pane fade show active" id="pengiriman" role="tabpanel">
                        ${mediaPengiriman.length > 0 ? `
                            <div class="swiper swiper-bukti-pengiriman rounded" style="width: 100%; height: 200px;">
                                <div class="swiper-wrapper">
                                    ${swiperPengiriman}
                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-pagination"></div>
                            </div>
                        ` : `
                            <p class="text-muted text-center">Tidak ada bukti pengiriman</p>
                        `}
                    </div>

                    <div class="tab-pane fade" id="penerimaan" role="tabpanel">
                        ${mediaPenerima.length > 0 ? `
                            <div class="swiper swiper-bukti-penerima rounded" style="width: 100%; height: 200px;">
                                <div class="swiper-wrapper">
                                    ${swiperPenerima}
                                </div>
                                <div class="swiper-button-next-penerima"></div>
                                <div class="swiper-button-prev-penerima"></div>
                                <div class="swiper-pagination-penerima"></div>
                            </div>
                        ` : `
                            <p class="text-muted text-center">Tidak ada bukti penerima</p>
                        `}
                    </div>

                </div>
            </div>
        `;
    }
    createProsesContent() {
        return '<p>Proses content</p>';
    }
    createAlamatContent() {
        const alamatarr = this.info?.alamat ?? [];
        if(alamatarr.length == 0) return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada alamat</p>';

        return `
            <ul class="list-group list-group-flush">
                ${alamatarr.map((data, i) => {
                    if(data.status == 1){
                        return `
                            <li class="list-group-item">
                                <div class="fw-bold">${data.jenis}</div>
                                <div class="text-body-secondary">${data.alamat ?? '-'}</div>
                            </li>
                        `;
                    }
                }).join('')}
            </ul>
        `;
    }
    createKaryawanContent() {
        const karyawanarr = this.info?.karyawan ?? [];
        if(karyawanarr.length == 0) return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada karyawan</p>';

        karyawanarr.sort((a, b) => {
            if (a.status > b.status) return 1;
            if (a.status < b.status) return -1;
            return 0;
        });

        return `
            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#add-modal-pic"><span class="bi bi-plus"></span> Ganti pic</button>
            <ul class="list-group list-group-flush rounded mt-2">
                ${karyawanarr.map((data, i) => {
                    let docSuratKuasa = '';
                    if(data.profile?.suratkuasa){
                        let urlDocumen = `${base_url}/storage/${data.profile.suratkuasa.file_path}/${data.profile.suratkuasa.file_hash}`;
                        docSuratKuasa = `<a type="button" class="btn btn-sm btn-outline-info" href="${urlDocumen}" target="_blank" title="Dokumen Surat Kuasa"><span class="bi bi-file-earmark-text"></span></a>`;
                    }

                    let htmlTglKeluar = '';
                    if(data.selesai_at){
                        htmlTglKeluar = `| Tgl keluar : ${dateFormat(data.selesai_at, 4)}`;
                    }

                    return `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">${data.name} ${data.status == 1 ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Tidak aktif</span>'}</div>
                                <div><small class="text-body-secondary">${data.email}</small></div>
                                <small>Tgl masuk : ${dateFormat(data.created_at, 4)} ${htmlTglKeluar}</small>
                            </div>
                            ${docSuratKuasa}
                        </li>
                    `;
                }).join('')}
            </ul>
        `;
    }
    createSuratKuasaContent() {
        let dokumen = this.info.suratkuasa;
        if(dokumen.length > 0){
            return `
                <div class="card mb-1">
                    <div class="card-body p-1 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <span class="fw-bolder">${dokumen[0].file_ori}</span>
                                <div class="text-body-secondary">
                                    <small>${dateFormat(dokumen[0].created_at, 4)}</small>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a type="button" class="btn btn-sm btn-outline-primary" target="_blank" href="${base_url}/storage/${dokumen[0].file_path}/${dokumen[0].file_hash}">Lihat</a>
                        </div>
                    </div>
                </div>
            `;
        } else {
            return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada surat kuasa</p>';
        }
    }
    createTldContent() {
        let listTld = [];
        let tldKontrol = false;
        let tldPengguna = false;
        switch (this.options.jenis) {
            case 'permohonan':
                listTld = this.data.rincian_list_tld ?? [];
            case 'kontrak':
                // tldKontrol = this.data.tld_kontrol ?? false;
                // tldPengguna = this.data.pengguna.some(pengguna => pengguna.tld_pengguna) ? this.data.pengguna.map(pengguna => pengguna.tld_pengguna ? { name: pengguna.nama, ...pengguna.tld_pengguna } : false) : false;
                break;
            case 'penyelia':
                listTld = this.data.permohonan.rincian_list_tld ?? [];
                break;

            default:
                break;
        }

        if (listTld.length > 0) {
            let htmlPengguna = '';
            let htmlKontrol = '';

            let arrPengguna = listTld.filter(d => d.pengguna);
            let arrKontrol = listTld.filter(d => !d.pengguna);

            let i = 1;
            for (const kontrol of arrKontrol) {
                if(kontrol.tld){
                    for (const tld of kontrol.tld) {
                        htmlKontrol += `
                            <li class="list-group-item d-flex justify-content-between">
                                <div>
                                    <span>${i}. </span>
                                    <span>${'TLD Kontrol'}</span>
                                </div>
                                <span>${tld.no_seri_tld}</span>
                            </li>
                        `;
                        i++;
                    }
                }
            }

            for (const pengguna of arrPengguna) {
                if(pengguna.tld){
                    for (const tld of pengguna.tld) {
                        htmlPengguna += `
                            <li class="list-group-item d-flex justify-content-between">
                                <div>
                                    <span>${i}. </span>
                                    <span>${pengguna.pengguna.name}</span>
                                </div>
                                <span>${tld.no_seri_tld}</span>
                            </li>
                        `;
                        i++;
                    }
                }
            }

            if(htmlKontrol == '' && htmlPengguna == '') {
                return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada TLD</p>';
            } else {
                return `
                    <ul class="list-group list-group-flush">
                        ${htmlKontrol}
                        ${htmlPengguna}
                    </ul>
                `;
            }
        }
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

    on(eventName, callback = () => { }) {
        return document.addEventListener(eventName, callback);
    }

    destroy() {
        if (this.options.modal) {
            $('#offcanvasDetail').remove();
        }
    }
}
