let signaturePad = false;
$(function () {
    loadData();
    signaturePad = signature(document.getElementById("content-ttd"), {
        text: 'Manager'
    });

    $('#modal-verif-invalid').on('hide.bs.modal', () => {
        $('#modal-verif-invoice').modal('show');
    });
});


function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        status: [90]
    };

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/manager/listManager`, params, result => {
        let html = '';
        for (const [i, keuangan] of result.data.entries()) {
            const permohonan = keuangan.permohonan;
            permohonan.idkeuangan = keuangan.keuangan_hash;
            let periode = JSON.parse(permohonan.periode_pemakaian);
            let btnAction = '';

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
                        <div class="col-6 col-md-2">${permohonan.pelanggan.name}</div>
                        <div class="col-6 col-md-2 text-center" data-keuangan='${JSON.stringify(keuangan)}'>
                            <button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="verifikasiInvoice(this)">verifikasi</button>
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

        $('#list-container').html(html);

        $('#list-pagination').html(createPaginationHTML(result.pagination));

        $('#list-placeholder').hide();
        $('#list-container').show();
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

$('#list-pagination').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    
    loadData(pageno);
});

function closeInvoice(){
    $('#modal-verif-invoice').modal('hide');
}

function verifikasiInvoice(obj){
    const keuangan = $(obj).parent().data("keuangan");
    $('#txtNoInvoice').html(keuangan.no_invoice ? keuangan.no_invoice : '-');
    $('#txtNoKontrakInvoice').html(keuangan.permohonan.no_kontrak ? keuangan.permohonan.no_kontrak : '-');
    $('#txtJenisInvoice').html(keuangan.permohonan.jenis_layanan?.name ? keuangan.permohonan.jenis_layanan.name : '-');
    $('#txtPenggunaInvoice').html(keuangan.permohonan.jumlah_pengguna ? keuangan.permohonan.jumlah_pengguna : '-');
    $('#txtTipeKontrakInvoice').html(keuangan.permohonan.tipe_kontrak ? keuangan.permohonan.tipe_kontrak : '-');
    $('#txtPelangganInvoice').html(keuangan.permohonan.pelanggan?.name ? keuangan.permohonan.pelanggan.name : '-');
    $('#txtJenisTldInvoice').html(keuangan.permohonan.jenis_tld?.name ? keuangan.permohonan.jenis_tld.name : '-');
    $('#txtInstansiInvoice').html('-');
    $('#idKeuangan').val(keuangan.keuangan_hash);

    descInvoice(keuangan);
    $('#modal-verif-invoice').modal('show');
}

function descInvoice(data){
    let hargaLayanan = data.permohonan.harga_layanan;
    let qty = data.permohonan.jumlah_kontrol+data.permohonan.jumlah_pengguna;
    let jumLayanan = data.permohonan.total_harga;
    let periode = JSON.parse(data.permohonan.periode_pemakaian);
    let jumPpn = 0;
    let jumPph = 0;
    let jumDiskon = 0;
    let descInvoice = `
        <tr>
            <th class="text-start">${data.permohonan.layanan_jasa.nama_layanan}</th>
            <td>${formatRupiah(hargaLayanan)}</td>
            <td>${qty}</td>
            <td>${periode.length}</td>
            <td>${formatRupiah(jumLayanan)}</td>
        </tr>
    `;
    
    for (const [i,diskon] of data.diskon.entries()) {
        countDiskon = jumLayanan * (diskon.diskon/100);
        jumDiskon += countDiskon;
        descInvoice += `
            <tr>
                <th class="text-start">${diskon.name}&nbsp${diskon.diskon}%</th>
                <td></td>
                <th colspan="2"></th>
                <td>- ${formatRupiah(countDiskon)}</td>
            </tr>
        `;
    }

    let jumAfterDiskon = jumLayanan - jumDiskon;

    if(data.pph){
        jumPph = jumAfterDiskon * (data.pph/100);
        descInvoice += `
            <tr>
                <th class="text-start">PPH 23 (${data.pph}%)</th>
                <td></td>
                <td></td>
                <td></td>
                <td>- ${formatRupiah(jumPph)}</td>
            </tr>
        `;
    }

    let jumAfterPph = jumAfterDiskon - jumPph;

    if(data.ppn){
        jumPpn = jumAfterPph * (data.ppn/100);
        descInvoice += `
            <tr>
                <th class="text-start">PPN ${data.ppn}%</th>
                <td></td>
                <td></td>
                <td></td>
                <td>${formatRupiah(jumPpn)}</td>
            </tr>
        `;
    }

    // total harga
    let jumTotal = jumAfterPph + jumPpn;
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
    if(signaturePad.isEmpty()){
        return Swal.fire({
            icon: "warning",
            text: "Harap berikan tanda tangan terlebih dahulu.",
        });
    }

    let ttd = signaturePad.toDataURL();
    let formData = new FormData();
    formData.append('_token', csrf);
    formData.append('ttd', ttd);
    formData.append('ttd_by', userActive.user_hash);
    formData.append('idKeuangan', $('#idKeuangan').val());
    formData.append('status', 3);

    spinner('show', $(obj));
    ajaxPost(`api/v1/keuangan/keuanganAction`, formData, result => {
        Swal.fire({
            icon: 'success',
            text: 'Invoice berhasil diverifikasi',
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            loadData();
            $('#modal-verif-invoice').modal('hide');
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

function tolakInvoice(obj){
    let note = $('#txt_note').val();
    spinner('show', $(obj));

    let formData = new FormData();
    formData.append('_token', csrf);
    formData.append('status', 91);
    formData.append('note', note);
    formData.append('idKeuangan', $('#idKeuangan').val());
    ajaxPost(`api/v1/keuangan/keuanganAction`, formData, result => {
        Swal.fire({
            icon: 'success',
            text: 'Pengajuan ditolak',
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            window.href = base_url+"/manager/pengajuan";
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