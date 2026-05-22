const invoice = new Invoice({ modal: false });
let buktiBayar = false;
let buktiBayarPph = false;
let modalDoc = new ModalDocument();

$(function () {
    invoice.addData(dataKeuangan);

    $('#deskripsiInvoice').empty().html(invoice.updateInvoiceDescription());

    // signaturePadManager = signature(document.getElementById("content-ttd-manager"), {
    //     text: 'Manager',
    //     name: dataKeuangan.usersig.name,
    //     defaultSig: dataKeuangan.ttd
    // });

    // Inisialisasi upload component
    buktiBayar = new UploadComponent('uploadBuktiBayar', {
        camera: false,
        allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg', 'pdf'],
        urlUpload: {
            url: `api/v1/keuangan/uploadBuktiBayar`,
            urlDestroy: `api/v1/keuangan/destroyBuktiBayar`,
            idHash: dataKeuangan.keuangan_hash
        },
        maxSize: 20
    });
    buktiBayar.addData(dataKeuangan.media_bukti_bayar);

    if (dataKeuangan.pph) {
        buktiBayarPph = new UploadComponent('uploadBuktiBayarPph', {
            camera: false,
            allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg', 'pdf'],
            urlUpload: {
                url: `api/v1/keuangan/uploadBuktiPph`,
                urlDestroy: `api/v1/keuangan/destroyBuktiPph`,
                idHash: dataKeuangan.keuangan_hash
            },
            maxSize: 20
        });
        buktiBayarPph.addData(dataKeuangan.media_bukti_bayar_pph);
        $('#divBuktiPph').show();
    }


})

function btnSimpan(obj) {
    let dataBuktiBayar = buktiBayar.getData();
    let dataBuktiBayarPph = dataKeuangan.pph ? buktiBayarPph.getData() : [];

    if (dataBuktiBayar.length === 0) {
        Swal.fire({
            icon: 'warning',
            text: 'Upload bukti bayar'
        });
        return;
    }

    // if (dataKeuangan.pph && dataBuktiBayarPph.length === 0) {
    //     Swal.fire({
    //         icon: 'warning',
    //         text: 'Upload bukti bayar PPH'
    //     });
    //     return;
    // }

    const formData = new FormData();
    formData.append('idKeuangan', dataKeuangan.keuangan_hash);
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
        if (result.isConfirmed) {
            spinner('show', $(obj));
            ajaxPost(`api/v1/keuangan/action`, formData, result => {
                if (result.meta.code == 200) {
                    Swal.fire({
                        icon: 'success',
                        text: 'Pembayaran berhasil disimpan',
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = base_url + "/permohonan/pembayaran";
                    });
                }
            }, error => {
                spinner('hide', $(obj));
            })
        }
    })
}

function openModalDoc(obj) {
    const url = $(obj).data('url');
    const title = $(obj).data('title') || 'Dokumen';
    modalDoc.show(url, {
        title: title
    });
}