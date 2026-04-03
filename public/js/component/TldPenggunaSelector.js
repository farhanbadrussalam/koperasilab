/**
 * TldPenggunaSelector
 * Class untuk menghandle modal pencarian dan pemilihan pengguna TLD
 * menggunakan DataTables ServerSide.
 */
class TldPenggunaSelector {
    constructor(options = {}) {
        // Default Configuration
        this.modalId = options.modalId || '#modal-add-tld-pengguna';
        this.tableId = options.tableId || '#table-user';
        this.searchId = options.searchId || '#customSearch';
        this.paginationId = options.paginationId || '#customPagination';
        this.emptyStateId = options.emptyStateId || '#emptyState';

        // API Endpoint (Bisa di-override saat inisialisasi)
        this.apiUrl = options.apiUrl || 'api/v1/pengguna/getHtml';

        this.type = options.type || 'pengguna';

        // Internal state
        this.datatable = null;

        this.dataSelected = [];

        // Initialize
        this._initDataTable();
        this._bindEvents();
    }

    /**
     * Inisialisasi DataTable
     */
    _initDataTable() {
        const self = this; // Reference untuk di dalam scope DataTable

        this.datatable = $(this.tableId).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: this.apiUrl,
                type: 'GET',
                data: function(d) {
                    // Kirim parameter filter pencarian ke server
                    d.filter = {
                        name: $(self.searchId).val()
                    };

                    self.dataSelected.length > 0 && (d.selected = self.dataSelected);
                    d.type = self.type;
                }
            },
            bLengthChange: false,
            bFilter: false, // Kita pakai custom search
            bInfo: false,
            ordering: false,
            pageLength: 5,
            columns: [
                { data: 'html', orderable: false }
            ],
            drawCallback: function(settings) {
                // 1. Pindahkan Pagination ke Footer Modal Custom
                var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');

                $(self.paginationId).html(pagination[0]);
                // 2. Handle Empty State
                if (settings.aoData.length === 0) {
                    $(self.emptyStateId).removeClass('d-none'); // Munculkan gambar kosong
                    $(this).hide(); // Sembunyikan tabel
                } else {
                    $(self.emptyStateId).addClass('d-none');
                    $(this).show();
                }

                // Opsional: Trigger event global jika ada popup reload image
                if (typeof showPopupReload === 'function') {
                    showPopupReload();
                }
            }
        });
    }

    /**
     * Bind Event Listeners (Search, Button Click, dll)
     */
    _bindEvents() {
        // 1. Event saat mengetik di kolom pencarian
        $(this.searchId).on('keyup', () => {
            this.reload();
        });

        $('#btn-trigger-create-user').on('click', () => {
            this.hide(); // Tutup modal pencarian

            // Dispatch event minta buka form create
            document.dispatchEvent(new CustomEvent("pengguna.request_create"));
        });

        // 2. Event Delegation untuk tombol "Pilih" di dalam tabel
        // Ini menggantikan fungsi onclick="btnPilih(this)" inline agar lebih modular
        $(this.tableId).on('click', '.btn-pilih-user', (e) => {
            const btn = $(e.currentTarget);

            // Ambil data dari atribut data-json atau data-id
            // Asumsi: Server mengirim button dengan class 'btn-pilih-user' dan data attributes
            const userData = {
                id: btn.data('id'),
                name: btn.data('name')
            };

            // Dispatch Custom Event
            this._dispatchSelectEvent(btn, userData, 'pengguna.pilih');

            // Tutup modal setelah memilih (Opsional)
            // this.hide();
        });

        $(this.tableId).on('click', '.btn-edit-pengguna', (e) => {
            this.hide();
            const btn = $(e.currentTarget);

            const userData = {
                id: btn.data('id'),
                name: btn.data('name')
            };

            this._dispatchSelectEvent(btn, userData, 'pengguna.request_edit');
        })

        $(this.modalId).on('hidden.bs.modal', () => {
            this._dispatchSelectEvent(null, null, 'pengguna.hide');
        })
    }

    /**
     * Dispatch event 'pengguna.pilih' agar bisa ditangkap oleh file lain
     */
    _dispatchSelectEvent(obj, data, type) {
        // Cara Modern: Dispatch ke document
        document.dispatchEvent(new CustomEvent(type, {
            detail: {
                html: obj,
                data: data
            }
        }));
    }

    /**
     * Public Method: Menampilkan Modal & Reload Data
     */
    show(dataSelected = []) {
        this.dataSelected = dataSelected;
        this.reload(); // Refresh data terbaru
        $(this.modalId).modal('show');
    }

    /**
     * Public Method: Menyembunyikan Modal
     */
    hide() {
        $(this.modalId).modal('hide');
    }

    /**
     * Public Method: Reload DataTable
     */
    reload() {
        if (this.datatable) {
            this.datatable.ajax.reload();
        }
    }
}

