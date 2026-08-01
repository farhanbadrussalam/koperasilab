class PenggunaForm {
    constructor(options = {}) {
        // Default Config & Selectors
        this.modalId = options.modalId || '#modal-add-pengguna';
        this.formId = options.formId || '#form-tambah-pengguna';
        this.btnSaveId = options.btnSaveId || '#btn-tambah-pengguna';
        this.loadingId = options.loadingId || '#loading-tambah-pengguna';
        this.uploadContainerId = options.uploadContainerId || 'uploadKtpPengguna';

        // API endpoints
        this.api = {
            radiasi: 'api/v1/pengguna/getRadiasi',
            divisi: 'api/v1/pengguna/getDivisi',
            action: 'api/v1/pengguna/action',
            getData: 'api/v1/pengguna/getDataById/'
        };

        // Internal State
        this.formValidate = null;
        this.uploadComponent = null;
        this.select2Radiasi = null;
        this.selectedData = null; // Menyimpan data user yang sedang diedit

        // Init
        this._initPlugins();
        this._bindEvents();
    }

    _initPlugins() {
        // 1. Form Validation
        if (typeof FormValidation !== 'undefined') {
            this.formValidate = new FormValidation(this.formId.replace('#', ''));
        }

        // 2. Upload Component
        if (typeof UploadComponent !== 'undefined') {
            this.uploadComponent = new UploadComponent(this.uploadContainerId, {
                allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg'],
                camera: false,
                multiple: false,
                preview: { fullwidth: true, height: 300 }
            });
        }

        // 3. Flatpickr
        $('#tanggal_lahir').flatpickr({
            enableTime: false,
            dateFormat: "Y-m-d"
        });

        // 4. Select2 - Radiasi
        this.select2Radiasi = $('#jenis_radiasi').select2({
            theme: "bootstrap-5",
            tags: true,
            placeholder: "Pilih Jenis Radiasi",
            dropdownParent: $(this.modalId),
            createTag: (params) => ({ id: params.term, text: params.term, newTag: true }),
            ajax: this._getSelect2AjaxConfig(this.api.radiasi, 'name_radiasi', 'nama_radiasi', 'radiasi_hash')
        });

        // Inisialisasi minimal 1 row divisi jika container kosong
        if ($('#container-divisi-rows').children().length === 0) {
            this.addDivisiRow();
        }
    }

    _getSelect2AjaxConfig(url, paramName, textField, idField) {
        return {
            url: `${base_url}/${url}`,
            dataType: 'json',
            delay: 250,
            type: 'GET',
            headers: {
                'Authorization': `Bearer ${bearer}`,
                'Content-Type': 'application/json'
            },
            data: (params) => ({ [paramName]: params.term }),
            processResults: (data) => {
                return {
                    results: $.map(data.data, function(item) {
                        return { text: item[textField], id: item[idField] };
                    })
                };
            }
        };
    }

    _bindEvents() {
        const self = this;

        // Tombol Tambah Row Divisi
        $(document).off('click', '#btn-add-divisi-row').on('click', '#btn-add-divisi-row', function() {
            self.addDivisiRow();
        });

        // Tombol Hapus Row Divisi
        $(document).off('click', '.btn-remove-divisi-row').on('click', '.btn-remove-divisi-row', function() {
            const container = $('#container-divisi-rows');
            if (container.children().length > 1) {
                $(this).closest('.divisi-row').remove();
            } else {
                Swal.fire({ icon: 'warning', text: 'Pengguna minimal harus memiliki 1 divisi.' });
            }
        });

        // Event Toggle Auto Kode per Row
        $(document).off('change', '.check-is-auto').on('change', '.check-is-auto', function() {
            const row = $(this).closest('.divisi-row');
            const input = row.find('.input-kode-lencana');
            if ($(this).is(':checked')) {
                input.val('').attr('readonly', true).attr('placeholder', 'Auto Generate').addClass('bg-secondary-subtle');
            } else {
                input.attr('readonly', false).attr('placeholder', 'Contoh: 004').removeClass('bg-secondary-subtle');
            }
        });

        // Reset Form saat Modal Ditutup
        $(this.modalId).on('hidden.bs.modal', () => {
            this.resetForm();
        });

        // Tombol Simpan
        $(this.btnSaveId).off('click').on('click', function() {
            self.submit(this);
        });
    }

    addDivisiRow(data = null) {
        const container = $('#container-divisi-rows');
        const rowId = 'divisi-row-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

        const rowHtml = `
            <div class="divisi-row border rounded-3 p-2 bg-light d-flex align-items-center gap-2" id="${rowId}">
                <div class="flex-grow-1">
                    <select class="form-select select2-divisi-item" data-placeholder="Pilih Divisi (Opsional)"></select>
                </div>
                <div style="width: 220px;">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control input-kode-lencana maskNumber bg-secondary-subtle" placeholder="Auto Generate" readonly>
                        <div class="input-group-text rounded-end" title="Centang untuk Auto Generate Kode Lencana">
                            <input type="checkbox" class="form-check-input check-is-auto mt-0" checked>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-divisi-row" title="Hapus Divisi">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

        container.append(rowHtml);
        const row = $(`#${rowId}`);

        // Inisialisasi Select2 pada row baru
        const select2El = row.find('.select2-divisi-item').select2({
            theme: "bootstrap-5",
            tags: true,
            placeholder: "Pilih / Ketik Divisi Baru",
            allowClear: true,
            dropdownParent: $(this.modalId),
            createTag: (params) => ({ id: params.term, text: params.term, newTag: true }),
            ajax: this._getSelect2AjaxConfig(this.api.divisi, 'name_divisi', 'name', 'divisi_hash')
        });

        // Jika ada initial data (saat edit)
        if (data) {
            if (data.id_divisi || data.name !== 'Tanpa Divisi') {
                const divValue = data.divisi_hash || data.name || data.id_divisi;
                if (divValue) {
                    const optionName = data.name && data.name !== '-' ? data.name : (divValue || '-');
                    const option = new Option(optionName, divValue, true, true);
                    select2El.append(option).trigger('change');
                }
            }

            const inputKode = row.find('.input-kode-lencana');
            const checkAuto = row.find('.check-is-auto');

            if (data.kode_lencana && data.kode_lencana !== '-') {
                checkAuto.prop('checked', false);
                inputKode.val(data.kode_lencana).attr('readonly', false).removeClass('bg-secondary-subtle');
            } else {
                checkAuto.prop('checked', true);
                inputKode.val('').attr('readonly', true).addClass('bg-secondary-subtle');
            }
        }
    }

    showAdd() {
        this.selectedData = null;
        this.resetForm();
        $(this.modalId).modal('show');
    }

    showEdit(id) {
        this.selectedData = null;
        this.resetForm();
        $(this.modalId).modal('show');

        $(this.formId).hide();
        spinner('show', $(this.loadingId), { height: "100px", width: "100px" });

        ajaxGet(`${this.api.getData}${id}`, false, (result) => {
            this._populateForm(result.data);

            spinner('hide', $(this.loadingId));
            $(this.formId).fadeIn();
        }, (error) => {
            spinner('hide', $(this.loadingId));
            $(this.formId).show();
        });
    }

    _populateForm(data) {
        this.selectedData = data;

        $('#nik_pengguna').val(data.nik);
        $('#nama_pengguna').val(data.name);
        $('#jenis_kelamin').val(data.jenis_kelamin);
        $('#tanggal_lahir').val(data.tanggal_lahir);
        $('#tempat_lahir').val(data.tempat_lahir);

        // Populate Multi-Divisi Rows
        const container = $('#container-divisi-rows');
        container.empty();

        if (data.divisi_list_detail && Array.isArray(data.divisi_list_detail) && data.divisi_list_detail.length > 0) {
            data.divisi_list_detail.forEach(d => {
                this.addDivisiRow(d);
            });
        } else if (data.divisi) {
            this.addDivisiRow({
                id_divisi: data.divisi.id_divisi,
                divisi_hash: data.divisi.divisi_hash,
                name: data.divisi.name,
                kode_lencana: data.kode_lencana
            });
        } else {
            this.addDivisiRow();
        }

        // Populate Radiasi
        if (data.radiasi && Array.isArray(data.radiasi)) {
            $('#jenis_radiasi').empty();
            data.radiasi.forEach(r => {
                const option = new Option(r.nama_radiasi, r.radiasi_hash, true, true);
                this.select2Radiasi.append(option);
            });
            this.select2Radiasi.trigger('change');
        }

        // Populate Image
        if (data.media_ktp && this.uploadComponent) {
            this.uploadComponent.setData(data.media_ktp);
        }
    }

    resetForm() {
        if (this.formValidate) this.formValidate.reset();

        $('#nik_pengguna, #nama_pengguna, #tanggal_lahir, #tempat_lahir, #jenis_kelamin').val('');
        $('#jenis_radiasi').empty().val(null).trigger('change');

        $('#container-divisi-rows').empty();
        this.addDivisiRow();

        if (this.uploadComponent) this.uploadComponent.addData([]);
    }

    submit(btnElement) {
        spinner('show', btnElement);

        if (this.formValidate && !this.formValidate.validate()) {
            return spinner('hide', btnElement);
        }

        const imageKtp = this.uploadComponent ? this.uploadComponent.getData() : [];
        if (imageKtp.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Oops...', text: 'Data KTP Pengguna wajib diupload.' });
            return spinner('hide', btnElement);
        }

        // Ambil data divisi dari setiap row
        const divisiArray = [];

        $('.divisi-row').each(function() {
            let selectVal = $(this).find('.select2-divisi-item').val();
            const inputKode = $(this).find('.input-kode-lencana').val();
            const isAuto = $(this).find('.check-is-auto').is(':checked') ? 1 : 0;

            if (!selectVal || selectVal === 'null') {
                selectVal = null; // Menandakan 'Tanpa Divisi'
            }

            divisiArray.push({
                id_divisi: selectVal,
                kode_lencana: inputKode,
                is_auto: isAuto
            });
        });

        if (divisiArray.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Oops...', text: 'Pengguna wajib memiliki setidaknya 1 baris divisi (bisa dibiarkan kosong untuk Tanpa Divisi).' });
            return spinner('hide', btnElement);
        }

        const formData = new FormData();
        formData.append('nik', $('#nik_pengguna').val());
        formData.append('jenis_kelamin', $('#jenis_kelamin').val());
        formData.append('tanggal_lahir', $('#tanggal_lahir').val());
        formData.append('tempat_lahir', $('#tempat_lahir').val());
        formData.append('name', $('#nama_pengguna').val());
        formData.append('ktp', imageKtp[0].file);
        formData.append('divisi_list', JSON.stringify(divisiArray));
        formData.append('radiasi', JSON.stringify($('#jenis_radiasi').val()));

        if (this.selectedData && this.selectedData.pengguna_hash) {
            formData.append('id', this.selectedData.pengguna_hash);
        }

        ajaxPost(this.api.action, formData, (result) => {
            if (result.meta.code == 200) {
                Swal.fire({ icon: "success", text: result.data.msg });

                document.dispatchEvent(new CustomEvent("pengguna.saved", { detail: result.data }));

                if (typeof reload === 'function') reload();

                $(this.modalId).modal('hide');
            } else {
                Swal.fire({ icon: "error", text: result.data.msg });
            }
            spinner('hide', btnElement);
        }, (error) => {
            spinner('hide', btnElement);
        });
    }
}

