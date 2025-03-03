const arrSelectDocument = [];
const arrDocCustom = [];
let arrPeriode = permohonan.periode_pemakaian;

if(permohonan.tipe_kontrak == 'kontrak lama') {
    arrPeriode = permohonan.kontrak.periode;
}
let mPeriode = false;
$(function () {
    mPeriode = new Periode(arrPeriode, {dataonly: true});
    load_form();

    $('#select_alamat').on('change', obj => {
        if (permohonan) {
            const perusahaan = permohonan.pelanggan.perusahaan;

            $('#alamatTujuan').val(perusahaan.alamat[obj.target.value].alamat + ", " + perusahaan.alamat[obj.target.value].kode_pos);
        }
    });

})

function load_form() {
    // Inisialisasi Alamat
    let htmlAlamat = '<option value="">Pilih alamat</option>';
    for (const [i, value] of permohonan.pelanggan.perusahaan.alamat.entries()) {
        htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
    }
    $('#select_alamat').html(htmlAlamat);

    $('#list-document').empty();
    let htmlDisabled = '';
    if(permohonan.tipe_kontrak == 'kontrak lama'){
        // htmlDisabled = 'disabled';
    }
    // list document TLD
    let checkedTld = permohonan.pengiriman ? 'disabled' : 'checked';
    let tldKontrol = ``;
    for (const list of permohonan.tldKontrol) {
        tldKontrol += `
            <div class="w-50 pe-1">
                <select class="form-select kodeTldKontrol" name="kodeTldKontrol" ${htmlDisabled}>
                    <option value="${list.tld_hash}" selected>${list.kode_lencana}</option>
                </select>
            </div>
        `;
    }

    let tldPengguna = ``;
    for (const list of permohonan.pengguna){
        tldPengguna += `
            <div class="w-50 pe-1">
                <select class="form-select kodeTldPengguna" name="kodeTldPengguna" data-id="${list.permohonan_pengguna_hash}" ${htmlDisabled}>
                    <option value="${list.tld_pengguna.tld_hash}" selected>${list.tld_pengguna.kode_lencana}</option>
                </select>
            </div>
        `;
    }

    htmlTld = `
    <div class="border shadow-sm py-2 rounded mb-2">
        <div
            class="d-flex justify-content-between align-items-center px-2">
            <div>
                <input class="form-check-input me-2" type="checkbox"
                    data-jenis="tld" data-id="${permohonan.permohonan_hash}"
                    id="selectDocumentTld" name="selectDocument" onclick="updateSelectDocument()" ${checkedTld}>
                <span class="fw-semibold fs-6">TLD</span>
                <small class="text-body-tertiary"> - ${permohonan.jumlah_pengguna} Pengguna + ${permohonan.jumlah_kontrol} Kontrol</small>
                <small>${statusFormat('pengiriman', permohonan.pengiriman?.status)}</small>
            </div>
            <div class="d-flex align-items-center gap-3 text-secondary">
            </div>
        </div>
        <div id="listTld" class="row">
            <div class="ps-5 text-info"><small>Di isi dengan nomer seri TLD yang sesuai</small></div>
            <div class="row">
                <div class="col-md-6">
                    <label class="px-4">TLD Pengguna</label>
                    <div class="px-4 pt-2 flex-wrap d-flex row-gap-2">
                        ${tldPengguna}
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="px-4">TLD kontrol</label>
                    <div class="px-4 pt-2 flex-wrap d-flex row-gap-2">
                        ${tldKontrol}
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;
    $('#list-document').append(htmlTld);

    _tldKontrol();
    _tldPengguna();

    // list document invoice
    let htmlInvoice = '';
    let urlLaporanInvoice = permohonan.invoice?.status == 5 ? `<a href="${base_url}/laporan/invoice/${permohonan.invoice?.keuangan_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Invoice</a>` : '<i class="bi bi-printer-fill"></i> Cetak Invoice';
    let checkedInvoice = permohonan.invoice?.status == 5 ? (permohonan.invoice?.pengiriman ? 'disabled' : 'checked') : 'disabled';
    permohonan.invoice ? htmlInvoice = `
        <div
            class="border shadow-sm py-2 d-flex justify-content-between align-items-center px-2 rounded mb-2">
            <div>
                <input class="form-check-input me-2" type="checkbox"
                    data-jenis="invoice" data-id="${permohonan.invoice.keuangan_hash}"
                    id="selectDocumentInvoice" name="selectDocument" onclick="updateSelectDocument()" ${checkedInvoice}>
                <span class="fw-semibold fs-6">Invoice</span>
                <small class="text-body-tertiary"> - ${permohonan.invoice.no_invoice}</small>
                <small>${statusFormat('pengiriman', permohonan.invoice.pengiriman?.status)}</small>
            </div>
            <div class="d-flex align-items-center gap-3 text-secondary">
                <small><i class="bi bi-calendar-fill"></i> ${dateFormat(permohonan.invoice.created_at, 4)}</small>
                <small>${statusFormat('invoice', permohonan.invoice.status)}</small>
                <small class="bg-body-tertiary rounded-pill ${permohonan.invoice.status == 5 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlLaporanInvoice}</small>
            </div>
        </div>
    ` : false;
    $('#list-document').append(htmlInvoice);

    // List Document LHU
    let htmlLhu = '';
    let checkedLhu = 'disabled';
    let urlDocLhu = '<i class="bi bi-printer-fill"></i> Cetak LHU';
    let findPeriode = arrPeriode[permohonan.lhu?.periode];

    if(permohonan.lhu?.status == 3){
        checkedLhu = 'checked';
        urlDocLhu = `<a href="${base_url}/storage/${permohonan.lhu.media.file_path}/${permohonan.lhu.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak LHU</a>`;
    }

    permohonan.lhu ? htmlLhu = `
        <div class="border shadow-sm py-2 rounded mb-2">
            <div class="d-flex justify-content-between align-items-center px-2">
                <div>
                    <input class="form-check-input me-2" type="checkbox"
                        data-jenis="lhu" data-id="${permohonan.lhu.penyelia_hash}"
                        id="selectDocumentLHU" name="selectDocument" onclick="updateSelectDocument()" ${checkedLhu}>
                    <span class="fw-semibold fs-6">LHU</span>
                    <small class="text-body-tertiary"> - ${permohonan.lhu.periode == 0 ? 'Zero Cek' : `Periode ${permohonan.lhu.periode} (${findPeriode?.start_date ? dateFormat(findPeriode.start_date, 4) : '-'} - ${findPeriode?.end_date ? dateFormat(findPeriode.end_date, 4) : '-'})`}</small>
                    <small>${statusFormat('pengiriman', permohonan.lhu.pengiriman?.status)}</small>
                </div>
                <div class="d-flex align-items-center gap-3 text-secondary">
                    <small><i class="bi bi-calendar-fill"></i> ${dateFormat(permohonan.lhu.created_at, 4)}</small>
                    <small>${statusFormat('penyelia', permohonan.lhu.status)}</small>
                    <!-- <small class="bg-body-tertiary rounded-pill ${permohonan.lhu.status == 3 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlDocLhu}</small> -->
                </div>
            </div>
        </div>
    ` : false;
    $('#list-document').append(htmlLhu);

    // List document custom (akan mengikat ke id pengiriman yang ada di permohonannya)
    let htmlCustom = '';
    let checkedCustom = permohonan.pengiriman ? 'disabled' : 'checked';
    if(permohonan.file_lhu){
        arrDocCustom.push({jenis: "lhu zero cek", media: permohonan.file_lhu});
    }

    for (const custom of arrDocCustom) {
        let urlDocCustom = custom.media ? `<a href="${base_url}/storage/${custom.media.file_path}/${custom.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Document</a>` : false;
        htmlCustom += `
            <div class="border shadow-sm py-2 rounded mb-2">
                <div
                    class="d-flex justify-content-between align-items-center px-2">
                    <div>
                        <input class="form-check-input me-2" type="checkbox"
                            data-jenis="${custom.jenis}" data-id="${permohonan.permohonan_hash}"
                            id="selectDocumentCustom" name="selectDocument" ${checkedCustom} disabled>
                        <span class="fw-semibold fs-6">${custom.jenis}</span>
                        <small class="text-body-tertiary"></small>
                        <small>${statusFormat('pengiriman', permohonan.pengiriman?.status)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-3 text-secondary">
                        ${urlDocCustom ? '<small class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2">'+urlDocCustom+'</small>' : ''}
                    </div>
                </div>
            </div>
        `;
    }
    $('#list-document').append(htmlCustom);

    updateSelectDocument();
}

