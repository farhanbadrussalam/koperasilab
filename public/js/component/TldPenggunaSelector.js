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
        $(this.tableId).on('click', '.btn-pilih-user', (e) => {
            const btn = $(e.currentTarget);
            const userId = btn.data('id');

            spinner('show', btn);

            ajaxGet(`api/v1/pengguna/getDataById/${userId}`, false, (result) => {
                spinner('hide', btn);
                if (result && result.data) {
                    this._openDivisiModal(btn, result.data);
                }
            }, () => {
                spinner('hide', btn);
            });
        });

        // 3. Confirm Selection in Divisi Modal
        $('#btn-confirm-select-divisi-user').off('click').on('click', () => {
            const userId = $('#select_divisi_user_id').val();
            const userName = $('#select_divisi_user_name').val();
            const selectDivEl = $('#select_divisi_user_id_divisi');
            const selectKodeEl = $('#select_divisi_user_kode_lencana');
            const selectedOpt = selectDivEl.find(':selected');

            const idDivisi = selectDivEl.val() || null;
            const kodeLencana = selectKodeEl.val() || null;
            const divisiName = idDivisi ? selectedOpt.text() : '-';

            const userData = {
                id: userId,
                name: userName,
                id_divisi: idDivisi,
                kode_lencana: kodeLencana,
                divisi_name: divisiName
            };

            $('#modal-select-divisi-user').modal('hide');
            this._dispatchSelectEvent(this.lastSelectedBtn, userData, 'pengguna.pilih');
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
     * Buka modal pemilihan divisi & kode lencana untuk pengguna yang dipilih
     */
    _openDivisiModal(btn, userData) {
        this.lastSelectedBtn = btn;

        $('#select_divisi_user_id').val(userData.pengguna_hash);
        $('#select_divisi_user_name').val(userData.name);

        const selectDivEl = $('#select_divisi_user_id_divisi');
        const selectKodeEl = $('#select_divisi_user_kode_lencana');
        selectDivEl.empty();
        selectKodeEl.empty();

        const listDetail = userData.divisi_list_detail || [];

        // 1. Ambil daftar divisi unik milik pengguna
        const uniqueDivisions = [];
        const seenDivs = new Set();

        listDetail.forEach(item => {
            if (item.name && item.name !== '-' && item.name !== 'Tanpa Divisi') {
                const divVal = item.divisi_hash || item.id_divisi;
                if (divVal && !seenDivs.has(divVal)) {
                    seenDivs.add(divVal);
                    uniqueDivisions.push({
                        val: divVal,
                        name: item.name
                    });
                }
            }
        });

        if (uniqueDivisions.length > 0) {
            uniqueDivisions.forEach(d => {
                selectDivEl.append(`<option value="${d.val}">${d.name}</option>`);
            });
        }
        // Opsi 'Tanpa Divisi'
        selectDivEl.append('<option value="">Tanpa Divisi</option>');

        // 2. Ambil seluruh daftar Kode Lencana unik yang dimiliki oleh pengguna ini
        const allKodes = [];
        listDetail.forEach(item => {
            const kode = item.kode_lencana && item.kode_lencana !== '-' ? item.kode_lencana : null;
            if (kode && !allKodes.includes(kode)) {
                allKodes.push(kode);
            }
        });
        if (allKodes.length === 0) {
            if (userData.kode_lencana && userData.kode_lencana !== '-') {
                allKodes.push(userData.kode_lencana);
            } else {
                allKodes.push('-');
            }
        }

        // 3. Fungsi pembaruan dropdown Kode Lencana (menampilkan SEMUA kode lencana, auto-select kode yang sesuai divisi)
        const updateKodeLencanaOptions = () => {
            const selectedDiv = selectDivEl.val();
            selectKodeEl.empty();

            // Cari kode lencana default untuk divisi terpilih
            let preferredKode = null;
            if (selectedDiv) {
                const foundItem = listDetail.find(item => {
                    const itemDiv = item.divisi_hash || item.id_divisi || null;
                    return itemDiv == selectedDiv && item.kode_lencana && item.kode_lencana !== '-';
                });
                if (foundItem) {
                    preferredKode = foundItem.kode_lencana;
                }
            }

            allKodes.forEach(kode => {
                const isSelected = (preferredKode && kode === preferredKode) ? 'selected' : '';
                selectKodeEl.append(`<option value="${kode}" ${isSelected}>${kode}</option>`);
            });
        };

        selectDivEl.off('change').on('change', updateKodeLencanaOptions);
        updateKodeLencanaOptions();

        $('#modal-select-divisi-user').modal('show');
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

