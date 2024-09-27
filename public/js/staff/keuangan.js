let arrDiskon = [];
let dataPengajuan = false;
let ppn = false;
let jumTotal = 0;

$(function () {
    // maskReload();
    switchLoadTab(1);
    $('#checkPpn').on('change', (obj) => {
        ppn = $(obj.target).is(':checked');
        descInvoice();
    });
    $('#inputPpn').on('input', descInvoice);
    $('#diskonModal').on('hide.bs.modal', () => {
        $('#createInvoiceModal').modal('show');
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
                    btnAction = `<button class="btn btn-outline-primary btn-sm" title="Buat Invoice" onclick="createInvoice(this)"><i class="bi bi-plus"></i> Buat invoice</button>`;
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
                        <div class="col-6 col-md-2">${statusFormat('keuangan', permohonan.status)}</div>
                        <div class="col-6 col-md-2 text-center" data-keuangan='${JSON.stringify(permohonan)}' data-invoice='${keuangan.no_invoice}'>
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
    
        descInvoice();
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
    descInvoice();
}

function createInvoice(obj){
    const pengajuan = $(obj).parent().data("keuangan");
    const noInvoice = $(obj).parent().data("invoice");

    dataPengajuan = pengajuan;
    $('#txtNoInvoice').html(noInvoice ? noInvoice : '-');
    $('#txtNoKontrakInvoice').html(pengajuan.no_kontrak ? pengajuan.no_kontrak : '-');
    $('#txtJenisInvoice').html(pengajuan.jenis_layanan?.name ? pengajuan.jenis_layanan.name : '-');
    $('#txtPenggunaInvoice').html(pengajuan.jumlah_pengguna ? pengajuan.jumlah_pengguna : '-');
    $('#txtTipeKontrakInvoice').html(pengajuan.tipe_kontrak ? pengajuan.tipe_kontrak : '-');
    $('#txtPelangganInvoice').html(pengajuan.pelanggan?.name ? pengajuan.pelanggan.name : '-');
    $('#txtJenisTldInvoice').html(pengajuan.jenis_tld?.name ? pengajuan.jenis_tld.name : '-');
    $('#txtInstansiInvoice').html('-');

    descInvoice();

    $('#createInvoiceModal').modal('show');
}

function descInvoice(){
    let hargaLayanan = dataPengajuan.harga_layanan;
    let qty = dataPengajuan.jumlah_kontrol+dataPengajuan.jumlah_pengguna;
    let jumLayanan = dataPengajuan.total_harga;
    let periode = JSON.parse(dataPengajuan.periode_pemakaian);
    let jumPpn = 0;
    let jumDiskon = 0;
    let descInvoice = `
        <tr>
            <th class="text-start">${dataPengajuan.layanan_jasa.nama_layanan}</th>
            <td>${formatRupiah(hargaLayanan)}</td>
            <td>${qty}</td>
            <td>${periode.length}</td>
            <td>${formatRupiah(jumLayanan)}</td>
        </tr>
    `;

    if(ppn){
        let valPpn = $('#inputPpn').val()
        jumPpn = jumLayanan * (valPpn/100);
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
    
    for (const [i,diskon] of arrDiskon.entries()) {
        countDiskon = jumLayanan * (diskon.diskon/100);
        jumDiskon += countDiskon;
        descInvoice += `
            <tr>
                <th class="text-start">${diskon.name}&nbsp${diskon.diskon}% <i class="bi bi-x-circle-fill text-danger" type="button" onclick="removeDiskon(${i})" title="Hapus diskon"></i></th>
                <td></td>
                <th colspan="2"></th>
                <td>- ${formatRupiah(countDiskon)}</td>
            </tr>
        `;
    }

    // total harga
    jumTotal = jumLayanan + jumPpn - jumDiskon;
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

function simpanInvoice(obj){
    const formData = new FormData();
    formData.append('idPermohonan', dataPengajuan.permohonan_hash);
    formData.append('idKeuangan', dataPengajuan.idkeuangan);
    formData.append('diskon', JSON.stringify(arrDiskon));
    formData.append('totalHarga', jumTotal);
    ppn && formData.append('ppn', $('#inputPpn').val());

    Swal.fire({
        text: 'Apa anda yakin ingin membuat invoice ?',
        icon: false,
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
                        closeInvoice();
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

function closeInvoice(){
    arrDiskon = [];
    ppn = false;
    jumTotal = 0;
    dataPengajuan = false;
    $('#checkPpn').prop('checked', false);
    $('#createInvoiceModal').modal('hide');
}