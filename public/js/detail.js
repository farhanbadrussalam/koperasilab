class Detail {
    constructor(options = {}) {
        this.options = {
            id: options.id ?? 'offcanvasDetail',
            modal: options.modal ?? true,
            information: options.information ?? true,
            jenis: options.jenis ?? 'permohonan',
            tab: {
                pengguna: options.tab?.pengguna ?? false,
                activitas: options.tab?.activitas ?? false,
                dokumen: options.tab?.dokumen ?? false,
                dokumen_lhu: options.tab?.dokumen_lhu ?? false,
                log: options.tab?.log ?? false,
                periode: options.tab?.periode ?? false,
                tld: options.tab?.tld ?? false,
                // Pengiriman
                items: options.tab?.items ?? false,
                bukti: options.tab?.bukti ?? false,
                // Penyelia
                proses: options.tab?.proses ?? false,
                // Perusahaan
                alamat: options.tab?.alamat ?? false,
                karyawan: options.tab?.karyawan ?? false,
                surat_kuasa: options.tab?.surat_kuasa ?? false
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

        // Log pagination variables
        this.logPage = 1;
        this.logLoading = false;
        this.hasMoreLogs = false;
        this.logUrl = null;
    }

    _createCustomEvents() {
        this.eventSimpan = new CustomEvent('detail.simpan', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
        // ketika modal ditutup
        $(`#${this.options.id}`).on('hidden.bs.offcanvas', () => {
            this._initializeProperties();
            $(`#${this.options.id}-container`).empty();
            $(`#${this.options.id}-loading`).empty();
        });

        $(`#${this.options.id}`).on('click', '.btn-lihat-dokumen', (e) => {
            e.preventDefault();
            const url = $(e.currentTarget).data('url');
            const title = $(e.currentTarget).data('title') || 'Dokumen';
            
            if ($(`#${this.options.id}`).hasClass('offcanvas')) {
                $(`#${this.options.id}`).offcanvas('hide');
            } else {
                $(`#${this.options.id}`).modal('hide');
            }
            
            if (!window.modalDocDetail) {
                window.modalDocDetail = new ModalDocument({ id: 'modalDocDetail' });
            }
            window.modalDocDetail.show(url, { title: title });
        });

        // Bind scroll listener for log tab infinite pagination
        $(`#${this.options.id}`).on('scroll', '#submenu-log', (e) => {
            if (this.options.jenis === 'tld' && this.options.tab.log) {
                const container = $(e.currentTarget);
                if (container.scrollTop() + container.innerHeight() >= container[0].scrollHeight - 20) {
                    if (this.hasMoreLogs && !this.logLoading) {
                        this.loadNextLogPage();
                    }
                }
            }
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

        let accordion = new Accordion($(`#${this.options.id}-pills-tab`), false);
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
                    perusahaan: this.data.pelanggan?.perusahaan?.nama_perusahaan ?? '-',
                    status: this.data.status ?? '-',
                    jmlKontrol: this.data.jumlah_kontrol ?? 0,
                    total_harga: this.data.total_harga ?? 0,
                    created_at: this.data.created_at ?? '-',
                    periodePemakaian: this.data.periode_pemakaian ?? [],
                    periodeNow: this.data.periode ?? '',
                    layananJasa: this.data.layanan_jasa?.nama_layanan ?? '',
                    jenisTld: this.data.jenis_tld?.name ?? '',
                    jenisStatus: 'permohonan',
                    detail: this.data.permohonan_detail ?? [],
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
                    jenisStatus: 'penyelia',
                    detail: this.data.permohonan.permohonan_detail ?? [],
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
                    jenisStatus: 'kontrak',
                    detail: this.data.kontrak_detail ?? [],
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
            case 'tld':
                this.info = {
                    no_seri_tld: this.data.no_seri_tld ?? '-',
                    merk: this.data.merk ?? '-',
                    jenis: this.data.jenis ?? '-',
                    status: this.data.status ?? 0,
                    digunakan: this.data.digunakan ?? null,
                    pemilik: this.data.pemilik?.nama_perusahaan ?? 'Internal Koperasi',
                    tanggal_pengadaan: this.data.tanggal_pengadaan ?? '-',
                    jenisStatus: 'tld',
                    current_user: this.data.current_assignment?.entitas?.name ?? '-',
                    current_user_type: this.data.current_assignment?.jenis ?? null,
                    current_contract: this.data.current_assignment?.permohonan?.kontrak?.no_kontrak ?? this.data.digunakan ?? '-',
                    logs: this.data.combined_logs ?? []
                };
                break;
            default:

                break;
        }
    }

    addData(data) {
        this.data = data;
    }

    loadData() {
        $(`#${this.options.id}-container`).empty();

        if (this.options.information) {
            this._initInformasi();

            switch (this.options.jenis) {
                case 'pengiriman':
                    $(`#${this.options.id}-container`).append(this.createInformationPengiriman());
                    break;
                case 'surattugas':
                    $(`#${this.options.id}-container`).append(this.createInformationSuratTugas());
                    break;
                case 'perusahaan':
                    $(`#${this.options.id}-container`).append(this.createInformationPerusahaan());
                    break;
                case 'history_pic':
                    $(`#${this.options.id}-container`).append(this.createInformationHistoryPic());
                    break;
                case 'kontrak':
                    $(`#${this.options.id}-container`).append(this.createInformationKontrak());
                    break;
                case 'tld':
                    $(`#${this.options.id}-container`).append(this.createInformationTld());
                    break;
                case 'history_note':
                    $(`#${this.options.id}-container`).append(this.createInformationHistoryNote());
                    break;
                default:
                    $(`#${this.options.id}-container`).append(this.createInformationPermohonan());
                    break;
            }
        }


        const hasTab = Object.values(this.options.tab).some(tab => tab);
        if (!hasTab) {
            // $('#container-detail').append(`<div class="text-center text-muted mt-3 w-100">Tidak ada tab yang ditampilkan</div>`);
            $(`#${this.options.id}-container`).append(``);
        } else {
            $(`#${this.options.id}-container`).append('<hr/>');
            $(`#${this.options.id}-container`).append(this.createTab());
            showPopupReload();
            this._actionAccordion();
            this._actionSwiper();
        }
    }

    show(url, params = {}) {
        $(`#${this.options.id}`).offcanvas('show');
        this.loadDataAjax(url, params);
    }

    _renderSkeleton() {
        if (this.options.jenis == 'history_pic') {
            const skeletonItem = `
                <div class="tl-item">
                    <div class="tl-dot border-secondary-subtle"></div>
                    <div class="tl-content w-100">
                        <div class="card shadow-sm border-0 mb-3 overflow-hidden">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="rounded-circle placeholder bg-light me-3" style="width: 42px; height: 42px;"></div>
                                        <div class="flex-grow-1">
                                            <span class="placeholder col-6 d-block mb-1"></span>
                                            <span class="placeholder col-4 d-block" style="height: 0.75rem;"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-2 rounded bg-light border border-light">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-auto me-2">
                                            <div class="rounded-circle placeholder bg-white" style="width: 28px; height: 28px;"></div>
                                        </div>
                                        <div class="col">
                                            <span class="placeholder col-3 d-block mb-1" style="height: 0.6rem;"></span>
                                            <span class="placeholder col-5 d-block"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            return `
                <div class="placeholder-glow container fs-7">
                    <div class="timeline ps-2">
                        ${skeletonItem}
                        ${skeletonItem}
                        ${skeletonItem}
                        ${skeletonItem}
                        ${skeletonItem}
                    </div>
                </div>
            `;
        }
        return `
            <div class="placeholder-glow container fs-7">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="placeholder col-5 mb-1 bg-light"></span>
                        <span class="placeholder col-10 d-block py-2 rounded"></span>
                    </div>
                    <div class="col-md-6">
                        <span class="placeholder col-4 mb-1 bg-light"></span>
                        <span class="placeholder col-8 d-block py-2 rounded"></span>
                    </div>
                    <div class="col-12">
                        <span class="placeholder col-3 mb-1 bg-light"></span>
                        <div class="d-flex gap-1">
                            <span class="placeholder col-2 py-2 rounded-pill"></span>
                            <span class="placeholder col-4 py-2 rounded-pill"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span class="placeholder col-4 mb-1 bg-light"></span>
                        <span class="placeholder col-9 d-block py-2 rounded"></span>
                    </div>
                    <div class="col-md-6">
                        <span class="placeholder col-4 mb-1 bg-light"></span>
                        <span class="placeholder col-7 d-block py-2 rounded"></span>
                    </div>
                    <div class="col-md-6">
                        <span class="placeholder col-5 mb-1 bg-light"></span>
                        <span class="placeholder col-8 d-block py-2 rounded"></span>
                    </div>
                    <div class="col-md-6">
                        <span class="placeholder col-4 mb-1 bg-light"></span>
                        <span class="placeholder col-6 d-block py-2 rounded"></span>
                    </div>
                </div>
                <hr class="my-4 opacity-25">
                <div class="mt-3">
                    <div class="placeholder col-12 mb-2 rounded-3" style="height: 48px;"></div>
                    <div class="placeholder col-12 mb-2 rounded-3" style="height: 48px;"></div>
                    <div class="placeholder col-12 mb-2 rounded-3" style="height: 48px;"></div>
                </div>
            </div>
        `;
    }

    loadDataAjax(url, params = {}) {
        $(`#${this.options.id}-title`).text('Detail');
        $(`#${this.options.id}-loading`).hide();
        $(`#${this.options.id}-container`).html(this._renderSkeleton());
        $(`#${this.options.id}-main`).show();

        this.logUrl = url;
        this.logPage = 1;
        this.logLoading = false;
        this.hasMoreLogs = false;

        ajaxGet(url, params, result => {
            this.addData(result.data);
            this.hasMoreLogs = result.data.has_more_logs ?? false;
            this.loadData();
        }, error => {
            $(`#${this.options.id}-container`).html(`
                <div class="text-center p-5">
                    <i class="bi bi-exclamation-triangle text-danger display-4"></i>
                    <p class="mt-2 text-muted">Gagal memuat data detail.</p>
                </div>
            `);
        });
    }

    loadNextLogPage() {
        if (this.logLoading || !this.hasMoreLogs) {
            return;
        }

        this.logLoading = true;

        // Append a small loading spinner at the bottom of the log timeline
        $('#submenu-log .timeline').append(`
            <div id="log-mini-spinner" class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </div>
        `);

        // Scroll slightly to make sure the spinner is visible
        let submenuLog = $('#submenu-log');
        submenuLog.scrollTop(submenuLog[0].scrollHeight);

        ajaxGet(this.logUrl, { page: this.logPage + 1 }, result => {
            $('#log-mini-spinner').remove();
            this.logLoading = false;

            if (result.meta.code === 200 && result.data.combined_logs) {
                let htmlPointLog = '';
                result.data.combined_logs.forEach((log, i) => {
                    htmlPointLog += `
                        <div class="tl-item">
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
                    `;
                });

                $('#submenu-log .timeline').append(htmlPointLog);

                this.hasMoreLogs = result.data.has_more_logs ?? false;
                if (result.data.combined_logs.length > 0) {
                    this.logPage++;
                }
            }
        }, error => {
            this.logLoading = false;
            $('#log-mini-spinner').remove();
        });
    }

    // membuat informasi
    createInformationPermohonan() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $(`#${this.options.id}-title`).text(`${this.info.layananJasa} - ${this.info.jenisTld}`);

        let htmlPeriode = !this.info.periodeNow ? `Zero Check` : 'Periode ' + this.info.periodeNow;
        if (this.info.periodeNow && this.info.is_have_tld && this.info.is_zerocek) {
            htmlPeriode += ' + Zero Check';
        }
        container.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-hash me-1"></i>No Kontrak</label>
                    <div class="text-dark fw-semibold text-break">${this.info.no_kontrak}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-info-circle me-1"></i>Status</label>
                    <div>${statusFormat(this.info.jenisStatus, this.info.status)}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-layers me-1"></i>Jenis Layanan</label>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-subtle fw-normal rounded-pill text-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-emphasis">${this.info.tipe_kontrak}</span>
                        <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${this.info.jenis_layanan} - ${this.info.jenis_layanan_parent}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-person me-1"></i>Pelanggan</label>
                    <div class="text-dark fw-semibold text-break">${this.info.pelanggan}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-building me-1"></i>Perusahaan</label>
                    <div class="text-dark fw-semibold text-break">${this.info.perusahaan}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-calendar-event me-1"></i>Periode</label>
                    <div class="text-dark fw-semibold">${htmlPeriode}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-tags me-1"></i>Harga</label>
                    <div class="text-primary fw-bold">${formatRupiah(this.info.total_harga)}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-clock-history me-1"></i>Dibuat Pada</label>
                    <div class="text-muted small">${dateFormat(this.info.created_at, 0)}</div>
                </div>
            </div>
        `;

        return container;
    }
    createInformationPengiriman() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $(`#${this.options.id}-title`).text('Detail Pengiriman');

        container.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-hash me-1"></i>ID Pengiriman</label>
                    <div class="text-dark fw-semibold text-break">${this.info.no_pengiriman}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-truck me-1"></i>No Resi</label>
                    <div class="text-primary fw-bold text-break">${this.info.no_resi}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-info-circle me-1"></i>Status</label>
                    <div>${statusFormat('pengiriman', this.info.status)}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-mailbox me-1"></i>Ekspedisi</label>
                    <div class="text-dark fw-semibold">${this.info.ekspedisi}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-hash me-1"></i>No Kontrak</label>
                    <div class="text-dark fw-semibold text-break">${this.info.no_kontrak}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-geo-alt me-1"></i>Tujuan</label>
                    <div class="text-dark fw-semibold">${this.info.tujuan}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-clock-history me-1"></i>Dibuat Pada</label>
                    <div class="text-muted small">${dateFormat(this.info.created_at, 1)}</div>
                </div>
                <div class="col-12">
                    <div class="bg-light p-2 rounded border-start border-3 border-primary">
                        <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-geo-alt me-1"></i>Alamat Lengkap</label>
                        <div class="text-dark small text-break">${this.info.alamat}</div>
                    </div>
                </div>
            </div>
        `;

        return container;
    }
    createInformationSuratTugas() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $(`#${this.options.id}-title`).text('Detail Surat Tugas');

        container.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-hash me-1"></i>No Kontrak</label>
                    <div class="text-dark fw-semibold text-break">${this.info.no_kontrak}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-info-circle me-1"></i>Status</label>
                    <div>${statusFormat(this.info.jenisStatus, this.info.status)}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-layers me-1"></i>Jenis Layanan</label>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-subtle fw-normal rounded-pill text-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-emphasis">${this.info.tipe_kontrak}</span>
                        <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${this.info.jenis_layanan} - ${this.info.jenis_layanan_parent}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-person me-1"></i>Pelanggan</label>
                    <div class="text-dark fw-semibold">${this.info.pelanggan}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-building me-1"></i>Perusahaan</label>
                    <div class="text-dark fw-semibold text-break">${this.info.perusahaan}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-calendar-check me-1"></i>Tanggal Mulai</label>
                    <div class="text-success fw-semibold">${dateFormat(this.info.start_date, 4)}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-calendar-x me-1"></i>Tanggal Selesai</label>
                    <div class="text-danger fw-semibold">${dateFormat(this.info.end_date, 4)}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-clock-history me-1"></i>Dibuat Pada</label>
                    <div class="text-muted small">${dateFormat(this.info.created_at, 1)}</div>
                </div>
            </div>
        `;

        return container;
    }
    createInformationPerusahaan() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $(`#${this.options.id}-title`).text('Detail Perusahaan');

        container.innerHTML = `
            <input type="hidden" name="id_perusahaan" id="detail_id_hash" value="${this.info.id}">
            <div class="row g-3">
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-building me-1"></i>Nama Perusahaan</label>
                    <div class="text-dark fw-semibold text-break">${this.info.nama_perusahaan}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-hash me-1"></i>Kode Perusahaan</label>
                    <div class="text-dark fw-semibold text-break">${this.info.kode_perusahaan}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-card-text me-1"></i>NPWP</label>
                    <div class="text-dark fw-semibold text-break">${this.info.npwp_perusahaan}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-envelope me-1"></i>E-mail</label>
                    <div class="text-dark fw-semibold text-break">${this.info.email}</div>
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
        let createLI = (pic) => {
            const isActive = pic.status == 1;
            return `
                <div class="tl-item ${isActive ? 'active' : ''}">
                    <div class="tl-dot ${isActive ? 'border-primary' : 'border-secondary-subtle'}"></div>
                    <div class="tl-content w-100">
                        <div class="card shadow-sm border-0 mb-1 ${isActive ? 'bg-primary-subtle bg-opacity-25' : 'bg-white'} overflow-hidden">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle ${isActive ? 'bg-primary text-white' : 'bg-light text-secondary'} d-flex justify-content-center align-items-center fw-bold me-3 shadow-sm"
                                            style="width: 42px; height: 42px; min-width: 42px; font-size: 1.1rem;">
                                            ${pic.name.charAt(0).toUpperCase()}
                                        </div>
                                        <div style="min-width: 0;">
                                            <h6 class="mb-0 fw-bold ${isActive ? 'text-primary' : 'text-dark'} text-truncate">${pic.name}</h6>
                                            <div class="text-muted text-truncate" style="font-size: 0.75rem;">
                                                <i class="bi bi-envelope-fill opacity-50 me-1"></i>${pic.email}
                                            </div>
                                        </div>
                                    </div>
                                    ${isActive ? `
                                        <span class="badge rounded-pill bg-primary px-3 py-2">
                                            <i class="bi bi-person-check-fill me-1"></i>PIC Aktif
                                        </span>
                                    ` : ''}
                                </div>
                                <div class="p-2 rounded bg-white bg-opacity-75 border border-light">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-auto me-2">
                                            <div class="rounded-circle bg-light d-flex justify-content-center align-items-center" style="width: 28px; height: 28px;">
                                                <i class="bi bi-calendar-event text-secondary" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted d-block" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                                                ${isActive ? 'Mulai Menjabat' : 'Periode Jabatan'}
                                            </small>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                                ${isActive ? dateFormat(pic.created_at, 4) : `${dateFormat(pic.created_at, 4)} — ${dateFormat(pic.selesai_at, 4)}`}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        if (findPic) html += createLI(findPic);
        this.data.filter(pic => pic.status != 1).forEach(pic => {
            html += createLI(pic);
        });

        $(`#${this.options.id}-title`).text('History PIC');
        container.innerHTML = `<div class="timeline ps-2">${html}</div>`;

        return container;
    }
    createInformationTld() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $(`#${this.options.id}-title`).text('Detail TLD');

        let badgeStatus = this.info.status === 1 || this.info.digunakan
            ? '<span class="badge bg-success">Digunakan</span>'
            : '<span class="badge bg-secondary">Tidak Digunakan</span>';

        container.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-hash me-1"></i>No Seri TLD</label>
                    <div class="text-dark fw-semibold text-break">${this.info.no_seri_tld}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-info-circle me-1"></i>Status</label>
                    <div>${badgeStatus}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-tag me-1"></i>Merk</label>
                    <div class="text-dark fw-semibold">${this.info.merk}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-cpu me-1"></i>Jenis TLD</label>
                    <div class="text-dark fw-semibold text-capitalize">${this.info.jenis}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-building me-1"></i>Kepemilikan</label>
                    <div class="text-dark fw-semibold">${this.info.pemilik}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-person me-1"></i>Pengguna Aktif Saat Ini</label>
                    <div class="text-dark fw-semibold">
                        ${this.info.current_user} 
                        ${this.info.current_user_type ? `<span class="badge bg-light text-secondary-emphasis fw-normal border ms-1 text-capitalize">${this.info.current_user_type}</span>` : ''}
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-file-earmark-text me-1"></i>No Kontrak Aktif</label>
                    <div class="text-dark fw-semibold">${this.info.current_contract}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-calendar-event me-1"></i>Tanggal Pengadaan</label>
                    <div class="text-muted small">${this.info.tanggal_pengadaan !== '-' ? dateFormat(this.info.tanggal_pengadaan, 0) : '-'}</div>
                </div>
            </div>
        `;

        return container;
    }
    createInformationKontrak() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $(`#${this.options.id}-title`).text('Informasi Kontrak');

        container.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-hash me-1"></i>No Kontrak</label>
                    <div class="text-dark fw-semibold text-break">${this.info.no_kontrak}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-info-circle me-1"></i>Status</label>
                    <div>${statusFormat(this.info.jenisStatus, this.info.status)}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-layers me-1"></i>Jenis Layanan</label>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-subtle fw-normal rounded-pill text-${this.info.tipe_kontrak == 'kontrak lama' ? 'success' : 'primary'}-emphasis">${this.info.tipe_kontrak}</span>
                        <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${this.info.jenis_layanan} - ${this.info.jenis_layanan_parent}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-person me-1"></i>Pelanggan</label>
                    <div class="text-dark fw-semibold text-break">${this.info.pelanggan}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-building me-1"></i>Perusahaan</label>
                    <div class="text-dark fw-semibold text-break">${this.info.perusahaan}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-tags me-1"></i>Harga</label>
                    <div class="text-primary fw-bold">${formatRupiah(this.info.total_harga)}</div>
                </div>
                <div class="col-12">
                    <label class="text-uppercase text-muted fw-bold small d-block mb-1"><i class="bi bi-clock-history me-1"></i>Dibuat Pada</label>
                    <div class="text-muted small">${dateFormat(this.info.created_at, 0)}</div>
                </div>
            </div>
        `;

        return container;
    }

    createInformationHistoryNote() {
        const container = document.createElement('div');
        container.className = 'container fs-7';

        $(`#${this.options.id}-title`).text('Histori Catatan');

        let html = '';
        if (this.data && this.data.length > 0) {
            html += '<div class="list-group list-group-flush gap-2">';
            this.data.forEach((note, i) => {
                let properties = {};
                try {
                    properties = typeof note.properties === 'string' ? JSON.parse(note.properties) : (note.properties || {});
                } catch (e) { properties = {}; }

                let catatan = properties.catatan || properties.note || '';

                // Deteksi status untuk badge dan warna tema
                let statusBadge = '';
                const desc = note.description.toLowerCase();

                if (desc.includes('setuju') || desc.includes('approve') || desc.includes('terima')) {
                    statusBadge = `<span class="badge bg-success-subtle text-success rounded-pill px-2" style="font-size: 0.65rem;">${note.description}</span>`;
                } else if (desc.includes('tolak') || desc.includes('reject') || desc.includes('kembali')) {
                    statusBadge = `<span class="badge bg-danger-subtle text-danger rounded-pill px-2" style="font-size: 0.65rem;">${note.description}</span>`;
                } else {
                    statusBadge = `<span class="badge bg-primary-subtle text-primary rounded-pill px-2" style="font-size: 0.65rem;">${note.description}</span>`;
                }

                html += `
                    <div class="list-group-item p-2 bg-body-secondary transition-all rounded-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light text-secondary d-flex justify-content-center align-items-center fw-bold shadow-sm"
                                    style="width: 24px; height: 24px; font-size: 0.75rem;">
                                    ${note.causer?.name ? note.causer.name.charAt(0).toUpperCase() : '?'}
                                </div>
                                <div class="d-flex flex-column" style="min-width: 0;">
                                    <span class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">${note.causer?.name || 'System'}</span>
                                    <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-clock-history opacity-50 me-1"></i> ${dateFormat(note.created_at, 1)}</span>
                                </div>
                            </div>
                            ${statusBadge}
                        </div>
                        ${catatan ? `
                            <div class="ms-4 px-2 py-1 rounded bg-white border border-light text-secondary" style="font-size: 0.8rem; line-height: 1.4;">
                                <i class="bi bi-chat-right-text me-1 opacity-50"></i>${catatan}
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            html += '</div>';
        } else {
            html = `<div class="text-center text-muted p-5 bg-light rounded-3">
                        <i class="bi bi-chat-left-dots-fill display-4 opacity-25 d-block mb-3"></i>
                        Belum ada histori catatan.
                    </div>`;
        }

        container.innerHTML = html;
        return container;
    }

    // membuat tab

    createTab() {
        const container = document.createElement('div');
        const tabs = {};
        this.options.tab.pengguna && (tabs.pengguna = { title: 'Pengguna', content: this.createPenggunaContent(), badge: this.data?.pengguna?.length ?? 0, icon: 'bi bi-person-circle' });
        this.options.tab.tld && (tabs.tld = { title: 'TLD', content: this.createTldContent(), icon: 'bi bi-diagram-3' });
        this.options.tab.activitas && (tabs.activitas = { title: 'Aktivitas', content: this.createAktivitasContent(), icon: 'bi bi-activity' });
        this.options.tab.periode && (tabs.periode = { title: 'Periode', content: this.createPeriodeContent(), icon: 'bi bi-calendar-check' });
        this.options.tab.dokumen && (tabs.dokumen = { title: 'Dokumen', content: this.createDokumenContent(), icon: 'bi bi-folder2-open' });
        this.options.tab.dokumen_lhu && (tabs.dokumen_lhu = { title: 'Dokumen LHU', content: this.createDokumenLhuContent(), icon: 'bi bi-file-earmark-text' });

        this.options.tab.items && (tabs.items = { title: 'Items', content: this.createItemsContent(), icon: 'bi bi-box-seam' });
        this.options.tab.bukti && (tabs.bukti = { title: 'Bukti', content: this.createBuktiContent(), icon: 'bi bi-check2-square', maxHeight: '50vh' });

        this.options.tab.proses && (tabs.proses = { title: 'Proses Penyelia', content: this.createProsesContent(), icon: 'bi bi-gear-wide-connected' });

        this.options.tab.alamat && (tabs.alamat = { title: 'Alamat', content: this.createAlamatContent(), icon: 'bi bi-geo-alt' });
        this.options.tab.karyawan && (tabs.karyawan = { title: `PIC (${this.info?.karyawan?.length ?? 0})`, content: this.createKaryawanContent(), icon: 'bi bi-people-fill' });
        this.options.tab.surat_kuasa && (tabs.surat_kuasa = { title: 'Surat Kuasa', content: this.createSuratKuasaContent(), icon: 'bi bi-file-earmark-text' });

        this.options.tab.log && (tabs.log = { title: 'Log', content: this.createLogContent(), icon: 'bi bi-journal-text' });
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
                    <div class="submenu p-2 rounded-bottom-3 overflow-auto overflow-x-hidden border-top" id="submenu-${tabId}" style="max-height: ${tab.maxHeight ? tab.maxHeight : '30vh'}">
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
          <ul class="accordion-custom px-0 m-0" id="${this.options.id}-pills-tab" role="tablist">
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
        let list_pengguna = this.info.detail?.filter(d => d.jenis == 'pengguna');
        if (list_pengguna && list_pengguna.length > 0) {
            let html = '';
            for (const [i, item] of list_pengguna.entries()) {
                const pengguna = item.entitas;
                let fileKtp = pengguna.media_ktp ? `${base_url}/storage/${pengguna.media_ktp.file_path}/${pengguna.media_ktp.file_hash}` : '';

                const dataCard = {
                    index: i,
                    idHash: item.permohonan_detail_hash,
                    name: pengguna.name,
                    divisi: pengguna.divisi?.name || '',
                    isCheckedEvaluasi: false,
                    radiasi: pengguna.radiasi?.map(r => r.nama_radiasi),
                    fileKtp: fileKtp,
                    no_seri_tld: item.tld?.no_seri_tld || '',
                    htmlDisabled: true
                }

                let adendumActive = ['permohonan', 'penyelia'];
                if (item.type == 'ganti' && adendumActive.includes(this.options.jenis)) {
                    dataCard['name'] = item.pengguna_lama?.name;
                    dataCard['pengguna_baru'] = {
                        name: pengguna.name,
                    }
                }

                html += cardPenggunaComponent(dataCard, {
                    is_have_tld: false,
                    label_tld: false,
                    status: item.type
                });
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
        let kontrak_hash = false;
        let periode = false;
        switch (this.options.jenis) {
            case 'permohonan':
                this.data.kontrak?.document_kontrak && (dataDokumen = this.data.kontrak.document_kontrak);
                dataDokumen = dataDokumen.concat(this.data.dokumen);
                invoiceData = this.data.invoice;
                dataPermohonan = this.data;
                kontrak_hash = this.data.kontrak?.kontrak_hash;
                periode = this.data.periode;
                break;
            case 'penyelia':
                this.data.permohonan.kontrak?.document_kontrak && (dataDokumen = this.data.permohonan.kontrak.document_kontrak);
                dataDokumen = dataDokumen.concat(this.data.permohonan.dokumen);
                invoiceData = this.data.permohonan.invoice;
                dataPermohonan = this.data.permohonan;
                kontrak_hash = this.data.permohonan.kontrak.kontrak_hash;
                periode = this.data.permohonan.periode;
                break
            case 'kontrak':
                this.data?.document_kontrak && (dataDokumen = this.data.document_kontrak);
                kontrak_hash = this.data.kontrak_hash;
                break;
            case 'pengiriman':
                this.data?.kontrak.document_kontrak && (dataDokumen = this.data.kontrak.document_kontrak);
                if (this.data.permohonan) {
                    dataDokumen = dataDokumen.concat(this.data.permohonan.dokumen);
                }

                if (this.data.dokumen) {
                    dataDokumen = dataDokumen.concat(this.data.dokumen);
                }

                dataPermohonan = this.data.permohonan;
                kontrak_hash = this.data.kontrak.kontrak_hash;
                periode = this.data.periode;
                invoiceData = this.data.permohonan?.invoice;
                break;
            default:
                break;
        }
        let exceptDoc = [];
        if (role.includes('Pelanggan')) {
            exceptDoc = [
                'surattugas'
            ];
        }

        for (const [i, dokumen] of dataDokumen.entries()) {
            let idHash = false;
            let namaDokumen = dokumen.nama;
            if (exceptDoc.includes(dokumen.jenis)) continue;
            switch (dokumen.jenis) {
                case 'invoice':
                    idHash = invoiceData?.keuangan_hash;
                    break;
                case 'kwitansi':
                    idHash = invoiceData?.keuangan_hash;
                    break;
                case 'kontrak':
                    idHash = kontrak_hash;
                    break;
                case 'KontrakPengujian':
                    idHash = kontrak_hash;
                    break;
                case 'surpeng':
                    namaDokumen += ` (Periode ${dokumen.periode})`;
                    idHash = kontrak_hash + '/' + dokumen.periode;
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
                                <span class="fw-bolder">${namaDokumen}</span>
                                <div class="text-body-secondary">
                                    <small>${dateFormat(dokumen.created_at, 4)}</small>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-lihat-dokumen" data-url="laporan/${dokumen.jenis}/${idHash}" data-title="${dokumen.nama}">Lihat</button>
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
        if (!this.data.media) return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada dokumen</p>';

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
                            : `Proses ${mapItem.jobs.name} di kembalikan`;

                        dataLog.push({
                            message,
                            created_at: log.created_at,
                            user: log?.causer?.name,
                            note: mapItem.note
                        });
                    });
                });

                // order by created_at
                dataLog.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
            case 'tld':
                this.info.logs?.forEach(log => {
                    dataLog.push({
                        message: log.message,
                        created_at: log.created_at,
                        user: log.user,
                        note: log.note
                    });
                });
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

        if (dataLog.length == 0) {
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
        if (this.data.tipe_kontrak == 'kontrak lama' || this.data.tipe_kontrak == 'adendum') {
            let findPeriode = data.kontrak?.periode.find(periode => periode.periode == data.periode);
            if (findPeriode) {
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
                htmlPeriode = `<span class="text-danger">Periode tidak ditemukan</span>`;
            }
        } else {
            if (this.info.periodePemakaian && this.info.periodePemakaian.length > 0) {
                for (const [i, periode] of this.info.periodePemakaian.entries()) {
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
            } else {
                for (const [i, periode] of this.info.periodeNow.entries()) {
                    if (periode.periode == 0) continue;
                    htmlPeriode += `
                        <div class="card mb-1">
                            <div class="card-body p-1 px-3">
                                <div>Periode ${periode.periode}</div>
                                <div class="text-body-secondary">
                                    <small>${dateFormat(periode.start_date, 4)} - ${dateFormat(periode.end_date, 4)}</small>
                                </div>
                            </div>
                        </div>
                    `;
                }
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
            let htmlPeriode = !detail.periode ? 'Zero Check' : `Periode ${detail.periode}`;

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
        if (alamatarr.length == 0) return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada alamat</p>';

        return `
            <ul class="list-group list-group-flush">
                ${alamatarr.map((data, i) => {
            if (data.status == 1) {
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
        if (karyawanarr.length == 0) return '<p class="text-center text-muted mt-3 w-100 fs-6 fw-bold">Tidak ada karyawan</p>';

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
            if (data.profile?.suratkuasa) {
                let urlDocumen = `${base_url}/storage/${data.profile.suratkuasa.file_path}/${data.profile.suratkuasa.file_hash}`;
                docSuratKuasa = `<a type="button" class="btn btn-sm btn-outline-info" href="${urlDocumen}" target="_blank" title="Dokumen Surat Kuasa"><span class="bi bi-file-earmark-text"></span></a>`;
            }

            let htmlTglKeluar = '';
            if (data.selesai_at) {
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
        if (dokumen.length > 0) {
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
        let listTld = this.info.detail ?? [];

        if (listTld.length > 0) {
            let htmlPengguna = '';
            let htmlKontrol = '';

            let arrPengguna = listTld.filter(d => d.jenis == 'pengguna');
            let arrKontrol = listTld.filter(d => d.jenis == 'kontrol');

            let i = 1;
            for (const kontrol of arrKontrol) {
                if (kontrol.tld) {
                    htmlKontrol += `
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <span>${i}. </span>
                                <span>${'TLD Kontrol'}</span>
                            </div>
                            <span>${kontrol.tld.no_seri_tld}</span>
                        </li>
                    `;
                    i++;
                }
            }

            for (const pengguna of arrPengguna) {
                if (pengguna.tld) {
                    htmlPengguna += `
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <span>${i}. </span>
                                <span>${pengguna.entitas.name}</span>
                            </div>
                            <span>${pengguna.tld.no_seri_tld}</span>
                        </li>
                    `;
                    i++;
                }
            }

            if (htmlKontrol == '' && htmlPengguna == '') {
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
            <div class="offcanvas offcanvas-end custom-offcanvas" tabindex="-1" id="${this.options.id}" aria-labelledby="${this.options.id}Label">
                <div class="offcanvas-header border-bottom py-1">
                    <div>
                        <h3 class="fw-semibold mb-2" id="${this.options.id}-title">Detail</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div id="${this.options.id}-loading" class="m-auto"></div>
                <div id="${this.options.id}-main" class="offcanvas-body p-2">
                    <div class="pt-2" id="${this.options.id}-container">

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
            $(`#${this.options.id}`).remove();
        }
    }
}
