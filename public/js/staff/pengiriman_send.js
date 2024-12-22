const arrImgBukti = [];
const arrSelectDocument = [];
const arrPeriode = permohonan.periode_pemakaian;
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

    $('#btnTambahBukti').on('click', obj => {

        let imgtmp = $('#uploadBuktiPengiriman')[0].files[0];

        if (imgtmp && arrImgBukti.length < 5) {
            spinner('show', $(obj.target));

            arrImgBukti.push(imgtmp);
            loadPreviewBukti();
            spinner('hide', $(obj.target));
            $('#uploadBuktiPengiriman').val('');
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
    // list document TLD
    let checkedTld = permohonan.pengiriman?.status == 2 ? 'disabled' : 'checked';
    const jumlahTLD = permohonan.jumlah_pengguna + permohonan.jumlah_kontrol;
    let tldDetail = ``;
    for (let i = 1; i <= jumlahTLD; i++) {
        tldDetail += `<div><input type="text" class="form-control form-control-sm" name="listTld[]" placeholder="TLD ${i}"></div>`;
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
                <small class="text-body-tertiary"> - ${jumlahTLD} PCS</small>
                <small>${statusFormat('pengiriman', permohonan.pengiriman?.status)}</small>
            </div>
            <div class="d-flex align-items-center gap-3 text-secondary">
            </div>
        </div>
        <div class="p-3 flex-wrap d-flex gap-2" id="listTld">
            ${tldDetail}
        </div>
    </div>
    `;
    $('#list-document').append(htmlTld);

    // list document invoice
    let htmlInvoice = '';
    let urlLaporanInvoice = permohonan.invoice?.status == 5 ? `<a href="${base_url}/laporan/invoice/${permohonan.invoice.keuangan_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Invoice</a>` : '<i class="bi bi-printer-fill"></i> Cetak Invoice';
    let checkedInvoice = permohonan.invoice.status == 5 ? 'checked' : 'disabled';
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
    let htmlLhuPeriode = ``;
    let checkedLhu = 'disabled';
    let urlDocLhu = '<i class="bi bi-printer-fill"></i> Cetak LHU';

    if(permohonan.lhu?.status == 3){
        checkedLhu = 'checked';
        urlDocLhu = `<a href="${base_url}/storage/${permohonan.lhu.media.file_path}/${permohonan.lhu.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak LHU</a>`;
    }

    for (const [i, periode] of arrPeriode.entries()) {
        let checkedPeriode = '';
        let statusPeriode = true;
        let statusKirim = '';
        if(i < mPeriode.getPeriodeNow()) {
            statusPeriode = false;
            statusKirim = `</span> <span class="text-success"><i class="bi bi-check-circle-fill"></i> Terkirim</span>`;
        } else if(i == mPeriode.getPeriodeNow())[
            checkedPeriode = 'checked'
        ]

        htmlLhuPeriode += `
            <li class="list-group-item">
                <input class="form-check-input me-1" type="radio" name="radioPeriodeLhu" onchange="updateSelectDocument()"
                    value="${i + 1}" id="radioPeriodeLhu-${i}" ${checkedPeriode} ${!statusPeriode ? 'disabled' : ''}>
                <label class="form-check-label cursoron ${!statusPeriode ? 'text-decoration-line-through' : ''}" for="radioPeriodeLhu-${i}">
                    Periode ${i + 1} <span class="text-body-tertiary">(${dateFormat(periode.start_date, 4)} - ${dateFormat(periode.end_date, 4)}) ${statusKirim}
                </label>
            </li>
        `;
    }

    permohonan.lhu ? htmlLhu = `
        <div class="border shadow-sm py-2 rounded mb-2">
            <div class="d-flex justify-content-between align-items-center px-2">
                <div>
                    <input class="form-check-input me-2" type="checkbox"
                        data-jenis="lhu" data-id="${permohonan.lhu.penyelia_hash}"
                        id="selectDocumentLHU" name="selectDocument" onclick="updateSelectDocument()" ${checkedLhu}>
                    <span class="fw-semibold fs-6">LHU</span>
                    <small class="text-body-tertiary"> - Periode ${permohonan.lhu.periode}</small>
                    <small>${statusFormat('pengiriman', permohonan.lhu.pengiriman?.status)}</small>
                </div>
                <div class="d-flex align-items-center gap-3 text-secondary">
                    <small><i class="bi bi-calendar-fill"></i> ${dateFormat(permohonan.lhu.created_at, 4)}</small>
                    <small>${statusFormat('penyelia', permohonan.lhu.status)}</small>
                    <small class="bg-body-tertiary rounded-pill ${permohonan.lhu.status == 3 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlDocLhu}</small>
                </div>
            </div>
            <div class="p-3 justify-content-between d-flex" id="listLhuPeriode">
                <ul class="list-group">
                    ${htmlLhuPeriode}
                </ul>
            </div>
        </div>
    ` : false;
    $('#list-document').append(htmlLhu);

    updateSelectDocument();
}

function loadPreviewBukti() {
    $('#list-preview-bukti').html('');
    for (const [i, img] of arrImgBukti.entries()) {
        const reader = new FileReader();
        reader.onload = function (e) {
            let divMain = document.createElement('div');
            divMain.className = '';
            divMain.style.width = '100px';
            divMain.style.height = '100px';

            const preview = document.createElement('img');
            preview.src = e.target.result;
            preview.className = 'img-thumbnail';
            preview.style.width = '100px';
            preview.style.height = '100px';
            preview.style.cursor = 'pointer';
            preview.onclick = () => {
                $('#modal-preview-image').attr('src', e.target.result);

                $('#modal-preview').modal('show');
            }

            const btnRemove = document.createElement('button');
            btnRemove.className = 'btn btn-danger btn-sm position-absolute mt-2 ms-2';
            btnRemove.innerHTML = '<i class="bi bi-trash"></i>';
            btnRemove.onclick = () => {
                arrImgBukti.splice(i, 1);
                loadPreviewBukti();
            }

            divMain.append(btnRemove);
            divMain.append(preview);

            document.getElementById('list-preview-bukti').appendChild(divMain);
        };
        reader.readAsDataURL(img);
    }

}

function updateSelectDocument(){
    let checkedDokumen = $('input[name="selectDocument"]');
    
    for (const doc of checkedDokumen) {
        let jenis = doc.dataset.jenis;
        let id = doc.dataset.id;
        let periode = false;
        let listTld = false;

        switch (jenis) {
            case 'lhu':
                if(doc.checked){
                    $('#btnCetakSurat').show();
                    $('#listLhuPeriode').addClass('d-flex').removeClass('d-none');
                }else{
                    $('#btnCetakSurat').hide();
                    $('#listLhuPeriode').addClass('d-none').removeClass('d-flex');
                }
    
                periode = $('input[name="radioPeriodeLhu"]:checked').val();
                break;
            case 'tld':
                if(doc.checked){
                    $('#listTld').addClass('d-flex').removeClass('d-none');
                }else{
                    $('#listTld').addClass('d-none').removeClass('d-flex');
                }
                listTld = $('input[name="listTld[]"]').map(function(index, element) {
                    return $(element).val() || `TLD ${index + 1}`;
                }).get();
                break;
            default:
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
    const noResi = $('#txt_resi').val();

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
            params.append('noResi', noResi);
            params.append('noKontrak', permohonan.kontrak.no_kontrak);
            params.append('alamat', dAlamat.alamat_hash);
            params.append('tujuan', permohonan.pelanggan.id);
            params.append('status', 3);
            params.append('detail', JSON.stringify(arrSelectDocument));
            arrImgBukti.forEach((file, index) => {
                params.append('buktiPengiriman[]', file);
            });

            spinner('show', $(obj));
            ajaxPost('api/v1/pengiriman/action', params, result => {
                Swal.fire({
                    icon: 'success',
                    text: `Pengiriman di jadwalkan`,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = `${base_url}/staff/pengiriman/permohonan`;
                });
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
                spinner('hide', $(obj));
            });
        }
    });
}