function updateSelectDocument(){
    let checkedDokumen = $('input[name="selectDocument"]');
    
    for (const doc of checkedDokumen) {
        let jenis = doc.dataset.jenis;
        let id = doc.dataset.id;
        let periode = false;
        let tldPengguna = false;
        let tldKontrol = false;

        switch (jenis) {
            case 'lhu':
                periode = permohonan.lhu.periode;
                if(doc.checked){
                    $('#btnCetakSurat').addClass('d-block').removeClass('d-none');
                }else{
                    $('#btnCetakSurat').addClass('d-none').removeClass('d-block');
                }
                break;
            case 'tld':
                if(permohonan.tipe_kontrak == 'kontrak lama'){
                    periode = permohonan.periode;
                }else{
                    periode = 0;
                }

                if(doc.checked){
                    $('#listTld').addClass('d-flex').removeClass('d-none');
                }else{
                    $('#listTld').addClass('d-none').removeClass('d-flex');
                }

                tldPengguna = $('select[name="kodeTldPengguna"]').map(function() {
                    return {
                        id: $(this).data('id'),
                        tld: $(this).val()
                    };
                }).get();

                tldKontrol = $('select[name="kodeTldKontrol"]').map(function() {
                    return {
                        tld: $(this).val()
                    };
                }).get();

                break;
            default:
                periode = permohonan.periode;
                break;
        }

        let getIndex = arrSelectDocument.findIndex(d => d.jenis == jenis);
        let tmp = {jenis: jenis, periode: periode, id: id, tldPengguna: tldPengguna, tldKontrol: tldKontrol};
        if(doc.checked){
            if(getIndex != -1){
                arrSelectDocument[getIndex] = tmp;
            }else{
                arrSelectDocument.push(tmp);
            }
        }else{
            if(getIndex != -1){
                arrSelectDocument.splice(getIndex, 1);
            }
        }
    }
    
}

