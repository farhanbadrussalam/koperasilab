/**
 * PengajuanTambahManager
 * Refactored modular manager for managing permohonan/pengajuan_tambah form.
 */
class PengajuanTambahManager {
    constructor() {
        // State
        this.idPermohonan = $('#id_permohonan').val();
        this.price = window.price || 0;
        this.arrKontrolTmp = [];
        this.typeLayanan = '';
        this.typeLayanan2 = '';
        this.JL = '';
        this.tmpArrTldPengguna = [];
        this.tmpArrTldKontrol = [];

        // Plugins
        this.inventoryTld = null;
        this.tldSelector = null;
        this.penggunaForm = null;
        this.periodeJs = null;
        this.periodeNextJs = null;

        // DOM elements cache
        this.dom = {
            formInputan: $('#form-inputan'),
            formTipeKontrak: $('#form-tipe-kontrak'),
            formPeriode: $('#form-periode'),
            formJenisTld: $('#form-jenis-tld'),
            formJumPengguna: $('#form-jum-pengguna'),
            formJumKontrol: $('#form-jum-kontrol'),
            formPic: $('#form-pic'),
            formNoHp: $('#form-nohp'),
            formAlamat: $('#form-alamat'),
            formPeriodeNext: $('#form-periode-next'),
            formPeriode1: $('#form-periode-1'),
            formPeriode2: $('#form-periode-2'),
            formTotalHarga: $('#form-total-harga'),
            formZeroCek: $('#form-zero-cek'),
            btnAddPengguna: $('#btn-add-pengguna'),
            jenisLayanan: $('#jenis_layanan'),
            jenisLayanan2: $('#jenis_layanan_2'),
            layananJasa: $('#layanan_jasa'),
            selectAlamat: $('#selectAlamat'),
            txtAlamat: $('#txt_alamat'),
            jenisTld: $('#jenis_tld'),
            jumPengguna: $('#jum_pengguna'),
            jumKontrol: $('#jum_kontrol'),
            periodePemakaian: $('#periode-pemakaian'),
            periodeNext: $('#periode_next'),
            zeroCek: $('#zero_cek'),
            totalHarga: $('#total_harga'),
            haveTld: $('#haveTld'),
            useZeroCek: $('#useZeroCek'),
            switchZerocek: $('#switch-zerocek'),
            formSwitch: $('#form-switch'),
            
            // Buttons
            btnClearPeriode: $('#btn-clear-periode'),
            btnClearPeriodeNext: $('#btn-clear-periode-next'),
            btnPeriode: $('#btn-periode'),
            btnPeriodeNext: $('#btn-periode-next'),
            btnBuatForm: $('#btn-buat-form'),
            divBuatForm: $('#div-buat-form'),
            simpanDraf: $('#simpanDraf'),
            simpanPengajuan: $('#simpanPengajuan')
        };
    }

    /**
     * Entry point to boot the manager
     */
    init() {
        this.initPlugins();
        this.initAlamat();
        this.bindEvents();
        this.resetForm();
        this.cekLayanan();
    }

    /**
     * Setup custom modules & libraries
     */
    initPlugins() {
        this.inventoryTld = new Inventory_tld({ preview: true });
        this.inventoryTld.on('inventory.selected', (e) => this.handleInventorySelected(e));

        this.tldSelector = new TldPenggunaSelector({
            apiUrl: `${base_url}/management/getDataPengguna`,
            type: 'selected'
        });

        this.penggunaForm = new PenggunaForm();

        // Periode instantiation
        const getPeriode = this.dom.periodePemakaian.attr('data-periode');
        const getPeriodeNext = this.dom.periodeNext.attr('data-periode');

        this.periodeJs = new Periode(getPeriode, {
            id_element: 1,
        });

        this.periodeNextJs = new Periode(getPeriodeNext, {
            max: 1,
            textPeriode: 'Periode Berikutnya',
            id_element: 2
        });

        // Register flatpickr
        $('#tanggal_lahir').flatpickr({
            enableTime: false,
            dateFormat: "Y-m-d"
        });
    }

    /**
     * Fill address dropdown based on pelanggan details
     */
    initAlamat() {
        let htmlAlamat = '<option value="">Pilih alamat</option>';
        if (dataPermohonan?.pelanggan?.perusahaan?.alamat) {
            for (const [i, value] of Object.entries(dataPermohonan.pelanggan.perusahaan.alamat)) {
                htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
            }
        }
        this.dom.selectAlamat.html(htmlAlamat);
    }

