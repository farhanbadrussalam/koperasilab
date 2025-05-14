const arrSelectDocument = [];
const arrDocCustom = [];
let inventoryTld = false;
let mPeriode = false;
const tmpArrTld = [];
$(function () {
    inventoryTld = new Inventory_tld({
        preview: true,
        no_kontrak: informasi.no_kontrak
    });

    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;

        $(`#tldNoSeri_${detail.selected}`).val(detail.data_tld.no_seri_tld);

        // reset tmpArrTld
        let index = tmpArrTld.findIndex(d => d.id == detail.selected);

        if(index > -1){
            tmpArrTld[index].tld = detail.data_tld.tld_hash;
        }
    })
    load_form();

    $('#select_alamat').on('change', obj => {
        if (informasi) {
            const perusahaan = informasi.pelanggan.perusahaan;

            $('#alamatTujuan').val(perusahaan.alamat[obj.target.value].alamat + ", " + perusahaan.alamat[obj.target.value].kode_pos);
        }
    });

})

function openInventory(obj, jenis){
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTld, jenis);
}

function load_form() {
    // Inisialisasi Alamat
    let htmlAlamat = '<option value="">Pilih alamat</option>';
    for (const [i, value] of informasi.pelanggan.perusahaan.alamat.entries()) {
        htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
    }
    $('#select_alamat').html(htmlAlamat);

    $('#list-document').empty();
    let htmlDisabled = '';
    if(informasi.tipe_kontrak == 'kontrak lama'){
        // htmlDisabled = 'disabled';
    }

    // filter untuk memisahkan antara tld pengguna dan tld kontrol
    let tldPengguna = [];
    let tldKontrol = [];
    let kontrakPeriode = [];
    if(informasi.kontrak){
        tldPengguna = informasi.kontrak.rincian_list_tld.filter(tld => tld.pengguna_map);
        tldKontrol = informasi.kontrak.rincian_list_tld.filter(tld => !tld.pengguna_map);
        kontrakPeriode = informasi.kontrak.periode;
    }else{
        tldPengguna = informasi.rincian_list_tld.filter(tld => tld.pengguna_map);
        tldKontrol = informasi.rincian_list_tld.filter(tld => !tld.pengguna_map);
        kontrakPeriode = informasi.periode;
    }

    // list document TLD
    // Mengecek apakah sudah last periode atau belum
    const isLastPeriode = _cekLastPeriode(kontrakPeriode, (periodeNow ? periodeNow : informasi.periode));

    if(!isLastPeriode){
        let checkedTld = status_tld ? 'disabled' : 'checked';
        let htmlKontrol = ``;
        for (const list of tldKontrol) {
            tmpArrTld.push({
                id: list.kontrak_tld_hash,
                tld: list.tld?.tld_hash
            })
            htmlKontrol += `
                <div class="w-50 pe-1 d-flex flex-column">
                    <span>&nbsp;</span>
                    <div class="input-group mt-auto mb-3">
                        <input type="text" class="form-control rounded-start form-sm" name="kodeTldKontrol" value="${list.tld?.no_seri_tld ?? ''}" data-id="${list.kontrak_tld_hash}" id="tldNoSeri_${list.kontrak_tld_hash}" placeholder="Pilih No Seri" readonly>
                        ${!list.tld ? `<button class="btn btn-outline-secondary btn-sm" type="button" data-id="${list.kontrak_tld_hash}" onclick="openInventory(this, 'kontrol')"><i class="bi bi-arrow-repeat"></i> Ganti</button>` : ``}
                    </div>
                </div>
            `;
        }
        // <select class="form-select kodeTldKontrol" name="kodeTldKontrol" data-status="${list.permohonan_tld_hash ? 'permohonan' : 'kontrak'}" data-id="${list.permohonan_tld_hash ?? list.kontrak_tld_hash ?? ''}" ${htmlDisabled}>
        //     <option value="${list.tld?.tld_hash ?? ''}" selected>${list.tld?.kode_lencana ?? ''}</option>
        // </select>

        // Menambil tld Pengguna dari kontrak
        let htmlPengguna = ``;
        for (const list of tldPengguna){
            tmpArrTld.push({
                id: list.kontrak_tld_hash,
                tld: list.tld?.tld_hash
            })
            htmlPengguna += `
                <div class="w-50 pe-1 d-flex flex-column">
                    <span>${list.pengguna_map.pengguna.name}</span>
                    <div class="input-group mt-auto mb-3">
                        <input type="text" class="form-control rounded-start form-sm" name="kodeTldPengguna" value="${list.tld?.no_seri_tld ?? ''}" data-id="${list.kontrak_tld_hash}" id="tldNoSeri_${list.kontrak_tld_hash}" placeholder="Pilih No Seri" readonly>
                        ${!list.tld ? `<button class="btn btn-outline-secondary btn-sm" type="button" data-id="${list.kontrak_tld_hash}" onclick="openInventory(this, 'pengguna')"><i class="bi bi-arrow-repeat"></i> Ganti</button>` : ``}
                    </div>
                </div>
            `;
        }
        // <select class="form-select kodeTldPengguna" name="kodeTldPengguna" data-status="${list.permohonan_tld_hash ? 'permohonan' : 'kontrak'}" data-id="${list.permohonan_tld_hash ?? list.kontrak_tld_hash ?? ''}" ${htmlDisabled}>
        //     <option value="${list.tld?.tld_hash ?? ''}" selected>${list.tld?.kode_lencana ?? ''}</option>
        // </select>
        htmlTld = `
            <div class="border shadow-sm py-2 rounded mb-2">
                <div
                    class="d-flex justify-content-between align-items-center px-2">
                    <div>
                        <input class="form-check-input me-2" type="checkbox"
                            data-jenis="tld" data-id="${informasi.permohonan_hash ?? ''}"
                            id="selectDocumentTld" name="selectDocument" onclick="updateSelectDocument()" ${checkedTld}>
                        <span class="fw-semibold fs-6">TLD</span>
                        <small class="text-body-tertiary"> - ${informasi.jumlah_pengguna} Pengguna + ${informasi.jumlah_kontrol} Kontrol</small>
                        <small>${statusFormat('pengiriman', status_tld?.status)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-3 text-secondary">
                    </div>
                </div>
                <div id="listTld" class="row">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="px-4">TLD Pengguna</label>
                            <div class="px-4 pt-2 flex-wrap d-flex row-gap-2">
                                ${htmlPengguna}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="px-4">TLD kontrol</label>
                            <div class="px-4 pt-2 flex-wrap d-flex row-gap-2">
                                ${htmlKontrol}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#list-document').append(htmlTld);

        // _tldKontrol();
        // _tldPengguna();
    }

    // list document invoice
    let htmlInvoice = '';
    let urlLaporanInvoice = informasi.invoice?.status == 5 ? `<a href="${base_url}/laporan/invoice/${informasi.invoice?.keuangan_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Invoice</a>` : '<i class="bi bi-printer-fill"></i> Cetak Invoice';
    // let checkedInvoice = informasi.invoice?.status == 5 ? (informasi.invoice?.pengiriman ? 'disabled' : 'checked') : 'disabled';
    let checkedInvoice = informasi.invoice?.pengiriman ? 'disabled' : 'checked';
    informasi.invoice ? htmlInvoice = `
        <div
            class="border shadow-sm py-2 d-flex justify-content-between align-items-center px-2 rounded mb-2">
            <div>
                <input class="form-check-input me-2" type="checkbox"
                    data-jenis="invoice" data-id="${informasi.invoice.keuangan_hash}"
                    id="selectDocumentInvoice" name="selectDocument" onclick="updateSelectDocument()" ${checkedInvoice}>
                <span class="fw-semibold fs-6">Invoice + MoU</span>
                <small class="text-body-tertiary"> - ${informasi.invoice.no_invoice}</small>
                <small>${statusFormat('pengiriman', informasi.invoice.pengiriman?.status)}</small>
            </div>
            <div class="d-flex align-items-center gap-3 text-secondary">
                <small><i class="bi bi-calendar-fill"></i> ${dateFormat(informasi.invoice.created_at, 4)}</small>
                <small>${statusFormat('invoice', informasi.invoice.status)}</small>
                <small class="bg-body-tertiary rounded-pill ${informasi.invoice.status == 5 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlLaporanInvoice}</small>
            </div>
        </div>
    ` : false;
    $('#list-document').append(htmlInvoice);

    // List Document LHU
    let htmlLhu = '';
    let checkedLhu = 'disabled';
    let urlDocLhu = '<i class="bi bi-printer-fill"></i> Cetak LHU';

    if(informasi.lhu?.status == 3){
        checkedLhu = 'checked';
        urlDocLhu = `<a href="${base_url}/storage/${informasi.lhu.media.file_path}/${informasi.lhu.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak LHU</a>`;
    }

    informasi.lhu ? htmlLhu = `
        <div class="border shadow-sm py-2 rounded mb-2">
            <div class="d-flex justify-content-between align-items-center px-2">
                <div>
                    <input class="form-check-input me-2" type="checkbox"
                        data-jenis="lhu" data-id="${informasi.lhu.penyelia_hash}"
                        id="selectDocumentLHU" name="selectDocument" onclick="updateSelectDocument()" ${checkedLhu}>
                    <span class="fw-semibold fs-6">LHU</span>
                    <small class="text-body-tertiary"> - Periode ${informasi.lhu.periode}${informasi.lhu.periode == 1 ? '/Zero Cek' : ''} (${informasi.kontrak_periode?.start_date ? dateFormat(informasi.kontrak_periode.start_date, 4) : '-'} - ${informasi.kontrak_periode?.end_date ? dateFormat(informasi.kontrak_periode.end_date, 4) : '-'})</small>
                    <small>${statusFormat('pengiriman', informasi.lhu.pengiriman?.status)}</small>
                </div>
                <div class="d-flex align-items-center gap-3 text-secondary">
                    <small><i class="bi bi-calendar-fill"></i> ${dateFormat(informasi.lhu.created_at, 4)}</small>
                    <small>${statusFormat('penyelia', informasi.lhu.status)}</small>
                    <!-- <small class="bg-body-tertiary rounded-pill ${informasi.lhu.status == 3 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlDocLhu}</small> -->
                </div>
            </div>
        </div>
    ` : false;
    $('#list-document').append(htmlLhu);

    // List document custom (akan mengikat ke id pengiriman yang ada di permohonannya)
    let htmlCustom = '';
    let checkedCustom = informasi.pengiriman ? 'disabled' : 'checked';
    if(informasi.file_lhu){
        arrDocCustom.push({jenis: "lhu zero cek", media: informasi.file_lhu});
    }

    for (const custom of arrDocCustom) {
        let urlDocCustom = custom.media ? `<a href="${base_url}/storage/${custom.media.file_path}/${custom.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Document</a>` : false;
        htmlCustom += `
            <div class="border shadow-sm py-2 rounded mb-2">
                <div
                    class="d-flex justify-content-between align-items-center px-2">
                    <div>
                        <input class="form-check-input me-2" type="checkbox"
                            data-jenis="${custom.jenis}" data-id="${informasi.permohonan_hash}"
                            id="selectDocumentCustom" name="selectDocument" ${checkedCustom} disabled>
                        <span class="fw-semibold fs-6">${custom.jenis}</span>
                        <small class="text-body-tertiary"></small>
                        <small>${statusFormat('pengiriman', informasi.pengiriman?.status)}</small>
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
        let listTld = [];

        switch (jenis) {
            case 'lhu':
                periode = informasi.lhu.periode;
                break;
            case 'tld':
                if(doc.checked){
                    $('#btnCetakSurat').attr('href', `${base_url}/laporan/surpeng/${informasi.kontrak_hash}/${periodeNow ? periodeNow : informasi.periode}`);
                    $('#btnCetakSurat').addClass('d-block').removeClass('d-none');
                }else{
                    $('#btnCetakSurat').attr('href', ``);
                    $('#btnCetakSurat').addClass('d-none').removeClass('d-block');
                }

                periode = informasi.periode;

                if(periodeNow){
                    periode = periodeNow;
                }

                if(doc.checked){
                    $('#listTld').addClass('d-flex').removeClass('d-none');
                }else{
                    $('#listTld').addClass('d-none').removeClass('d-flex');
                }

                listTld = tmpArrTld;
                break;
            default:
                periode = informasi.periode;
                break;
        }

        let getIndex = arrSelectDocument.findIndex(d => d.jenis == jenis);
        let tmp = {jenis: jenis, periode: periode, id: id, listTld: listTld};
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
            let dAlamat = informasi.pelanggan.perusahaan.alamat[alamat];
            const params = new FormData();
            params.append('idPengiriman', $('#no_pengiriman').val());
            params.append('idPermohonan', informasi.permohonan_hash);
            params.append('alamat', dAlamat.alamat_hash);
            params.append('tujuan', informasi.pelanggan.id);
            params.append('status', 3);
            params.append('detail', JSON.stringify(arrSelectDocument));
            periodeNow ? params.append('periode', periodeNow) : (informasi.kontrak_periode ? params.append('periode', informasi.kontrak_periode.periode) : false);
            informasi.kontrak_hash ? params.append('idKontrak', informasi.kontrak_hash) : false;

            spinner('show', $(obj));
            ajaxPost('api/v1/pengiriman/buatPengiriman', params, result => {
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
        // allowClear: true,
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

function _cekLastPeriode(periode_kontrak, periode_now){
    // Ambil periode terakhir
    const lastPeriode = periode_kontrak[periode_kontrak.length-1];
    const isLast = periode_now == lastPeriode?.periode ? true : false;
    return isLast;
}