function buatPengiriman(obj){
    const alamat = $('#select_alamat').val();

    if(alamat == '') {
        return Swal.fire({icon: 'warning',text: `Harap pilih alamat`});
    }
    
    if(arrSelectDocument.length == 0){
        return Swal.fire({icon: 'warning',text: `Harap tambahkan document yang akan dikirim`});
    }
    
    updateSelectDocument();
    Swal.fire({
        title: 'Konfirmasi Pengiriman',
        text: "Apakah Anda yakin ingin menjadwalkan pengiriman ini?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, jadwalkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            let dAlamat = permohonan.pelanggan.perusahaan.alamat[alamat];
            
            const params = new FormData();
            params.append('idPengiriman', $('#no_pengiriman').val());
            params.append('idPermohonan', permohonan.permohonan_hash);
            params.append('alamat', dAlamat.alamat_hash);
            params.append('tujuan', permohonan.pelanggan.id);
            params.append('status', 3);
            params.append('detail', JSON.stringify(arrSelectDocument));
            permohonan.kontrak ? params.append('idKontrak', permohonan.kontrak.kontrak_hash) : false;

            spinner('show', $(obj));
            ajaxPost('api/v1/pengiriman/action', params, result => {
                Swal.fire({
                    icon: 'success',
                    text: `Pengiriman di jadwalkan`,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = `${base_url}/staff/pengiriman`;
                });
            }, error => {
                spinner('hide', $(obj));
            });
        }
    });
}

function _tldKontrol() {
    $('.kodeTldKontrol').select2({
        theme: "bootstrap-5",
        tags: true,
        placeholder: "Pilih Kode lencana",
        createTag: (params) => {
            return {
                id: params.term,
                text: params.term,
                newTag: true
            };
        },
        maximumSelectionLength: 2,
        ajax: {
            url: `${base_url}/api/v1/tld/searchTld`,
            type: "GET",
            dataType: "json",
            processing: true,
            serverSide: true,
            delay: 250,
            headers: {
                'Authorization': `Bearer ${bearer}`,
                'Content-Type': 'application/json'
            },
            data: function(params) {
                let queryParams = {
                    kode_lencana: params.term,
                    jenis: 'kontrol'
                }
                return queryParams;
            },
            processResults: function(response, params){
                let items = [];
                for (const data of response.data) {
                    items.push({
                        id : data.tld_hash,
                        text : data.kode_lencana,
                        status : data.status,
                        disabled : data.status == 1 ? true : false
                    });
                }
                return {
                    results: items
                };
            }
        },
        templateResult: _templateTld
    })
}

function _tldPengguna() {
    $('.kodeTldPengguna').select2({
        theme: "bootstrap-5",
        tags: true,
        placeholder: "Pilih Kode lencana",
        createTag: (params) => {
            return {
                id: params.term,
                text: params.term,
                newTag: true
            };
        },
        maximumSelectionLength: 2,
        ajax: {
            url: `${base_url}/api/v1/tld/searchTld`,
            type: "GET",
            dataType: "json",
            processing: true,
            serverSide: true,
            delay: 250,
            headers: {
                'Authorization': `Bearer ${bearer}`,
                'Content-Type': 'application/json'
            },
            data: function(params) {
                let queryParams = {
                    kode_lencana: params.term,
                    jenis: 'pengguna'
                }
                return queryParams;
            },
            processResults: function(response, params){
                let items = [];
                for (const data of response.data) {
                    items.push({
                        id : data.tld_hash,
                        text : data.kode_lencana,
                        status : data.status,
                        disabled : data.status == 1 ? true : false
                    });
                }
                return {
                    results: items
                };
            }
        },
        templateResult: _templateTld
    })
}

function _templateTld(state){
    if(!state.id){
        return state.text;
    }

    let content = $(`
        <div class="d-flex justify-content-between">
            <div>${state.text}</div>
            <div>${state.status == 1 ? '<span class="badge rounded-pill text-bg-success">Digunakan</span>' : ''}</div>
        </div>
    `);
    return content;
}