    /**
     * Map all UI event listeners
     */
    bindEvents() {
        // Form & input bindings
        this.dom.btnAddPengguna.on('click', () => this.tldSelector.show());

        $('#btn-add-kontrol').on('click', () => {
            $('#modal-add-kontrol').modal('show');
        });

        this.dom.selectAlamat.on('change', (e) => this.handleAlamatChange(e));
        this.dom.jenisLayanan.on('change', (e) => this.handleJenisLayananChange(e));
        this.dom.jenisTld.on('change', (e) => this.handleJenisTldChange(e));

        // Save actions
        this.dom.simpanDraf.on('click', (e) => this.save(true, e.target));
        this.dom.simpanPengajuan.on('click', (e) => this.save(false, e.target));

        // Periode actions
        this.dom.btnPeriode.on('click', () => this.periodeJs.show());
        this.dom.btnPeriodeNext.on('click', () => this.periodeNextJs.show());

        this.periodeJs.on('periode.simpan.1', () => this.simpanPeriode());
        this.periodeNextJs.on('periode.simpan.2', () => this.simpanPeriodeNext());

        this.dom.btnClearPeriode.on('click', () => this.clearPeriode());
        this.dom.btnClearPeriodeNext.on('click', () => this.clearPeriodeNext());

        // Create form
        this.dom.btnBuatForm.on('click', (e) => this.handleBuatForm(e));

        // TLD Switch
        this.dom.haveTld.on('change', (e) => this.handleHaveTldChange(e));

        // Custom Document Event Listeners
        document.addEventListener('pengguna.request_create', () => {
            this.penggunaForm.showAdd();
        });

        document.addEventListener('pengguna.request_edit', (event) => {
            this.penggunaForm.showEdit(event.detail?.data?.id);
        });

        document.addEventListener('pengguna.saved', () => {
            this.tldSelector.reload();
            this.tldSelector.show();
        });

        document.addEventListener('pengguna.pilih', (event) => {
            const obj = event.detail.html;
            this.btnPilihPengguna(obj);
        });
    }

    /**
     * Clear & hide fields inside the form
     */
    resetForm() {
        this.dom.formTipeKontrak.hide();
        this.dom.formPic.hide();
        this.dom.formNoHp.hide();
        this.dom.formAlamat.hide();
        this.dom.formPeriodeNext.hide();
        this.dom.formPeriode.hide();
        this.dom.formJenisTld.hide();
        this.dom.formJumPengguna.hide();
        this.dom.formJumKontrol.hide();
        this.dom.formTotalHarga.hide();
        this.dom.formPeriode1.hide();
        this.dom.formPeriode2.hide();

        this.dom.formSwitch.hide();

        $('#no_kontrak').val('');
        $('#durasi').val('');
        this.dom.jenisTld.val('');
        this.dom.jumKontrol.val('');
        $('#pic').val('');
        $('#nohp').val('');
        this.dom.periodeNext.val('');
        $('#periode_1').val('');
        $('#periode_2').val('');
        this.dom.totalHarga.val('');
        this.dom.zeroCek.val('');
    }

    /**
     * Handle address dropdown change
     */
    handleAlamatChange(e) {
        if (dataPermohonan) {
            const perusahaan = dataPermohonan.pelanggan.perusahaan;
            const key = e.target.value;
            if (perusahaan.alamat[key]) {
                this.dom.txtAlamat.val(`${perusahaan.alamat[key].alamat}, ${perusahaan.alamat[key].kode_pos}`);
            } else {
                this.dom.txtAlamat.val('');
            }
        }
    }

