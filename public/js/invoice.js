class Invoice {
    constructor(options = {}) {
        this.options = {
            modal: options.modal ?? true
        }

        this._initializeProperties();
        this._loadMethodePembayaran();
        this._createCustomEvents();

        if(this.options.modal){
            $('body').append(this.modalCreate());
        }

        this._bindEventListeners();
    }

    // Initialize class properties with default values
    _initializeProperties() {
        this.dataKeuangan = null;
        this.invoiceMode = '';
        this.arrDiskon = [];
        this.ppn = false;
        this.pph = false;
        this.jumTotal = 0;
        this.signaturePad = null;
        this.uploadFaktur = false;
        this.methodePembayaran = false;
    }

    _loadMethodePembayaran() {
        ajaxGet('api/v1/keuangan/listJenisPembayaran', false, result => {
            this.methodePembayaran = result.data;
        });
    }

    // Create custom events for invoice actions
    _createCustomEvents() {
        this.eventSimpan = new CustomEvent('invoice.simpan', {});
        this.eventTolak = new CustomEvent('invoice.tolak', {});
    }

    // Centralize event binding
    _bindEventListeners() {
        // $('#btnTambahFaktur').on('click', this._handleFakturUpload.bind(this));
        $('#diskonModal').on('hide.bs.modal', () => $('#invoiceModal').modal('show'));
        $('#modal-verif-invalid').on('hide.bs.modal', () => $('#modal-verif-invoice').modal('show'));
        $('#btnInvoiceClose').on('click', this.closeInvoiceModal.bind(this));
        $('#btnTambahDiskon').on('click', this.tambahDiskon.bind(this));
        $('#btnTolakInvoice').on('click', this.tolakInvoice.bind(this));
        $('#invoiceModal').one('show.bs.modal', showPopupReload);
    }

    // Handle faktur document upload
    _handleFakturUpload(event) {
        const imgTmp = $('#uploadDocumentFaktur')[0].files[0];
        const $target = $(event.target);

        spinner('show', $target);

        if (!imgTmp) {
            spinner('hide', $target);
            return;
        }

        const params = new FormData();
        params.append('faktur', imgTmp);
        params.append('idHash', this.dataKeuangan?.keuangan_hash);

        ajaxPost(
            'api/v1/keuangan/uploadFaktur',
            params,
            this._onFakturUploadSuccess.bind(this, $target),
            this._onFakturUploadError.bind(this, $target)
        );
    }

    // Success handler for faktur upload
    _onFakturUploadSuccess($target, result) {
        if (result.meta.code === 200) {
            this.loadPreviewDoc(this.dataKeuangan?.keuangan_hash);
            $('#uploadDocumentFaktur').val('');
        }
        spinner('hide', $target);
    }

    // Error handler for faktur upload
    _onFakturUploadError($target, error) {
        spinner('hide', $target);
    }

    addData(data) {
        this.dataKeuangan = data;
    }

    open(mode){
        this.invoiceMode = mode;
        let invoiceClass = this;
        $('#signature-container').empty();
        $('#ttd-div-manager').addClass('d-none').removeClass('d-block');
        $('#plt-div-manager').addClass('d-none').removeClass('d-block');
        $('#methode-pembayaran').addClass('d-none').removeClass('d-block');

        // Populate invoice details
        const detailsHTML = `
            <div class="col-md-6 col-12">
                <label class="fw-bolder">No Invoice</label>
                <div>${this.dataKeuangan.no_invoice || '-'}</div>
            </div>
            <div class="col-md-6 col-12">
                <label class="fw-bolder">No Kontrak</label>
                <div>${this.dataKeuangan.permohonan.kontrak?.no_kontrak || '-'}</div>
            </div>
            <div class="col-md-6 col-12">
                <label class="fw-bolder">Jenis</label>
                <div>${this.dataKeuangan.permohonan.jenis_layanan?.name || '-'}</div>
            </div>
            <div class="col-md-6 col-12">
                <label class="fw-bolder">Pengguna</label>
                <div>${this.dataKeuangan.permohonan.jumlah_pengguna || '-'}</div>
            </div>
            <div class="col-md-6 col-12">
                <label class="fw-bolder">Tipe Kontrak</label>
                <div>${this.dataKeuangan.permohonan.tipe_kontrak || '-'}</div>
            </div>
            <div class="col-md-6 col-12">
                <label class="fw-bolder">Pelanggan</label>
                <div>${this.dataKeuangan.permohonan.pelanggan?.name || '-'}</div>
            </div>
            <div class="col-md-6 col-12">
                <label class="fw-bolder">Jenis TLD</label>
                <div>${this.dataKeuangan.permohonan.jenis_tld?.name || '-'}</div>
            </div>
            <div class="col-md-6 col-12">
                <label class="fw-bolder">Instansi</label>
                <div>${this.dataKeuangan.permohonan.pelanggan?.perusahaan?.nama_perusahaan || '-'}</div>
            </div>
        `;

        $('#invoiceDetails').html(detailsHTML);

        // Set up actions based on mode
        $('#invoiceActions').empty();
        let actionsHTML = '';
        if(mode == 'create'){
            actionsHTML = `
                <div class="col-md-6 col-12 row">
                    <div class="col-6">
                        <label class="col-form-label" for="inputPpn">PPN %</label>
                        <div class="input-group">
                            <input type="text" name="inputPpn" id="inputPpn" class="form-control maskPercent" value="11" autocomplete="off">
                            <span class="input-group-text"><input class="form-check-input m-0" type="checkbox" id="checkPpn"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="col-form-label" for="inputPph">PPH 23 %</label>
                        <div class="input-group">
                            <input type="text" name="inputPph" id="inputPph" class="form-control maskPercent" value="2" autocomplete="off">
                            <span class="input-group-text"><input class="form-check-input m-0" type="checkbox" id="checkPph"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12 d-flex align-items-end">
                    <button class="btn btn-outline-secondary me-3" data-bs-toggle="modal" data-bs-target="#diskonModal"><i class="bi bi-plus"></i> Tambah Diskon</button>
                </div>
            `;
            $('#invoiceActions').html(actionsHTML);

            $('#checkPpn').on('change', (obj) => {
                invoiceClass.ppn = $(obj.target).is(":checked");
                $('#deskripsiInvoice').empty().append(this.updateInvoiceDescription());
            });
            $('#inputPpn').on('input', () => {
                $('#deskripsiInvoice').empty().append(this.updateInvoiceDescription());
            });

            $('#checkPph').on('change', (obj) => {
                invoiceClass.pph = $(obj.target).is(":checked");
                $('#deskripsiInvoice').empty().append(this.updateInvoiceDescription());
            });
            $('#inputPph').on('input', () => {
                $('#deskripsiInvoice').empty().append(this.updateInvoiceDescription);
            });
            $('#rincianInvoice-tab').click();

            // load metode pembayaran
            this.loadPaymentMethod();
        } else if (mode === 'verify') {
            // this.signaturePad = signature(document.getElementById("content-ttd-manager"), {
            //     text: 'Manager'
            // });

            this.signaturePad = new SignatureSelect('#signature-container', {
                inputId: 'invoice-validation-manager',
                label: 'Nyatakan Valid',
                placeholderText: 'Menunggu Validasi Manager...',
                signerUser: userActive
            });

            if(this.dataKeuangan.plt) {
                $('#plt-div-manager input').prop('checked', true);
            } else {
                $('#plt-div-manager input').prop('checked', false);
            }
            $('#ttd-div-manager').addClass('d-block').removeClass('d-none');
            $('#plt-div-manager').addClass('d-block').removeClass('d-none');

            $('#rincianInvoice-tab').click();
            $('#invoiceActions').append(this.btnPrinter());
        } else if (mode === 'detail') {
            this.signaturePad = new SignatureSelect('#signature-container', {
                defaultSig: this.dataKeuangan.ttd_image ? this.dataKeuangan.ttd_image : this.dataKeuangan.ttd,
                signerUser: this.dataKeuangan.usersig,
                signedDate: this.dataKeuangan.verif_at
            });
            $('#ttd-div-manager').addClass('d-block').removeClass('d-none');

            $('#rincianInvoice-tab').click();
            this.showPaymentProof();
            $('#invoiceActions').append(this.btnPrinter());
        } else if (mode === 'verifStaff'){
            if(this.dataKeuangan.ttd){
                this.signaturePad = new SignatureSelect('#signature-container', {
                    defaultSig: this.dataKeuangan.ttd_image ? this.dataKeuangan.ttd_image : this.dataKeuangan.ttd,
                    signerUser: this.dataKeuangan.usersig,
                    signedDate: this.dataKeuangan.verif_at
                });
                $('#ttd-div-manager').addClass('d-block').removeClass('d-none');
            }

            $('#buktiPayment-tab').click();
            this.showPaymentProof();
            $('#invoiceActions').append(this.btnPrinter());
        }

        // Set Up upload document
        if(this.dataKeuangan.status === 7) { // untuk status upload faktur
            if(!this.uploadFaktur) {
                this.uploadFaktur = new UploadComponent('uploadDocumentFaktur', {
                    allowedFileExtensions: ['pdf'],
                    camera: false,
                    data: this.dataKeuangan.media,
                    urlUpload: {
                        url: `api/v1/keuangan/uploadFaktur`,
                        urlDestroy: `api/v1/keuangan/destroyFaktur`,
                        idHash: this.dataKeuangan.keuangan_hash
                    }
                })
            }
            $('#docFaktur-tab').click();
        }else{
            if(!this.uploadFaktur) {
                this.uploadFaktur = new UploadComponent('uploadDocumentFaktur', {
                    mode: 'preview',
                    camera: false,
                    data: this.dataKeuangan.media
                });
            }
        }

        // Set up footer buttons
        let footerHTML = '';
        if (mode === 'create') {
            footerHTML = `
                <button type="button" class="btn btn-primary" id="btnSimpanInvoice">Simpan</button>
            `;
        } else if (mode === 'verify' || mode === 'verifStaff') {
            footerHTML = `
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-verif-invalid">Tolak</button>
                <button type="button" class="btn btn-success" id="btnSimpanInvoice">Setujui</button>
            `;
        }

        if(this.dataKeuangan.status === 7) {
            footerHTML = `
                <button type="button" class="btn btn-primary" id="btnSimpanInvoice">Simpan</button>
            `;
        }
        $('#modalFooter').html(footerHTML);

        $('#btnSimpanInvoice').on('click', obj => {
            this.simpanInvoice(obj.target);
        })

        this.loadPreviewDoc();
        $('#deskripsiInvoice').empty().append(this.updateInvoiceDescription());
        maskReload();

        $('#invoiceModal').modal('show');
    }

    btnPrinter(){
        let div1 = document.createElement('div');
        div1.className = 'col-md-12 d-flex justify-content-end mt-2';

        let btnPrint = document.createElement('button');
        btnPrint.className = 'btn btn-outline-info me-3';
        btnPrint.innerHTML = `<i class="bi bi-printer-fill"></i> Cetak Invoice`;
        btnPrint.onclick = () => {
            this.print();
        };

        div1.append(btnPrint);
        return div1;
    }

    tambahDiskon() {
        const namaDiskon = $('#inputNamaDiskon').val();
        const diskon = $('#inputJumDiskon').val();
        if(namaDiskon != '' && diskon != ''){
            this.arrDiskon.push({
                name: namaDiskon,
                diskon: diskon
            });

            $('#deskripsiInvoice').empty().append(this.updateInvoiceDescription());
            $('#diskonModal').modal('hide');
            $('#inputNamaDiskon').val("");
            $('#inputJumDiskon').val("");
        }else{
            Swal.fire({
                icon: 'warning',
                text: 'Harap isi diskon'
            });
        }
    }

    removeDiskon(index) {
        this.arrDiskon.splice(index, 1);
        $('#deskripsiInvoice').empty().append(this.updateInvoiceDescription());
    }

    showPaymentProof() {
        if (this.dataKeuangan.media_bukti_bayar) {
            let media = this.dataKeuangan.media_bukti_bayar;
            let mediaPph = this.dataKeuangan.media_bukti_bayar_pph;

            $('#paymentProofImage').empty();
            let previewBukti = new UploadComponent('paymentProofImage', {
                mode: 'preview',
                data: media
            });

            $('#paymentPphProof').empty();
            let previewPph = new UploadComponent('paymentPphProof', {
                mode: 'preview',
                data: mediaPph
            });
            showPopupReload();
        }
    }

    loadPaymentMethod() {
        $('#methode-pembayaran-select').empty();
        $('#methode-pembayaran-select').append(`
            <option value="">Pilih Metode Pembayaran</option>
        `);
        for (const metode of this.methodePembayaran) {
            $('#methode-pembayaran-select').append(`
                <option value="${metode.jenis_pembayaran_hash}">${metode.name}</option>
            `);
        }
        this.changeMethodePembayaran();
        $('#methode-pembayaran').removeClass('d-none');
        // Event
        $('#methode-pembayaran-select').off('change').on('change', () => this.changeMethodePembayaran());
    }
    changeMethodePembayaran() {
        let selectedMetodePembayaran = $('#methode-pembayaran-select').val();
        let find = this.methodePembayaran.find(d => d.jenis_pembayaran_hash == selectedMetodePembayaran);

        if(find){
            let variabels = find.variables;
            let content = find.content;
            if(variabels) {
                let html = '';
                for (const variabel of variabels) {
                    // reset to string with space
                    content = content.replace(new RegExp(`@${variabel}`, 'g'), '_____________');
                    const formattedVariable = variabel.replace(/_/g, ' ');
                    html += `
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="variabel_content" data-key="${variabel}" id="variabel_${variabel}" placeholder="${formattedVariable}">
                        </div>
                    `;
                }

                $('#inputMetodePembayaran').html(html);

                $('input[name="variabel_content"]').off('input').on('input', () => {
                    let content = find.content;
                    for (const variabel of variabels) {
                        content = content.replace(new RegExp(`@${variabel}`, 'g'), $('#variabel_' + variabel).val() || '_____________');
                    }
                    $('#showMetodePembayaran').html(content);
                });
            } else {
                $('#inputMetodePembayaran').html('');
            }

            $('#showMetodePembayaran').html(content);
            $('#collapseMetodePembayaran').collapse('show');
        } else {
            $('#showMetodePembayaran').html('');
            $('#inputMetodePembayaran').html('');
            $('#collapseMetodePembayaran').collapse('hide');
        }
    }

    updateInvoiceDescription() {
        const { permohonan } = this.dataKeuangan;

        const hargaLayanan = permohonan.harga_layanan;
        const qty = permohonan.jumlah_kontrol + permohonan.jumlah_pengguna;
        const jumLayanan = permohonan.total_harga;
        const periode = permohonan.periode_pemakaian;

        let jumDiskon = 0;
        let jumPph = 0;
        let jumPpn = 0;

        // Create the table body to append rows
        const tableBody = document.createElement('tbody');

        // First row with service details
        const serviceRow = document.createElement('tr');

        const serviceNameTh = document.createElement('th');
        serviceNameTh.classList.add('text-start');
        serviceNameTh.textContent = permohonan.layanan_jasa.nama_layanan;

        const priceTd = document.createElement('td');
        priceTd.textContent = formatRupiah(hargaLayanan);

        const qtyTd = document.createElement('td');
        qtyTd.textContent = qty;

        const periodTd = document.createElement('td');
        periodTd.textContent = periode.length;

        const totalPriceTd = document.createElement('td');
        totalPriceTd.textContent = formatRupiah(jumLayanan);

        serviceRow.append(serviceNameTh, priceTd, qtyTd, periodTd, totalPriceTd);
        tableBody.appendChild(serviceRow);

        // Handle discounts
        const arrDiskon = this.dataKeuangan.diskon.length != 0
            ? this.dataKeuangan.diskon
            : this.arrDiskon;

        // Add discount rows
        arrDiskon.forEach((diskon, i) => {
            const countDiskon = jumLayanan * (diskon.diskon / 100);
            jumDiskon += countDiskon;

            const discountRow = document.createElement('tr');

            const discountNameTh = document.createElement('th');
            discountNameTh.classList.add('text-start');
            discountNameTh.textContent = `${diskon.name} ${diskon.diskon}% `;

            // Add remove discount button for create mode
            if (this.invoiceMode === 'create') {
                const removeButton = document.createElement('i');
                removeButton.classList.add('bi', 'bi-x-circle-fill', 'text-danger');
                removeButton.setAttribute('type', 'button');
                removeButton.setAttribute('id', 'btnRemoveDiskon');
                removeButton.setAttribute('data-idx', i);
                removeButton.setAttribute('title', 'Hapus diskon');
                removeButton.onclick = () => {
                    this.removeDiskon(i);
                };
                discountNameTh.appendChild(removeButton);
            }

            const emptyTd1 = document.createElement('td');
            const emptyTh = document.createElement('th');
            emptyTh.setAttribute('colspan', '2');

            const discountValueTd = document.createElement('td');
            discountValueTd.textContent = `- ${formatRupiah(countDiskon)}`;

            discountRow.append(discountNameTh, emptyTd1, emptyTh, discountValueTd);
            tableBody.appendChild(discountRow);
        });

        const jumAfterDiskon = jumLayanan - jumDiskon;

        // Handle PPH (Pajak Penghasilan)
        if (this.pph || this.dataKeuangan.pph) {
            const valPph = document.getElementById('inputPph')?.value || this.dataKeuangan.pph || 0;
            const pphRate = parseInt(valPph);
            jumPph = jumAfterDiskon * (pphRate / 100);

            const pphRow = document.createElement('tr');

            const pphNameTh = document.createElement('th');
            pphNameTh.classList.add('text-start');
            pphNameTh.textContent = `PPH 23 (${pphRate}%)`;

            const emptyTd1 = document.createElement('td');
            const emptyTd2 = document.createElement('td');
            const emptyTd3 = document.createElement('td');

            const pphValueTd = document.createElement('td');
            pphValueTd.textContent = `- ${formatRupiah(jumPph)}`;

            pphRow.append(pphNameTh, emptyTd1, emptyTd2, emptyTd3, pphValueTd);
            tableBody.appendChild(pphRow);
        }

        const jumAfterPph = jumAfterDiskon - jumPph;

        // Handle PPN (Pajak Pertambahan Nilai)
        if (this.ppn || this.dataKeuangan.ppn) {
            const valPpn = document.getElementById('inputPpn')?.value || this.dataKeuangan.ppn;
            const ppnRate = parseInt(valPpn);
            jumPpn = jumAfterPph * (ppnRate / 100);

            const ppnRow = document.createElement('tr');

            const ppnNameTh = document.createElement('th');
            ppnNameTh.classList.add('text-start');
            ppnNameTh.textContent = `PPN`;

            const emptyTd1 = document.createElement('td');
            const emptyTd2 = document.createElement('td');
            const emptyTd3 = document.createElement('td');

            const ppnValueTd = document.createElement('td');
            ppnValueTd.textContent = formatRupiah(jumPpn);

            ppnRow.append(ppnNameTh, emptyTd1, emptyTd2, emptyTd3, ppnValueTd);
            tableBody.appendChild(ppnRow);
        }

        // Calculate total price
        this.jumTotal = jumAfterPph + jumPpn;

        // Total row
        const totalRow = document.createElement('tr');

        const emptyTd1 = document.createElement('td');
        const emptyTd2 = document.createElement('td');

        const totalLabelTh = document.createElement('th');
        totalLabelTh.setAttribute('colspan', '2');
        totalLabelTh.textContent = 'Total Jumlah';

        const totalValueTd = document.createElement('td');
        totalValueTd.textContent = formatRupiah(this.jumTotal);

        totalRow.append(emptyTd1, emptyTd2, totalLabelTh, totalValueTd);
        tableBody.appendChild(totalRow);

        return tableBody.children;
    }

    loadPreviewDoc() {
        $('#loading-document').empty();
        spinner('show', $('#loading-document'), {
            width: '50px',
            height: '50px'
        });

        $('#loading-document').show();
        $('#list-document').hide();

        const invoiceClass = this;

        ajaxGet(`api/v1/keuangan/getKeuangan/${this.dataKeuangan.keuangan_hash}`, false, result => {
            $('#list-document').empty();
            if(result.meta.code == 200){
                for (const media of result.data.media) {
                    let options = {}

                    if(this.invoiceMode == 'create'){
                        options.download = false;
                        options.onRemove = () => {
                            ajaxDelete(`api/v1/keuangan/destroyFaktur/${invoiceClass.dataKeuangan.keuangan_hash}/${media.media_hash}`, result => {
                                invoiceClass.loadPreviewDoc();
                            }, error => {
                                const result = error.responseJSON;
                                if(result?.meta?.code && result?.meta?.code == 500){
                                    Swal.fire({
                                        icon: "error",
                                        text: 'Server error',
                                    });
                                    console.error(result.data.msg);
                                }else{
                                    Swal.fire({
                                        icon: "error",
                                        text: 'Server error',
                                    });
                                    console.error(error);
                                }
                                spinner(`hide`, $(obj.target));
                            });
                        }
                    }
                    let html = printMedia(media, false, options);

                    $('#list-document').append(html);
                }

                if(result.data.media.length == 0){
                    let html = `
                        <div class="d-flex flex-column align-items-center py-3">
                            <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                            <span class="fw-bold mt-3 text-muted">No Data Faktur</span>
                        </div>
                    `;

                    $('#list-document').append(html);
                }
            }

            $('#loading-document').hide();
            $('#list-document').show();
        }, error => {
            $('#loading-document').hide();
            $('#list-document').show();
        })
    }

    tolakInvoice(obj){
        let note = $('#txt_note').val();
        spinner('show', $(obj));

        let formData = new FormData();
        formData.append('status', 91);
        formData.append('note', note);
        formData.append('idKeuangan', this.dataKeuangan.keuangan_hash);
        ajaxPost(`api/v1/keuangan/action`, formData, result => {
            Swal.fire({
                icon: 'success',
                text: 'Pengajuan ditolak',
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                document.dispatchEvent(this.eventTolak);
                this.closeInvoiceModal();
                spinner('hide', $(obj));
            });
        }, error => {
            const result = error.responseJSON;
            if(result.meta.code == 500){
                spinner('hide', obj);
                Swal.fire({
                    icon: "error",
                    text: 'Server error',
                });
                console.error(result.data.msg);
            }
        })
    }

    simpanInvoice(obj) {
        const formData = new FormData();
        formData.append('idKeuangan', this.dataKeuangan.keuangan_hash);

        let textQuestion,textSuccess = '';

        switch (this.invoiceMode) {
            case 'create':
                let metodePembayaran = $('#methode-pembayaran-select').parsley().validate();
                if(metodePembayaran !== true){
                    return;
                }

                formData.append('idPermohonan', this.dataKeuangan.permohonan_hash);
                formData.append('diskon', JSON.stringify(this.arrDiskon));
                formData.append('totalHarga', this.jumTotal);
                this.ppn && formData.append('ppn', $('#inputPpn').val());
                this.pph && formData.append('pph', $('#inputPph').val());
                formData.append('status', 7);
                formData.append('metodePembayaran', $('#methode-pembayaran-select').val());
                // mengambil value variabel dari methode pembayaran
                let variabelPembayaran = Array.from($('input[name="variabel_content"]').map(function() {
                    return { [$(this).data('key')]: $(this).val() };
                }));
                if(variabelPembayaran.length > 0){
                    formData.append('variabel_pembayaran', JSON.stringify(variabelPembayaran));
                }
                textQuestion = 'Apa anda yakin ingin membuat invoice ?';
                textSuccess = 'Invoice berhasil dibuat.';
                break;
            case 'verify':
                let [ttdValue, ttdBy] = this.signaturePad.getValue();
                if(!ttdValue){
                    return Swal.fire({
                        icon: "warning",
                        text: "Harap berikan tanda tangan terlebih dahulu.",
                    });
                }
                formData.append('ttd', ttdValue);
                formData.append('ttd_by', ttdBy);
                formData.append('status', 3);
                $('#pltChecked').is(":checked") ? formData.append('plt', 1) : formData.append('plt', 0);
                textQuestion = 'Apa invoice sudah benar ?';
                textSuccess = 'Invoice berhasil diverifikasi.';
                break;
            case 'verifStaff':
                formData.append('status', 5);
                textQuestion = 'Apa invoice sudah benar ?';
                textSuccess = 'Invoice berhasil diverifikasi.';
                break;
        }

        if(this.dataKeuangan.status === 7) {
            // cek sudah uploadDokumen faktur atau belum
            if(this.dataKeuangan.media.length == 0){
                return Swal.fire({
                    icon: "warning",
                    text: "Harap unggah dokumen faktur terlebih dahulu.",
                });
            }
            formData.append('status', 2);
            textQuestion = 'Apa faktur sudah benar ?';
            textSuccess = 'Faktur berhasil diupload.';
        }

        Swal.fire({
            text: textQuestion,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Iya',
            cancelButtonText: 'Tidak',
            customClass: {
                confirmButton: 'btn btn-success mx-1',
                cancelButton: 'btn btn-danger mx-1'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then(result => {
            if(result.isConfirmed){
                spinner('show', $(obj));
                ajaxPost(`api/v1/keuangan/action`, formData, result => {
                    if(result.meta.code == 200){
                        Swal.fire({
                            icon: 'success',
                            text: textSuccess,
                            timer: 1200,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            document.dispatchEvent(this.eventSimpan);
                            this.closeInvoiceModal();
                            spinner('hide', $(obj));
                        });
                    }
                }, error => {
                    spinner('hide', obj);
                })
            }
        })
    }

    print() {
        const linkInvoice = document.createElement('a');
        linkInvoice.href = `${base_url}/laporan/invoice/${this.dataKeuangan.keuangan_hash}`;
        linkInvoice.target = `_blank`;
        linkInvoice.click();
    }

    closeInvoiceModal() {
        this.arrDiskon = [];
        this.ppn = false;
        this.pph = false;
        this.jumTotal = 0;
        this.dataKeuangan = null;
        this.signaturePad = false;
        this.invoiceMode = '';
        $('#modal-verif-invalid').modal('hide');
        $('#invoiceModal').modal('hide');
        $('#checkPpn').prop('checked', false);
        $('#checkPpn').off('change');
        $('#inputPpn').off('input');
        $('#checkPph').off('change');
        $('#inputPph').off('input');
        $('#signature-container').empty();
        $('#paymentPphProof').html(`<div class="text-center text-muted mt-3 w-100">Tidak ada file yang diupload</div>`);
        $('#paymentProofImage').html(`<div class="text-center text-muted mt-3 w-100">Tidak ada file yang diupload</div>`);

        // reset parsley
        $('#methode-pembayaran-select').parsley().reset();

        this.uploadFaktur ? this.uploadFaktur.destroy() : '';
        this.uploadFaktur = false;
    }

    modalCreate() {
        return `
            <div class="modal fade" id="invoiceModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header border-bottom-0 p-4">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-receipt-cutoff me-2 text-primary"></i>Manajemen Invoice
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" id="btnInvoiceClose"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row mx-2" id="invoiceDetails">
                                <!-- Invoice details will be dynamically inserted here -->
                            </div>
                            <hr class="my-2">
                            <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded-3 mb-4" id="myTab" role="tablist" style="border: 1px solid #e9ecef;">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-3 fw-bold" id="rincianInvoice-tab" data-bs-toggle="tab" data-bs-target="#rincianInvoice-tab-pane" type="button" role="tab">
                                        <i class="bi bi-list-check me-2"></i>Rincian Tagihan
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-3 fw-bold" id="docFaktur-tab" data-bs-toggle="tab" data-bs-target="#docFaktur-tab-pane" type="button" role="tab">
                                        <i class="bi bi-file-earmark-text me-2"></i>Dokumen Faktur
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-3 fw-bold" id="buktiPayment-tab" data-bs-toggle="tab" data-bs-target="#buktiPayment-tab-pane" type="button" role="tab">
                                        <i class="bi bi-shield-check me-2"></i>Bukti Pembayaran
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="rincianInvoice-tab-pane" role="tabpanel" aria-labelledby="rincianInvoice-tab" tabindex="0">
                                    <div class="row" id="invoiceActions">
                                        <!-- Action buttons will be dynamically inserted here -->
                                    </div>
                                    <div class="row my-2" id="methode-pembayaran" class="d-none">
                                        <div class="col-md-6">
                                            <select class="form-select" id="methode-pembayaran-select"
                                                data-parsley-required-message="Metode pembayaran harus dipilih"
                                                data-parsley-errors-container="#message-methode-pembayaran"
                                                required></select>
                                            <div class="invalid-feedback" id="message-methode-pembayaran"></div>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <div class="collapse" id="collapseMetodePembayaran">
                                                <div class="card card-body mb-2">
                                                    <div id="inputMetodePembayaran" class="mb-2 d-flex gap-2 flex-wrap"></div>
                                                    <div id="showMetodePembayaran"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border rounded p-3 mt-3">
                                        <table class="table w-100 text-center">
                                            <thead>
                                                <tr>
                                                    <th class="text-start" width="40%">Rincian</th>
                                                    <th>Harga</th>
                                                    <th>Qty</th>
                                                    <th>Periode</th>
                                                    <th>Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody id="deskripsiInvoice">
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row m-2 d-none" id="plt-div-manager">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="pltChecked">
                                            <label class="form-check-label" for="pltChecked">
                                                Plt Manager
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row my-2 d-none d-flex justify-content-center" id="ttd-div-manager">
                                        <div class="w-50" id="signature-container">

                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="docFaktur-tab-pane" role="tabpanel" aria-labelledby="docFaktur-tab" tabindex="0">
                                    <div id="uploadDocumentFaktur" class="p-3"></div>
                                </div>
                                <div class="tab-pane fade" id="buktiPayment-tab-pane" role="tabpanel" aria-labelledby="buktiPayment-tab" tabindex="0">
                                    <div id="paymentProofSection" class="mt-3 row">
                                        <div class="col-6">
                                            <h5 class="text-center">Bukti Pembayaran</h5>
                                            <div id="paymentProofImage"></div>
                                        </div>
                                        <div class="col-6">
                                            <h5 class="text-center">Bukti PPH</h5>
                                            <div id="paymentPphProof"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-center" id="modalFooter">
                        <!-- Footer buttons will be dynamically inserted here -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="diskonModal" tabindex="-1" aria-labelledby="diskonModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="diskonModalLabel">Tambah Diskon</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body px-4">
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label class="col-form-label" for="inputNamaDiskon">Nama diskon</label>
                                    <input type="text" name="inputNamaDiskon" id="inputNamaDiskon" class="form-control">
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="col-form-label" for="inputJumDiskon">Jumlah diskon %</label>
                                    <input type="text" name="inputJumDiskon" id="inputJumDiskon" class="form-control maskPercent" autocomplete="off">
                                </div>
                                <div class="col-md-12 d-flex justify-content-center">
                                    <button type="button" class="btn btn-primary" id="btnTambahDiskon"><i class="bi bi-plus"></i> Tambah</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-verif-invalid" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Invoice ditolak</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body row justify-content-center">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="col-form-label" for="txt_note">Note</label>
                                    <textarea name="txt_note" id="txt_note" rows="3" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" id="btnTolakInvoice">Return</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    on(eventName, callback = () => {}) {
        return document.addEventListener(eventName, callback);
    }
}
