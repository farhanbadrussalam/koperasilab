$(function() {
    descInvoice(dataKeuangan);

    setDropify('init', '#uploadBuktiBayar', {
        allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg']
    });

    setDropify('init', '#uploadBuktiPph', {
        allowedFileExtensions: ['pdf']
    });

    signaturePadManager = signature(document.getElementById("content-ttd-manager"), {
        text: 'Manager',
        name: dataKeuangan.usersig.name,
        defaultSig: dataKeuangan.ttd
    });

})

function uploadBukti(){
    const formData = new FormData();
    formData.append('_token', csrf);
    formData.append('idKeuangan', dataKeuangan.keuangan_hash);
    formData.append('buktiBayar', $('#uploadBuktiBayar')[0].files[0]);
    formData.append('buktiPph', $('#uploadBuktiPph')[0].files[0]);
    formData.append('status', 4);

    Swal.fire({
        text: 'Apa anda yakin ingin menyimpan data ?',
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
            spinner('show', $('#btn-upload-bukti'));
            ajaxPost(`api/v1/keuangan/action`, formData, result => {
                if(result.meta.code == 200){
                    Swal.fire({
                        icon: 'success',
                        text: 'Pembayaran berhasil disimpan',
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = base_url+"/permohonan/pembayaran";
                    });
                }
            }, error => {
                Swal.fire({
                    icon: "error",
                    text: 'Server error',
                });
                console.error(error.responseJSON.data.msg);
                spinner('hide', $('#btn-upload-bukti'));
            })
        }
    })
}


function descInvoice(keuangan){
    let dataPengajuan = keuangan.permohonan;

    let hargaLayanan = dataPengajuan.harga_layanan;
    let qty = dataPengajuan.jumlah_kontrol+dataPengajuan.jumlah_pengguna;
    let jumLayanan = dataPengajuan.total_harga;
    let periode = JSON.parse(dataPengajuan.periode_pemakaian);
    let jumPph = 0;
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
    
    for (const [i,diskon] of keuangan.diskon.entries()) {
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
    
    if(dataKeuangan.pph) {
        let valPph = dataKeuangan.pph;
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

    if(keuangan.ppn){
        let valPpn = keuangan.ppn;
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