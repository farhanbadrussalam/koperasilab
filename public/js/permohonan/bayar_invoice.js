const invoice = new Invoice({modal : false});

$(function() {
    invoice.addData(dataKeuangan);

    $('#deskripsiInvoice').empty().html(invoice.updateInvoiceDescription());

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