    /**
     * Handle primary service dropdown change to fetch child services
     */
    handleJenisLayananChange(e) {
        const jenisLayanan = e.target.value;

        if (jenisLayanan === '') {
            this.dom.formInputan.addClass('d-none').removeClass('d-block');
            this.dom.jenisLayanan2.html('<option value="">Pilih</option>');
            return;
        }

        if (dataPermohonan.layanan_jasa) return;

        spinner('show', $('#label-jenis-layanan-2'), { place: 'after' });
        ajaxGet(`api/v1/permohonan/getChildJenisLayanan/${jenisLayanan}`, false, (result) => {
            if (result.meta.code === 200) {
                let html = '<option value="">Pilih</option>';
                result.data.child.forEach((list) => {
                    html += `<option value='${list.jenis_layanan_hash}'>${list.name}</option>`;
                });

                this.dom.formInputan.addClass('d-none').removeClass('d-block');
                this.dom.jenisLayanan2.html(html);
                spinner('hide', $('#label-jenis-layanan-2'));
            }
        });
    }

    /**
     * Handle TLD type dropdown change to retrieve service pricing
     */
    handleJenisTldChange(e) {
        const idJenisLayanan = this.dom.jenisLayanan2.val();
        const idJenisTld = e.target.value;

        spinner('show', $('#label_total_harga'), { place: 'after' });
        if (idJenisLayanan && idJenisTld) {
            const params = { idJenisLayanan, idJenisTld };
            ajaxGet(`api/v1/permohonan/getPrice`, params, result => {
                const price = result.data.price;
                this.price = price;
                window.price = price;

                this.calcPrice();
                spinner('hide', $('#label_total_harga'));
            });
        } else {
            this.price = 0;
            window.price = 0;
            this.calcPrice();
            spinner('hide', $('#label_total_harga'));
        }
    }

