const arrSelectDocument = [];
const arrDocCustom = [];
let inventoryTld = false;
let mPeriode = false;
const tmpArrTld = [];
const periode_aktif = informasi.periode[0] ? informasi.periode[0] : null;
const data_permohonan = periode_aktif?.permohonan;

$(function () {
    inventoryTld = new Inventory_tld({
        preview: true,
        no_kontrak: informasi.no_kontrak
    });

    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;

        $(`#tldNoSeri_${$.escapeSelector(detail.selected)}`).val(detail.data_tld.no_seri_tld);
        $(`#tldNoSeri_${$.escapeSelector(detail.selected)}_view`).html(detail.data_tld.no_seri_tld);

        // reset tmpArrTld
        let index = tmpArrTld.findIndex(d => d.id == detail.selected);

        if(index > -1){
            tmpArrTld[index].tld = detail.data_tld.tld_hash;
        }
    })
    load_form();

    $('#select_alamat').on('change', obj => {
        if (informasi) {
            const perusahaan = data_permohonan.pelanggan.perusahaan;

            if(perusahaan.alamat[obj.target.value].alamat){
                $('#alamatTujuan').val(perusahaan.alamat[obj.target.value].alamat + ", " + perusahaan.alamat[obj.target.value].kode_pos);
            } else {
                const alamatUtama = perusahaan.alamat.find(a => a.jenis == 'utama');
                $('#alamatTujuan').val(alamatUtama.alamat + ", " + alamatUtama.kode_pos);
            }
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
    for (const [i, value] of data_permohonan.pelanggan.perusahaan.alamat.entries()) {
        if(value.status) {
            htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
        }
    }
    $('#select_alamat').html(htmlAlamat);

    $('#list-document').empty();

    // if(informasi.kontrak){ // jika ada permohonannya
    //     tldPengguna = informasi.kontrak.kontrak_detail.filter(p => p.jenis == 'pengguna');
    //     tldKontrol = informasi.kontrak.kontrak_detail.filter(p => p.jenis == 'kontrol');
    //     kontrakPeriode = informasi.kontrak.periode;
    //     JL = jenislayanan(informasi.kontrak.jenis_layanan_parent, informasi.kontrak.jenis_layanan);
    //     periodeAwal = getPeriodeAwal(informasi.kontrak);
    //     periodeNow = informasi.periode;
    // }else{ // jika tidak ada permohonannya
    let tldPengguna = informasi.kontrak_detail.filter(p => p.jenis == 'pengguna');
    let tldKontrol = informasi.kontrak_detail.filter(p => p.jenis == 'kontrol');
    let kontrakPeriode = informasi.periode;
    let periodeAwal = getPeriodeAwal(informasi);
    let JL = jenislayanan(informasi.jenis_layanan_parent, informasi.jenis_layanan);
    // }

    // list document TLD
    // Mengecek apakah sudah last periode atau belum
    let htmlDisabled = false;
    let periodeTld = periode_aktif.periode === 0 ? 1 : periode_aktif.periode;

    if(!periodeAwal.includes(periodeTld)){
        const perAktif = kontrakPeriode.find(p => p.periode == periode_aktif.periode);
        let isPeriodOne = perAktif.count_tld == 1 || periode_aktif.periode == 0;
        let checkedTld = status_tld?.detail?.find(d => d.jenis == 'tld') ? 'disabled' : 'checked';
        let htmlKontrol = ``;
        for (const [i, list] of tldKontrol.entries()) {
            const tldActive = isPeriodOne ? list.tld_1 : list.tld_2;
            tmpArrTld.push({
                id: `${list.kontrak_detail_hash}`,
                tld: tldActive?.tld_hash
            });
            if(!list.tld){
                htmlDisabled = false;
            } else {
                if(data_permohonan.tipe_kontrak == 'kontrak lama' || (tmpArrEvaluasi.includes(JL) && data_permohonan.is_have_tld == 1)){
                    htmlDisabled = true;
                }
            }
            htmlKontrol += `
                <div class="bg-white border rounded px-2 py-1 d-flex align-items-center shadow-sm">
                    <small class="text-muted me-2">${data_permohonan.pelanggan.perusahaan.kode_perusahaan}-${i > 1 ? `C${i+1}` : 'C'}:</small>
                    <span class="fw-bold me-2" id="tldNoSeri_${list.kontrak_detail_hash}_view">${tldActive ? tldActive.no_seri_tld : 'Tidak ada'}</span>
                    ${!htmlDisabled ? `<button class="btn btn-sm btn-link p-0 text-info ms-auto" data-id="${list.kontrak_detail_hash}" onclick="openInventory(this, 'kontrol')"><i class="bi bi-arrow-repeat"></i></button>` : ``}
                    <input type="hidden" class="form-control rounded-start form-sm" name="kodeTldKontrol" value="${tldActive ? tldActive.no_seri_tld : ''}" data-id="${list.kontrak_detail_hash}" id="tldNoSeri_${list.kontrak_detail_hash}" placeholder="Pilih No Seri" readonly>
                </div>
            `;
        }

        // Mengambil tld Pengguna dari kontrak
        let htmlPengguna = ``;
        for (const list of tldPengguna){
            const tldActive = isPeriodOne ? list.tld_1 : list.tld_2;
            tmpArrTld.push({
                id: list.kontrak_detail_hash,
                tld: tldActive ? tldActive.tld_hash : null
            })
            if(!list.tld){
                htmlDisabled = false;
            } else {
                if(data_permohonan.tipe_kontrak == 'kontrak lama' || (tmpArrEvaluasi.includes(JL) && data_permohonan.is_have_tld == 1)){
                    htmlDisabled = true;
                }
            }
            htmlPengguna += `
                <div class="bg-white border rounded px-2 py-1 d-flex align-items-center shadow-sm">
                    <input type="hidden" class="form-control rounded-start form-sm" value="${tldActive ? tldActive.no_seri_tld : ''}" data-id="${list.kontrak_detail_hash}" id="tldNoSeri_${list.kontrak_detail_hash}" readonly>
                    <small class="text-muted me-2">${data_permohonan.pelanggan.perusahaan.kode_perusahaan}-${list.entitas.kode_lencana}:</small>
                    <span class="fw-bold me-2" id="tldNoSeri_${list.kontrak_detail_hash}_view">${tldActive ? tldActive.no_seri_tld : 'Tidak Ada'}</span>
                    ${!htmlDisabled ? `<button class="btn btn-sm btn-link p-0 text-info" data-id="${list.kontrak_detail_hash}" onclick="openInventory(this, 'pengguna')"><i class="bi bi-arrow-repeat"></i></button>` : ``}
                </div>
            `;
        }
        htmlTld = `
            <div class="card border border-primary-subtle rounded-3">
                <div class="card-body p-3 pb-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <div>
                                <input class="form-check-input" type="checkbox" id="selectDocumentTld"
                                    data-jenis="tld" name="selectDocument" data-id="${data_permohonan.permohonan_hash ?? ''}"
                                        onclick="updateSelectDocument()" ${checkedTld}>
                                <label class="form-check-label fw-bold" for="checkTLD">TLD Periode ${periode_aktif.status === 2 ? 'Pengembalian' : periodeTld}</label>
                                <span class="badge bg-light text-muted border ms-2">${data_permohonan.jumlah_pengguna} Pengguna + ${data_permohonan.jumlah_kontrol} Kontrol</span>
                            </div>
                            <div>
                                <small><i class="bi bi-calendar-fill"></i> ${dateFormat(data_permohonan.created_at, 4)}</small>
                                <small>${statusFormat('pengiriman', checkedTld == 'disabled' ? status_tld.status : false)}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-2 bg-light p-2 rounded-3 m-2" id="listTld">
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-2 text-uppercase fw-bold" style="font-size: 0.7rem;">TLD Pengguna</small>
                        <div class="d-flex flex-wrap gap-2">
                            ${htmlPengguna}
                        </div>
                    </div>
                    <div class="col-md-6 border-start ps-3">
                        <small class="text-muted d-block mb-2 text-uppercase fw-bold" style="font-size: 0.7rem;">TLD Kontrol</small>
                        <div class="d-flex flex-wrap gap-2">
                            ${htmlKontrol}
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#list-document').append(htmlTld);
    }

    // list document invoice
    let htmlInvoice = '';
    let urlLaporanInvoice = data_permohonan.invoice?.status == 5 ? `<a href="${base_url}/laporan/invoice/${data_permohonan.invoice?.keuangan_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i></a>` : '<i class="bi bi-printer-fill"></i>';
    // let checkedInvoice = informasi.invoice?.status == 5 ? (informasi.invoice?.pengiriman ? 'disabled' : 'checked') : 'disabled';
    let checkedInvoice = data_permohonan.invoice?.pengiriman ? 'disabled' : 'checked';
    data_permohonan.invoice ? htmlInvoice = `
        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border mb-2 shadow-xs">
            <div class="form-check">
                <div class="d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox"
                        data-jenis="invoice" data-id="${data_permohonan.invoice.keuangan_hash}"
                        id="selectDocumentInvoice" name="selectDocument" onclick="updateSelectDocument()" ${checkedInvoice}>
                    <label class="form-check-label" for="checkInv">
                        <span class="fw-bold">Invoice + MoU</span>
                        <small class="text-muted ms-2">#${data_permohonan.invoice.no_invoice}</small>
                    </label>
                </div>
                <div>
                    <small><i class="bi bi-calendar-fill"></i> ${dateFormat(data_permohonan.invoice.created_at, 4)}</small>
                    <small>${statusFormat('pengiriman', data_permohonan.invoice.pengiriman?.status)}</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                ${statusFormat('invoice', data_permohonan.invoice.status)}
                <small class="btn btn-sm btn-outline-primary border-0 bg-primary-subtle text-primary ${data_permohonan.invoice.status == 5 ? "cursoron" : "cursordisable"}">
                    ${urlLaporanInvoice}
                </small>
            </div>
        </div>
    ` : false;
    $('#list-document').append(htmlInvoice);

    // List Document LHU
    let htmlLhu = '';
    let checkedLhu = 'disabled';
    let urlDocLhu = '<i class="bi bi-printer-fill"></i> Cetak LHU';

    if(data_permohonan.lhu?.status == 3){
        checkedLhu = 'checked';
        urlDocLhu = `<a href="${base_url}/storage/${data_permohonan.lhu.media.file_path}/${data_permohonan.lhu.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak LHU</a>`;
    }

    if(data_permohonan.lhu?.pengiriman){
        checkedLhu = 'disabled';
    }

    let htmlRangeDate = `(${data_permohonan.kontrak_periode?.start_date ? dateFormat(data_permohonan.kontrak_periode.start_date, 4) : '-'} - ${data_permohonan.kontrak_periode?.end_date ? dateFormat(data_permohonan.kontrak_periode.end_date, 4) : '-'})`;

    let htmlPeriode = "";
    if(data_permohonan.lhu){
        if(data_permohonan.lhu.periode == 1 && data_permohonan.is_zerocek == 1 && data_permohonan.is_have_tld == 1) {
            htmlPeriode += ' + Zero Cek';
        }
    }

    data_permohonan.lhu ? htmlLhu = `
        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border mb-2">
            <div class="form-check">
                <div class="d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox"
                        data-jenis="lhu" data-id="${data_permohonan.lhu.penyelia_hash}"
                        id="selectDocumentLHU" name="selectDocument" onclick="updateSelectDocument()" ${checkedLhu}>
                    <label class="form-check-label fw-bold" for="selectDocumentLHU">LHU ${htmlPeriode}</label>

                    <small class="text-body-tertiary"> - ${!data_permohonan.lhu.periode ? 'Zero Cek' : `Periode ${data_permohonan.lhu.periode} ${htmlRangeDate}`} </small>
                </div>
                <div>
                    <small><i class="bi bi-calendar-fill"></i> ${dateFormat(data_permohonan.lhu.created_at, 4)}</small>
                    <small>${statusFormat('pengiriman', data_permohonan.lhu.pengiriman?.status)}</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <small>${statusFormat('penyelia', data_permohonan.lhu.status)}</small>
                <!-- <small class="bg-body-tertiary rounded-pill ${data_permohonan.lhu.status == 3 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlDocLhu}</small> -->
            </div>
        </div>
    ` : false;
    $('#list-document').append(htmlLhu);

    // List document custom (akan mengikat ke id pengiriman yang ada di permohonannya)
    let htmlCustom = '';
    let checkedCustom = data_permohonan.pengiriman ? 'disabled' : 'checked';
    if(data_permohonan.file_lhu){
        arrDocCustom.push({jenis: "lhu zero cek", media: data_permohonan.file_lhu});
    }

    for (const custom of arrDocCustom) {
        let urlDocCustom = custom.media ? `<a href="${base_url}/storage/${custom.media.file_path}/${custom.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Document</a>` : false;
        htmlCustom += `
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border mb-2">
                <div class="form-check">
                    <div class="d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox"
                            data-jenis="${custom.jenis}" data-id="${data_permohonan.permohonan_hash}"
                            id="selectDocumentCustom" name="selectDocument" ${checkedCustom} disabled>
                        <label class="form-check-label fw-bold" for="selectDocumentLHU">${custom.jenis}</label>
                    </div>
                    <div>
                        <small>${statusFormat('pengiriman', data_permohonan.pengiriman?.status)}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 text-secondary">
                    ${urlDocCustom ? '<small class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2">'+urlDocCustom+'</small>' : ''}
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
                periode = data_permohonan.lhu.periode;
                break;
            case 'tld':
                if(doc.checked){
                    $('#btnCetakSurat').attr('href', `${base_url}/laporan/surpeng/${informasi.kontrak_hash}/${periode_aktif ? periode_aktif.periode : (data_permohonan.periode ? data_permohonan.periode : 1)}`);
                    $('#btnCetakSurat').addClass('d-block').removeClass('d-none');
                }else{
                    $('#btnCetakSurat').attr('href', ``);
                    $('#btnCetakSurat').addClass('d-none').removeClass('d-block');
                }

                periode = periode_aktif.periode;

                // if(periodeNow){
                //     periode = periodeNow;
                // }

                if(doc.checked){
                    $('#listTld').addClass('d-flex').removeClass('d-none');
                }else{
                    $('#listTld').addClass('d-none').removeClass('d-flex');
                }

                listTld = tmpArrTld;
                break;
            default:
                periode = periode_aktif.periode;
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
            let dAlamat = data_permohonan.pelanggan.perusahaan.alamat[alamat];
            const params = new FormData();
            params.append('idPengiriman', $('#no_pengiriman').html());
            params.append('idPermohonan', data_permohonan.permohonan_hash);
            params.append('alamat', dAlamat.alamat_hash);
            params.append('tujuan', data_permohonan.pelanggan.id);
            params.append('status', 3);
            params.append('detail', JSON.stringify(arrSelectDocument));
            periode_aktif ? params.append('periode', periode_aktif.periode) : false
            // periodeNow ? params.append('periode', periodeNow) : ();
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
function _cekLastPeriode(periode_kontrak, periode_now){
    // Ambil periode terakhir
    if(periode_kontrak){
        const lastPeriode = periode_kontrak[periode_kontrak.length-1];
        const isLast = periode_now < lastPeriode?.periode ? false : true;
        return isLast;
    }
    return false;
}
