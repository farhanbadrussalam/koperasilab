/**
 * PenggunaForm
 * Class untuk menghandle Modal Tambah/Edit Pengguna
 */
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
        this.select2Divisi = null;
        this.selectedData = null; // Menyimpan data user yang sedang diedit

        // Init
        this._initPlugins();
        this._bindEvents();
    }

    _initPlugins() {
        // 1. Form Validation (Asumsi library FormValidation tersedia global)
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

        // 5. Select2 - Divisi
        this.select2Divisi = $('#divisi_pengguna').select2({
            theme: "bootstrap-5",
            tags: true,
            placeholder: "Pilih Divisi",
            dropdownParent: $(this.modalId),
            createTag: (params) => ({ id: params.term, text: params.term, newTag: true }),
            ajax: this._getSelect2AjaxConfig(this.api.divisi, 'name_divisi', 'name', 'divisi_hash')
        });
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

        // Toggle Checkbox Auto Generate Kode Lencana
        $('#is_aktif').on('change', function() {
            self._toggleKodeLencana($(this).is(':checked'));
        });

        // Reset Form saat Modal Ditutup
        $(this.modalId).on('hidden.bs.modal', () => {
            this.resetForm();
        });

        // Tombol Simpan
        $(this.btnSaveId).on('click', function() {
            self.submit(this);
        });
    }

    _toggleKodeLencana(isChecked) {
        const input = $('#kode_lencana');
        if (isChecked) {
            input.val('').attr('readonly', true)
                .attr('placeholder', 'Auto Generate')
                .addClass('bg-secondary-subtle')
                .attr('data-parsley-required', 'false');
        } else {
            input.attr('readonly', false)
                .attr('placeholder', '')
                .removeClass('bg-secondary-subtle')
                .attr('data-parsley-required', 'true');
        }
    }

    /**
     * Public Method: Tampilkan Modal Tambah (Kosong)
     */
    showAdd() {
        this.selectedData = null;
        this.resetForm();
        $(this.modalId).modal('show');
    }

    /**
     * Public Method: Tampilkan Modal Edit dengan Data
     * @param {string} id - Hash ID pengguna
     */
    showEdit(id) {
        this.selectedData = null; // Reset dulu
        $(this.modalId).modal('show');

        // Hide form, show loading
        $(this.formId).hide();
        spinner('show', $(this.loadingId), { height: "100px", width: "100px" });

        // Fetch Data
        ajaxGet(`${this.api.getData}${id}`, false, (result) => {
            this._populateForm(result.data);

            spinner('hide', $(this.loadingId));
            $(this.formId).fadeIn();
        });
    }

    _dispatchSelectEvent(obj, data, type) {
        // Cara Modern: Dispatch ke document
        document.dispatchEvent(new CustomEvent(type, {
            detail: obj,
            data: data
        }));
    }

    _populateForm(data) {
        this.selectedData = data;

        $('#nik_pengguna').val(data.nik);
        $('#nama_pengguna').val(data.name);
        $('#jenis_kelamin').val(data.jenis_kelamin);
        $('#tanggal_lahir').val(data.tanggal_lahir); // Flatpickr auto handles Y-m-d
        $('#tempat_lahir').val(data.tempat_lahir);

        // Handle Kode Lencana & Checkbox
        $('#kode_lencana').val(data.kode_lencana).attr('readonly', true);
        $('#is_aktif').hide(); // Sembunyikan checkbox autogenerate saat edit

        // Populate Select2 Divisi
        if (data.divisi) {
            const option = new Option(data.divisi.name, data.divisi.divisi_hash, true, true);
            this.select2Divisi.append(option).trigger('change');
        }

        // Populate Select2 Radiasi (Multiple)
        if (data.radiasi && Array.isArray(data.radiasi)) {
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

        // Reset basic fields
        $('#nik_pengguna, #nama_pengguna, #tanggal_lahir, #tempat_lahir, #jenis_kelamin').val('');

        // Reset Select2
        $('#jenis_radiasi').empty().val(null).trigger('change');
        $('#divisi_pengguna').empty().val(null).trigger('change');

        // Reset Logic Checkbox
        $('#is_aktif').show().prop('checked', true);
        this._toggleKodeLencana(true);
        $('#kode_lencana').val('');

        // Reset Upload
        if (this.uploadComponent) this.uploadComponent.addData([]);
    }

    submit(btnElement) {
        spinner('show', btnElement);

        // 1. Validasi Form Standard
        if (this.formValidate && !this.formValidate.validate()) {
            return spinner('hide', btnElement);
        }

        // 2. Validasi Custom (Gambar)
        const imageKtp = this.uploadComponent ? this.uploadComponent.getData() : [];
        if (imageKtp.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Oops...', text: 'Data KTP Pengguna wajib diupload.' });
            return spinner('hide', btnElement);
        }

        // 3. Prepare Data
        const formData = new FormData();
        formData.append('nik', $('#nik_pengguna').val());
        formData.append('kode_lencana', $('#kode_lencana').val());
        formData.append('is_aktif', $('#is_aktif').is(':checked') ? 1 : 0);
        formData.append('jenis_kelamin', $('#jenis_kelamin').val());
        formData.append('tanggal_lahir', $('#tanggal_lahir').val());
        formData.append('tempat_lahir', $('#tempat_lahir').val());
        formData.append('name', $('#nama_pengguna').val());
        formData.append('ktp', imageKtp[0].file);

        // Handle Select2 Values
        const divisiVal = $('#divisi_pengguna').val();
        if (divisiVal && divisiVal !== 'null') formData.append('divisi', divisiVal);
        formData.append('radiasi', JSON.stringify($('#jenis_radiasi').val()));

        // Jika Edit Mode, append ID
        if (this.selectedData && this.selectedData.pengguna_hash) {
            formData.append('id', this.selectedData.pengguna_hash);
        }

        // 4. AJAX Submit
        ajaxPost(this.api.action, formData, (result) => {
            if (result.meta.code == 200) {
                Swal.fire({ icon: "success", text: result.data.msg });

                // Trigger event global agar tabel di halaman lain bisa refresh
                document.dispatchEvent(new CustomEvent("pengguna.saved", { detail: result.data }));

                // Jika ada fungsi reload global
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