    /**
     * Handle "Buat Form" action to save and initialize form layout
     */
    handleBuatForm(e) {
        spinner('show', e.target);
        const jenisLayanan = this.dom.jenisLayanan.val();
        const jenisLayanan2 = this.dom.jenisLayanan2.val();
        const layananJasa = this.dom.layananJasa.val();

        this.typeLayanan = this.dom.jenisLayanan.find(':selected').text();
        this.typeLayanan2 = this.dom.jenisLayanan2.find(':selected').text();
        this.JL = jenislayanan({ name: this.typeLayanan }, { name: this.typeLayanan2 });

        if (jenisLayanan === '' || jenisLayanan2 === '' || layananJasa === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Data berikut masih kosong: Jenis Layanan, Layanan Jasa'
            });
            return spinner('hide', e.target);
        }

        const formData = new FormData();
        formData.append('idPermohonan', this.idPermohonan);
        formData.append('jenisLayanan1', jenisLayanan);
        formData.append('jenisLayanan2', jenisLayanan2);
        formData.append('idLayanan', layananJasa);
        formData.append('status', 80);

        ajaxPost(`api/v1/permohonan/tambahPengajuan`, formData, () => {
            this.openForm();

            // Disable controls
            this.dom.jenisLayanan.attr('readonly', true).addClass('bg-secondary-subtle');
            this.dom.jenisLayanan2.attr('readonly', true).addClass('bg-secondary-subtle');
            this.dom.layananJasa.attr('readonly', true).addClass('bg-secondary-subtle');

            this.dom.divBuatForm.addClass('d-none').removeClass('d-block');
            spinner('hide', e.target);
        }, () => {
            spinner('hide', e.target);
        });
    }

    /**
     * Handle inventory item selection
     */
    handleInventorySelected(e) {
        const detail = e.detail;
        const split = detail.selected;
        const params = new FormData();

        if (detail.data_tld.jenis === 'pengguna') {
            const index = this.tmpArrTldPengguna.findIndex(d => d.index === split);
            if (index > -1) {
                params.append('id', this.tmpArrTldPengguna[index].id);
            }
        } else {
            const index = this.tmpArrTldKontrol.findIndex(d => d.index === split);
            if (index > -1) {
                params.append('id', this.tmpArrTldKontrol[index].id);
            }
        }

        params.append('id_tld', detail.data_tld.tld_hash);

        ajaxPost(`api/v1/permohonan/action_tld`, params, () => {
            if (detail.data_tld.jenis === 'pengguna') {
                this.loadPengguna();
            } else {
                this.loadKontrol();
            }
        });
    }

    /**
     * Handle have TLD toggle switch change
     */
    handleHaveTldChange(e) {
        const layananActive = jenislayanan(
            { name: this.dom.jenisLayanan.find(':selected').text() },
            { name: this.dom.jenisLayanan2.find(':selected').text() }
        );

        if (e.target.checked) {
            // Memiliki TLD
            this.dom.useZeroCek.prop('checked', false);
            if (StringZerocek === layananActive) {
                this.dom.useZeroCek.prop('checked', true);
            } else if (layananActive === 'KontrakEvaluasi' || tmpArrEvaluasi.includes(layananActive)) {
                // Untuk Kontrak evaluasi, jika memiliki TLD, zerocek menjadi optional (tampilkan switch-zerocek)
                this.dom.switchZerocek.show();
            } else {
                this.dom.switchZerocek.show();
            }
        } else {
            // Tidak memiliki TLD
            if (StringZerocek === layananActive) {
                this.dom.useZeroCek.prop('checked', true);
            } else if (layananActive === 'KontrakEvaluasi' || tmpArrEvaluasi.includes(layananActive)) {
                // Untuk Kontrak evaluasi, jika TIDAK memiliki TLD, otomatis menggunakan zerocek & sembunyikan switch
                this.dom.useZeroCek.prop('checked', true);
                this.dom.switchZerocek.hide();
            } else {
                this.dom.useZeroCek.prop('checked', false);
                this.dom.switchZerocek.hide();
            }
        }

        this.loadPengguna();
        this.loadKontrol();
    }

    /**
     * Save/Submit Form generic function
     */
    save(isDraft, eventTarget) {
        const valjenisTld = this.dom.jenisTld.val();
        const valperiodePemakaian = this.dom.periodePemakaian.attr('data-periode');
        const valPeriodeNext = this.dom.periodeNext.attr('data-periode');
        const valjumPengguna = this.dom.jumPengguna.val();
        const valjumKontrol = this.dom.jumKontrol.val();
        let valAlamat = this.dom.selectAlamat.val();
        const valtotalHarga = this.dom.totalHarga.val();
        const valHargaLayanan = this.price;
        const haveTld = this.dom.haveTld.is(':checked');
        const useZeroCek = this.dom.useZeroCek.is(':checked');

        if (!isDraft) {
            const sanityCek = [];
            const periodeNextShow = this.dom.formPeriodeNext.is(':visible');
            if (periodeNextShow && !valPeriodeNext) sanityCek.push('Periode Selanjutnya');
            if (!valjenisTld) sanityCek.push('Jenis TLD');
            if (!valperiodePemakaian) sanityCek.push('Periode Pemakaian');
            if (Number(valjumPengguna) === 0) sanityCek.push('Jumlah Pengguna');

            if (sanityCek.length > 0) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: `Data berikut masih kosong: ${sanityCek.join(', ')}`
                });
            }

            // Map address hash
            if (dataPermohonan?.pelanggan?.perusahaan?.alamat?.[valAlamat]) {
                valAlamat = dataPermohonan.pelanggan.perusahaan.alamat[valAlamat].alamat_hash;
            }
        }

        const alertTitle = isDraft ? 'Simpan permohonan sebagai draf?' : 'Apa kamu yakin?';
        const alertText = isDraft ? '' : 'Apakah Anda ingin melanjutkan tindakan ini?';
        const successText = isDraft ? 'Pengajuan disimpan sebagai draf' : 'Pengajuan berhasil dibuat';

        Swal.fire({
            title: alertTitle,
            text: alertText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('idPermohonan', this.idPermohonan);
                formData.append('alamat', valAlamat);
                formData.append('tipeKontrak', 'kontrak baru');
                formData.append('jenisTld', valjenisTld);
                formData.append('periodePemakaian', valperiodePemakaian);
                formData.append('periodeNext', valPeriodeNext);
                formData.append('jumlahPengguna', valjumPengguna);
                formData.append('jumlahKontrol', valjumKontrol);
                formData.append('hargaLayanan', valHargaLayanan);
                formData.append('totalHarga', valtotalHarga);
                formData.append('haveTld', haveTld ? 1 : 0);
                formData.append('is_zerocek', useZeroCek ? 1 : 0);
                formData.append('note', '');

                if (isDraft) {
                    formData.append('status', 80);
                }

                if (haveTld && useZeroCek) {
                    formData.append('periode', 1);
                } else {
                    formData.append('periode', useZeroCek ? 0 : 1);
                }

                spinner('show', eventTarget);
                ajaxPost(`api/v1/permohonan/tambahPengajuan`, formData, () => {
                    Swal.fire({
                        icon: 'success',
                        text: successText,
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = `${base_url}/permohonan/pengajuan`;
                    });
                }, () => {
                    spinner('hide', eventTarget);
                });
            }
        });
    }

    /**
     * Clear Selected Periode
     */
    clearPeriode() {
        this.dom.periodePemakaian.val('');
        this.dom.periodePemakaian.attr('data-periode', '');
        this.dom.periodePemakaian.attr('data-jumperiode', '');
        this.dom.btnClearPeriode.addClass('d-none').removeClass('d-block');
        this.periodeJs.addData([]);
        this.calcPrice();
    }

    /**
     * Clear Selected Next Periode
     */
    clearPeriodeNext() {
        this.dom.periodeNext.val('');
        this.dom.periodeNext.attr('data-periode', '');
        this.dom.periodeNext.attr('data-jumperiode', '');
        this.dom.btnClearPeriodeNext.addClass('d-none').removeClass('d-block');
        this.periodeNextJs.addData([]);
    }

    /**
     * Save callback from Periode Next module
     */
    simpanPeriodeNext() {
        const dataPeriode = this.periodeNextJs.getData();
        if (dataPeriode) {
            this.dom.periodeNext.attr('data-periode', JSON.stringify(dataPeriode));
            if (dataPeriode.length === 1) {
                this.dom.periodeNext.val(`${dateFormat(dataPeriode[0].start_date, 4)} - ${dateFormat(dataPeriode[0].end_date, 4)}`);
            } else {
                this.dom.periodeNext.val(dataPeriode.length + ' Periode');
            }
            this.dom.periodeNext.attr('data-jumperiode', dataPeriode.length);
            this.dom.btnClearPeriodeNext.addClass('d-block').removeClass('d-none');
        }
    }

    /**
     * Save callback from Periode module
     */
    simpanPeriode() {
        const dataPeriode = this.periodeJs.getData();
        if (dataPeriode) {
            this.dom.periodePemakaian.attr('data-periode', JSON.stringify(dataPeriode));
            if (dataPeriode.length === 1) {
                this.dom.periodePemakaian.val(`${dateFormat(dataPeriode[0].start_date, 4)} - ${dateFormat(dataPeriode[0].end_date, 4)}`);
            } else {
                this.dom.periodePemakaian.val(dataPeriode.length + ' Periode');
            }
            this.dom.periodePemakaian.attr('data-jumperiode', dataPeriode.length);
            this.dom.btnClearPeriode.addClass('d-block').removeClass('d-none');
        }

        this.calcPrice();
    }

    /**
     * Fetch list of Pengguna and render components
     */
    loadPengguna() {
        const params = { idPermohonan: this.idPermohonan };
        this.tmpArrTldPengguna = [];
        const haveTld = this.dom.haveTld.is(':checked');

        ajaxGet(`api/v1/permohonan/listPengguna`, params, result => {
            if (result.meta.code === 200) {
                let html = '';
                if (result.data) {
                    for (const [i, value] of result.data.entries()) {
                        const pengguna = value.entitas;
                        const fileKtp = pengguna.media_ktp ? `${base_url}/storage/${pengguna.media_ktp.file_path}/${pengguna.media_ktp.file_hash}` : '';

                        const dataCard = {
                            index: i,
                            idHash: value.permohonan_detail_hash,
                            name: pengguna.name,
                            divisi: pengguna.divisi?.name || '',
                            isCheckedEvaluasi: false,
                            radiasi: pengguna.radiasi?.map(r => r.nama_radiasi),
                            fileKtp: fileKtp,
                            no_seri_tld: value.tld?.no_seri_tld || '',
                            htmlDisabled: true
                        };

                        html += cardPenggunaComponent(dataCard, {
                            is_have_tld: (tmpArrEvaluasi.includes(this.JL) || StringZerocek === this.JL) && haveTld,
                            status: value.type,
                            label_tld: false,
                            is_btn_remove: true
                        });

                        this.tmpArrTldPengguna.push({
                            id: value.permohonan_detail_hash,
                            index: `tldNoSeri_${i}_pengguna`
                        });
                    }
                }

                if (result.data.length === 0) {
                    html = `
                        <div class="d-flex flex-column align-items-center py-4">
                            <span class="fw-bold text-muted">Tidak ada pengguna</span>
                        </div>
                    `;
                }
                this.dom.jumPengguna.val(result.data.length);

                this.calcPrice();
                $('#pengguna-list-container').html(html);
                showPopupReload();
            }
        });
    }

    /**
     * Delete Pengguna details
     */
    removePengguna(obj) {
        const idPengguna = $(obj).data('id');

        ajaxDelete(`api/v1/permohonan/destroyPengguna/${idPengguna}/${this.idPermohonan}`, result => {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                this.loadPengguna();
            });
        }, error => {
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(error.responseJSON.data.msg);
        });
    }

    /**
     * Delete Kontrol details
     */
    deleteKontrol(obj) {
        let idDivisi = $(obj).data('id');

        if (!idDivisi) {
            idDivisi = 'default';
        }
        ajaxDelete(`api/v1/permohonan/destroyKontrol/${this.idPermohonan}/${idDivisi}`, result => {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                this.loadKontrol();
            });
        }, error => {
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(error.responseJSON.data.msg);
        });
    }

    /**
     * Fetch list of Kontrol and render components
     */
    loadKontrol() {
        let html = '';
        const haveTld = this.dom.haveTld.is(':checked');
        this.tmpArrTldKontrol = [];

        ajaxGet(`api/v1/permohonan/listKontrol`, { idPermohonan: this.idPermohonan }, result => {
            this.arrKontrolTmp = result.data.tldPermohonan;
            let jumKontrol = 0;

            for (const [key, value] of Object.entries(this.arrKontrolTmp)) {
                const firstData = value[0];

                const data = {
                    index: key,
                    name: `Kontrol ${firstData.entitas?.name ?? ''} C`,
                    kode: 'C',
                    isCheckedEvaluasi: false,
                    tldHash: firstData.id_pengguna_divisi,
                    no_seri_tld: firstData.tld?.no_seri_tld || false,
                    htmlDisabled: true,
                    rincian: value
                };

                html += cardKontrolComponent(data, {
                    is_btn_remove: true,
                    label_tld: false,
                    add_kontrol: true,
                    is_have_tld: (tmpArrEvaluasi.includes(this.JL) || StringZerocek === this.JL) && haveTld
                });

                value.map((info, i) => {
                    this.tmpArrTldKontrol.push({
                        id: info.permohonan_detail_hash,
                        index: `tldNoSeri_${key}_${i}_kontrol`
                    });
                });

                jumKontrol += value.length;
            }

            const isEmptyKontrol = !this.arrKontrolTmp || 
                (Array.isArray(this.arrKontrolTmp) && this.arrKontrolTmp.length === 0) || 
                (typeof this.arrKontrolTmp === 'object' && Object.keys(this.arrKontrolTmp).length === 0);

            if (isEmptyKontrol) {
                html = `
                    <div class="d-flex flex-column align-items-center py-4">
                        <span class="fw-bold text-muted">Tidak ada kontrol</span>
                    </div>
                `;
            }
            this.dom.jumKontrol.val(jumKontrol);
            this.calcPrice();

            $('#kontrol-list-container').html(html);
        });
    }

    /**
     * Plus/Minus control badges click handler
     */
    changeCountKontrol(type, count, obj) {
        const id = $(obj).data('id');
        const params = new FormData();

        if (type === 'plus') {
            params.append('aksi', 'tambah');
        } else {
            if (count === 1) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Kontrol tidak bisa dihapus',
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
                return;
            }
            params.append('aksi', 'hapus');
        }

        params.append('id_permohonan', this.idPermohonan);
        if (id) {
            params.append('id_divisi', id);
        }

        params.append('jenis', 'kontrol');
        params.append('type', 'baru');

        ajaxPost('api/v1/permohonan/action_tld', params, () => {
            this.loadKontrol();
        });
    }

    /**
     * Real-time price calculation logic
     */
    calcPrice() {
        const price = this.price;
        let subTotal = price;
        const per = this.dom.periodePemakaian.attr('data-jumperiode');
        const jumlah = Number(this.dom.jumPengguna.val()) + Number(this.dom.jumKontrol.val());

        if (per) {
            subTotal *= Number(per);
        }

        if (jumlah !== 0) {
            subTotal *= jumlah;
        }
        this.dom.totalHarga.val(subTotal);

        maskReload();
    }

    /**
     * Expand/Collapse view toggler
     */
    showHideCollapse(obj) {
        const collapse = obj;
        if (!collapse.classList.contains('show')) {
            collapse.innerHTML = '<i class="bi bi-eye"></i> Tampilkan';
        } else {
            collapse.innerHTML = '<i class="bi bi-eye-slash"></i> Lebih sedikit';
        }
        collapse.classList.toggle('show');
    }

    /**
     * Show TLD selector inventory dialog
     */
    openInventory(obj, jenis) {
        const id = $(obj).data('id');
        let arr = [];
        if (jenis === 'pengguna') {
            arr = this.tmpArrTldPengguna;
        } else if (jenis === 'kontrol') {
            arr = this.tmpArrTldKontrol;
        }
        this.inventoryTld.show(id, arr, jenis);
    }

    /**
     * Select a user/pengguna and link them to permohonan
     */
    btnPilihPengguna(obj) {
        const id = $(obj).length > 0 ? $(obj).data('id') : obj;

        const params = new FormData();
        params.append('idPengguna', id);
        params.append('idPermohonan', this.idPermohonan);

        spinner('show', $(obj));
        ajaxPost(`api/v1/permohonan/tambahPengguna`, params, () => {
            this.loadPengguna();
            this.loadKontrol();
            $('#modal-add-tld-pengguna').modal('hide');
            $('#modal-add-pengguna').modal('hide');
            spinner('hide', $(obj));
        }, () => {
            spinner('hide', $(obj));
        });
    }

    /**
     * Configures the active form layout based on selected service details
     */
    openForm() {
        const layanan = this.dom.jenisLayanan2.val();
        let html = '<option value="">Pilih</option>';
        $('#form-kode-lencana-pengguna').hide();
        this.arrKontrolTmp = [];

        // Disable elements
        this.dom.jenisLayanan.attr('disabled', true).addClass('bg-secondary-subtle');
        this.dom.jenisLayanan2.attr('disabled', true).addClass('bg-secondary-subtle');
        this.dom.layananJasa.attr('disabled', true).addClass('bg-secondary-subtle');

        if (layanan === '') {
            this.dom.formInputan.addClass('d-none').removeClass('d-block');
        } else {
            ajaxGet(`api/v1/permohonan/getJenisTld/${layanan}`, false, result => {
                if (result.meta.code === 200) {
                    result.data.forEach(value => {
                        html += `<option value="${value.jenis_tld.jenis_tld_hash}">${value.jenis_tld.name}</option>`;
                    });

                    this.dom.jenisTld.html(html);

                    if (tmpArrSewa.includes(this.JL)) {
                        this.dom.useZeroCek.prop('checked', true);
                        this.dom.haveTld.prop('checked', false);
                        this.loadKontrol();
                        this.loadPengguna();
                    } else {
                        // Trigger haveTld change to establish correct UI visibility
                        dataPermohonan.is_have_tld === 0 ? this.dom.haveTld.prop('checked', false).trigger('change') : this.dom.haveTld.prop('checked', true).trigger('change');

                        // Set the actual saved zerocek value
                        dataPermohonan.is_zerocek === 0 ? this.dom.useZeroCek.prop('checked', false) : this.dom.useZeroCek.prop('checked', true);

                        this.dom.formSwitch.show();
                    }

                    switch (this.typeLayanan.toLowerCase()) {
                        case 'kontrak':
                            this.dom.jenisTld.val(dataPermohonan.jenis_tld?.jenis_tld_hash).trigger('change');
                            this.periodeJs.addData(dataPermohonan.periode_pemakaian);
                            this.simpanPeriode();

                            this.dom.btnAddPengguna.addClass('d-block').removeClass('d-none');
                            this.dom.formTipeKontrak.show();
                            this.dom.formPeriode.show();
                            this.dom.formJenisTld.show();
                            this.dom.formJumPengguna.show();
                            this.dom.formJumKontrol.show();
                            this.dom.formTotalHarga.show();
                            break;

                        case 'evaluasi':
                            this.dom.jenisTld.val(dataPermohonan.jenis_tld?.jenis_tld_hash).trigger('change');
                            this.periodeJs.addData(dataPermohonan.periode_pemakaian);
                            this.simpanPeriode();
                            this.periodeNextJs.addData(dataPermohonan.periode_next);
                            this.simpanPeriodeNext();

                            this.dom.formTipeKontrak.show();
                            this.dom.formPeriode.show();
                            this.dom.formJenisTld.show();
                            this.dom.formJumKontrol.show();
                            this.dom.formJumPengguna.show();
                            this.dom.formAlamat.show();
                            this.dom.formTotalHarga.show();
                            this.dom.formPeriodeNext.show();
                            this.periodeJs.maxPeriode = 1;
                            this.dom.formJenisTld.addClass('col-md-12').removeClass('col-md-6');
                            this.dom.formSwitch.show();
                            break;

                        case 'zero cek':
                            this.dom.formTipeKontrak.show();
                            this.dom.formPeriode.show();
                            this.dom.formJenisTld.show();
                            this.dom.formJumKontrol.show();
                            this.dom.formJumPengguna.show();
                            this.dom.formAlamat.show();
                            this.dom.formTotalHarga.show();
                            this.periodeJs.maxPeriode = 2;
                            this.dom.useZeroCek.prop('checked', true);
                            this.dom.switchZerocek.hide();
                            break;

                        case 'adendum':
                            this.dom.btnAddPengguna.addClass('d-block').removeClass('d-none');
                            this.dom.formTipeKontrak.show();
                            this.dom.formPeriode.show();
                            this.dom.formJenisTld.show();
                            this.dom.formJumPengguna.show();
                            this.dom.formJumKontrol.show();
                            this.dom.formTotalHarga.show();
                            break;

                        case 'pembelian':
                            this.dom.formJenisTld.show();
                            this.dom.formJumPengguna.show();
                            this.dom.formJumKontrol.show();
                            this.dom.formPeriode1.show();
                            this.dom.formPeriode2.show();
                            this.dom.formTotalHarga.show();
                            break;
                    }

                    this.dom.formInputan.addClass('d-block').removeClass('d-none');
                }
            });
        }
    }

    /**
     * Check current permohonan service status and setup layouts accordingly
     */
    cekLayanan() {
        if (dataPermohonan.layanan_jasa) {
            $('#id_layanan').val(dataPermohonan.layanan_jasa.layanan_hash).trigger('change');
            this.dom.jenisLayanan.val(dataPermohonan.jenis_layanan_parent.jenis_layanan_hash).trigger('change');
            this.dom.jenisLayanan2.html(`<option value="${dataPermohonan.jenis_layanan.jenis_layanan_hash}">${dataPermohonan.jenis_layanan.name}</option>`);

            this.typeLayanan = dataPermohonan.jenis_layanan_parent.name;
            this.typeLayanan2 = dataPermohonan.jenis_layanan.name;
            this.JL = jenislayanan(dataPermohonan.jenis_layanan_parent, dataPermohonan.jenis_layanan);

            this.dom.divBuatForm.addClass('d-none').removeClass('d-block');
            this.openForm();
        }
    }

    /**
     * Destroy Permohonan completely
     */
    remove() {
        ajaxDelete(`api/v1/permohonan/destroyPermohonan/${this.idPermohonan}`, result => {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                window.location.href = `${base_url}/permohonan/pengajuan`;
            });
        }, error => {
            const result = error.responseJSON;
            if (result?.meta?.code && result.meta.code === 500) {
                Swal.fire({ icon: "error", text: 'Server error' });
                console.error(result.data.msg);
            } else {
                Swal.fire({ icon: "error", text: 'Server error' });
                console.error(result?.message || 'Unknown error');
            }
        });
    }
}

// Global scope instantiation & bridge logic mapping
let manager;
$(function () {
    manager = new PengajuanTambahManager();
    manager.init();
});

// Bridge to maintain support for inline HTML onclick calls
window.remove = () => manager.remove();
window.removePengguna = (obj) => manager.removePengguna(obj);
window.deleteKontrol = (obj) => manager.deleteKontrol(obj);
window.changeCountKontrol = (type, count, obj) => manager.changeCountKontrol(type, count, obj);
window.openInventory = (obj, jenis) => manager.openInventory(obj, jenis);
window.showHideCollapse = (obj) => manager.showHideCollapse(obj);
window.btnPilihPengguna = (obj) => manager.btnPilihPengguna(obj);
