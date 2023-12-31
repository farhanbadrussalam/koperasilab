import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // initialisasi
    const canvas_lhu = document.getElementById('signature-keuangan');
    const signaturePadLhu = new SignaturePad(canvas_lhu, {
        backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
    });
    const bearer = $('#bearer-token').val();
    const csrf = $('#csrf-token').val();
    const base_url = $('#base_url').val();

    // event
    $('#signature-clear').on('click', (obj) => {
        signaturePadLhu.clear();
    });

    $('#sendKwitansi').on('click', () => {
        if(signaturePadLhu.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Please provide a signature first.",
            });
        }

        const ttd = signaturePadLhu.toDataURL();
        const idKip = $('#idKip').val();

        const formData = new FormData();
        formData.append('_token', csrf);
        formData.append('idKip', idKip);
        formData.append('ttd_1', ttd);
        formData.append('status', 4);

        $.ajax({
            url: `${base_url}/api/lhu/validasiKIP`,
            method: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            headers:{
                'Authorization': `Bearer ${bearer}`
            },
            data: formData
        }).done(result => {
            if(result.meta?.code == 200){
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Kwitansi berhasil diterbitkan'
                });
                dt_keuangan?.ajax.reload();
                $('#modal-kwitansi').modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi masalah',
                    text: result.meta?.message
                });
            }
        })
    });

    $('#modal-kwitansi').on('show.bs.modal', () => {
        signaturePadLhu.clear();
    })
})
