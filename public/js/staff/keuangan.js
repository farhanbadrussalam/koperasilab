let arrDiskon = [];
let dataKeuangan = false;
let ppn = false;
let pph = false;
let jumTotal = 0;

$(function () {
    switchLoadTab(1);
    $('#diskonModal').on('hide.bs.modal', () => {
        $('#invoiceModal').modal('show');
    });
});

function switchLoadTab(menu){
    switch (menu) {
        case 1:
            menu = 'pengajuan';
            break;

        case 2:
            menu = 'pembayaran';
            break;

        case 3:
            menu = 'verifikasi';
            break;

        case 4:
            menu = 'diterima';
            break;

        default:
            menu = 'ditolak';
            break;
    }
    loadData(1, menu);
}

function loadData(page = 1, menu) {
    let params = {
        limit: 10,
        page: page,
        menu: menu
    };
    
    $(`#list-placeholder-${menu}`).show();
    $(`#list-container-${menu}`).hide();
    ajaxGet(`api/v1/keuangan/listKeuangan`, params, result => {
        let html = '';
        for (const [i, keuangan] of result.data.entries()) {
            const permohonan = keuangan.permohonan;
            permohonan.idkeuangan = keuangan.keuangan_hash;
            let periode = JSON.parse(permohonan.periode_pemakaian);
            let btnAction = '';
            switch (menu) {
                case 'pengajuan':
                    btnAction = `<button class="btn btn-outline-primary btn-sm" title="Buat Invoice" onclick="openInvoiceModal(this, 'create')"><i class="bi bi-plus"></i> Buat invoice</button>`;
                    break;
                case 'verifikasi':
                    btnAction = `<button class="btn btn-outline-primary" title="Verifikasi" onclick="openInvoiceModal(this, 'verify')"><i class="bi bi-check2-circle"></i> Verif Invoice</button>`;
                    break;
                default:
                    break;
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${permohonan.jenis_tld.name}</div>
                                <div>Periode : ${periode.length} Bulan</div>
                                <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 my-3">${permohonan.jenis_layanan_parent.name}-${permohonan.jenis_layanan.name}</div>
                        <div class="col-6 col-md-3 my-3 text-end text-md-start">
                            <div>${permohonan.tipe_kontrak}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.no_kontrak}</small>
                        </div>
                        <div class="col-6 col-md-2">${statusFormat('keuangan', keuangan.status)}</div>
                        <div class="col-6 col-md-2 text-center" data-keuangan='${JSON.stringify(keuangan)}' data-invoice='${keuangan.no_invoice}'>
                            ${btnAction}
                        </div>
                    </div>
                </div>
            `;
        }

        if(result.data.length == 0){
            html = `
                <div class="d-flex flex-column align-items-center py-3">
                    <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                    <span class="fw-bold mt-3 text-muted">No Data Available</span>
                </div>
            `;
        }

        $(`#list-container-${menu}`).html(html);

        $(`#list-pagination-${menu}`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-${menu}`).hide();
        $(`#list-container-${menu}`).show();
    }, error => {
        const result = error.responseJSON;
        if(result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }
    })
}

function tambahDiskon() {
    const namaDiskon = $('#inputNamaDiskon').val();
    const diskon = $('#inputJumDiskon').val();

    if(namaDiskon != '' && diskon != ''){
        arrDiskon.push({
            name: namaDiskon,
            diskon: diskon
        });
    
        updateInvoiceDescription();
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

function removeDiskon(index) {
    arrDiskon.splice(index, 1);
    updateInvoiceDescription();
}

let invoiceMode = 'create'; // 'create' or 'verify'

function openInvoiceModal(obj, mode) {
    const keuangan = $(obj).parent().data("keuangan");
    const noInvoice = $(obj).parent().data("invoice");
    
    invoiceMode = mode;
    dataKeuangan = keuangan;
    let permohonan = keuangan.permohonan;
    
    // Populate invoice details
    let detailsHTML = `
        <div class="col-md-6 col-12">
            <label class="fw-bolder">No Invoice</label>
            <div id="txtNoInvoice">${noInvoice || '-'}</div>
        </div>
        <div class="col-md-6 col-12">
            <label class="fw-bolder">No Kontrak</label>
            <div id="txtNoKontrakInvoice">${permohonan.no_kontrak || '-'}</div>
        </div>
        <div class="col-md-6 col-12">
            <label class="fw-bolder">Jenis</label>
            <div id="txtJenisInvoice">${permohonan.jenis_layanan?.name || '-'}</div>
        </div>
        <div class="col-md-6 col-12">
            <label class="fw-bolder">Pengguna</label>
            <div id="txtPenggunaInvoice">${permohonan.jumlah_pengguna || '-'}</div>
        </div>
        <div class="col-md-6 col-12">
            <label class="fw-bolder">Tipe Kontrak</label>
            <div id="txtTipeKontrakInvoice">${permohonan.tipe_kontrak || '-'}</div>
        </div>
        <div class="col-md-6 col-12">
            <label class="fw-bolder">Pelanggan</label>
            <div id="txtPelangganInvoice">${permohonan.pelanggan?.name || '-'}</div>
        </div>
        <div class="col-md-6 col-12">
            <label class="fw-bolder">Jenis TLD</label>
            <div id="txtJenisTldInvoice">${permohonan.jenis_tld?.name || '-'}</div>
        </div>
        <div class="col-md-6 col-12">
            <label class="fw-bolder">Instansi</label>
            <div id="txtInstansiInvoice">-</div>
        </div>
    `;
    $('#invoiceDetails').html(detailsHTML);

    // Set up actions based on mode
    let actionsHTML = '';
    if (mode === 'create') {
        actionsHTML = `
            <div class="col-md-6 col-12 row">
                <div class="col-6">
                    <label class="col-form-label" for="inputPpn">PPN %</label>
                    <div class="input-group">
                        <input type="text" name="inputPpn" id="inputPpn" class="form-control maskNumber" value="11" autocomplete="off">
                        <span class="input-group-text"><input class="form-check-input m-0" type="checkbox" id="checkPpn"></span>
                    </div>
                </div>
                <div class="col-6">
                    <label class="col-form-label" for="inputPph">PPH 23 %</label>
                    <div class="input-group">
                        <input type="text" name="inputPph" id="inputPph" class="form-control maskNumber" value="2" autocomplete="off">
                        <span class="input-group-text"><input class="form-check-input m-0" type="checkbox" id="checkPph"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12 d-flex align-items-end">
                <button class="btn btn-outline-secondary me-3" data-bs-toggle="modal" data-bs-target="#diskonModal"><i class="bi bi-plus"></i> Tambah Diskon</button>
                <button class="btn btn-outline-secondary"><i class="bi bi-plus"></i> Tambah Faktur</button>
            </div>
        `;
        $('#paymentProofSection').hide();
    } else if (mode === 'verify') {
        // actionsHTML = `
        //     <div class="col-12">
        //         <button class="btn btn-outline-secondary" onclick="showVerificationHistory()"><i class="bi bi-clock-history"></i> Riwayat Verifikasi</button>
        //     </div>
        // `;
        actionsHTML = '';
        showPaymentProof();
    }
    $('#invoiceActions').html(actionsHTML);

    $('#checkPpn').on('change', (obj) => {
        ppn = $(obj.target).is(":checked");
        updateInvoiceDescription();
    });
    $('#inputPpn').on('input', updateInvoiceDescription);

    $('#checkPph').on('change', (obj) => {
        pph = $(obj.target).is(":checked");
        updateInvoiceDescription();
    });
    $('#inputPph').on('input', updateInvoiceDescription);

    // Set up footer buttons
    let footerHTML = '';
    if (mode === 'create') {
        footerHTML = '<button type="button" class="btn btn-primary" onclick="simpanInvoice(this)">Simpan</button>';
    } else if (mode === 'verify') {
        footerHTML = `
        <button type="button" class="btn btn-danger" onclick="verifikasiInvoice(this, 'reject')">Tolak</button>
            <button type="button" class="btn btn-success" onclick="verifikasiInvoice(this, 'approve')">Setujui</button>
        `;
    }
    $('#modalFooter').html(footerHTML);

    updateInvoiceDescription(mode);

    $('#invoiceModal').modal('show');
}

function showPaymentProof() {
    // Assuming the payment proof URL is stored in dataKeuangan.bukti_pembayaran
    if (dataKeuangan.media_bayar) {
        let media = dataKeuangan.media_bayar;
        let mediaPph = dataKeuangan.media_bayar_pph;
        $('#paymentProofImage').html(`
            <li class="w-50">
                <img src="${base_url}/storage/${ media.file_path}/${media.file_hash}" alt="Bukti Pembayaran" class="img-fluid rounded img-thumbnail">
            </li>
        `);
        $('#paymentPphProofImage').html(`
            <li class="w-50">
                <img src="${base_url}/storage/${ mediaPph.file_path}/${mediaPph.file_hash}" alt="Bukti PPH" class="img-fluid rounded img-thumbnail">
            </li>
        `);
        $('#paymentProofSection').show();
    } else {
        $('#paymentProofSection').hide();
    }
}

function updateInvoiceDescription(mode) {
    const permohonan = dataKeuangan.permohonan;
    
    let hargaLayanan = permohonan.harga_layanan;
    let qty = permohonan.jumlah_kontrol+permohonan.jumlah_pengguna;
    let jumLayanan = permohonan.total_harga;
    let periode = JSON.parse(permohonan.periode_pemakaian);
    let jumPph = 0;
    let jumPpn = 0;
    let jumDiskon = 0;
    let descInvoice = `
        <tr>
            <th class="text-start">${permohonan.layanan_jasa.nama_layanan}</th>
            <td>${formatRupiah(hargaLayanan)}</td>
            <td>${qty}</td>
            <td>${periode.length}</td>
            <td>${formatRupiah(jumLayanan)}</td>
        </tr>
    `;

    if(dataKeuangan.diskon){
        for (const diskon of dataKeuangan.diskon) {
            arrDiskon.push({
                name: diskon.name,
                diskon: diskon.diskon
            });
        }
    }
    
    for (const [i,diskon] of arrDiskon.entries()) {
        countDiskon = jumLayanan * (diskon.diskon/100);
        jumDiskon += countDiskon;
        descInvoice += `
            <tr>
                <th class="text-start">${diskon.name}&nbsp${diskon.diskon}% &nbsp;${mode != 'verify' ? `<i class="bi bi-x-circle-fill text-danger" type="button" onclick="removeDiskon(${i})" title="Hapus diskon"></i>` : ''}</th>
                <td></td>
                <th colspan="2"></th>
                <td>- ${formatRupiah(countDiskon)}</td>
            </tr>
        `;
    }

    let jumAfterDiskon = jumLayanan - jumDiskon;

    if(pph || dataKeuangan.pph) {
        let valPph = $('#inputPph').val() || dataKeuangan.pph;
        valPph = parseInt(valPph);
        jumPph = jumAfterDiskon * (valPph/100);
        descInvoice += `
            <tr>
                <th class="text-start">PPH 23 (${valPph}%)</th>
                <td></td>
                <td></td>
                <td></td>
                <td>- ${formatRupiah(jumPph)}</td>
            </tr>
        `;
    }

    let jumAfterPph = jumAfterDiskon - jumPph;

    if(ppn || dataKeuangan.ppn){
        let valPpn = $('#inputPpn').val() || dataKeuangan.ppn;
        valPpn = parseInt(valPpn);
        jumPpn = jumAfterPph * (valPpn/100);
        descInvoice += `
            <tr>
                <th class="text-start">PPN ${valPpn}%</th>
                <td></td>
                <td></td>
                <td></td>
                <td>${formatRupiah(jumPpn)}</td>
            </tr>
        `;
    }

    // total harga
    jumTotal = jumAfterPph + jumPpn;
    descInvoice += `
        <tr>
            <td></td>
            <td></td>
            <th colspan="2">Total Jumlah</th>
            <td>${formatRupiah(jumTotal)}</td>
        </tr>
    `;
    $('#deskripsiInvoice').html(descInvoice);
}

function simpanInvoice(obj) {
    const formData = new FormData();
    formData.append('_token', csrf);
    formData.append('idPermohonan', dataKeuangan.permohonan_hash);
    formData.append('idKeuangan', dataKeuangan.keuangan_hash);
    formData.append('diskon', JSON.stringify(arrDiskon));
    formData.append('totalHarga', jumTotal);
    formData.append('status', 2);
    ppn && formData.append('ppn', $('#inputPpn').val());
    pph && formData.append('pph', $('#inputPph').val());

    Swal.fire({
        text: 'Apa anda yakin ingin membuat invoice ?',
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
            ajaxPost(`api/v1/keuangan/keuanganAction`, formData, result => {
                if(result.meta.code == 200){
                    Swal.fire({
                        icon: 'success',
                        text: 'Invoice berhasil dibuat.',
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        switchLoadTab(1);
                        closeInvoiceModal();
                        spinner('hide', $(obj));
                    });
                }
            }, error => {
                Swal.fire({
                    icon: "error",
                    text: 'Server error',
                });
                console.error(error.responseJSON.data.msg);
                spinner('hide', obj);
            })
        }
    })
}

function verifikasiInvoice(obj, action) {
    // New function for invoice verification
    Swal.fire({
        text: `Apa anda yakin ingin ${action === 'approve' ? 'menyetujui' : 'menolak'} invoice ini?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak',
        customClass: {
            confirmButton: 'btn btn-success mx-1',
            cancelButton: 'btn btn-danger mx-1'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            spinner('show', $(obj));
            
            const formData = new FormData();
            formData.append('_token', csrf);
            formData.append('idKeuangan', dataKeuangan.keuangan_hash);
            formData.append('status', action === 'approve' ? 5 : 90);

            ajaxPost('api/v1/keuangan/keuanganAction', formData, result => {
                if (result.meta.code == 200) {
                    Swal.fire({
                        icon: 'success',
                        text: `Invoice berhasil ${action === 'approve' ? 'disetujui' : 'ditolak'}.`,
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        switchLoadTab(3);
                        closeInvoiceModal();
                        spinner('hide', $(obj));
                    });
                }
            }, error => {
                Swal.fire({
                    icon: "error",
                    text: 'Server error',
                });
                console.error(error.responseJSON.data.msg);
                spinner('hide', obj);
            });
        }
    });
}

function closeInvoiceModal() {
    arrDiskon = [];
    ppn = false;
    pph = false;
    jumTotal = 0;
    dataKeuangan = null;
    $('#checkPpn').prop('checked', false);
    $('#invoiceModal').modal('hide');
    $('#checkPpn').off('change');
    $('#inputPpn').off('input');
    $('#checkPph').off('change');
    $('#inputPph').off('input');
}

// Add other necessary functions here