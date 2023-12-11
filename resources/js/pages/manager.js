import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // initialisasi
    const canvas_kip = document.getElementById('signature-kip');
    const signaturePadKip = new SignaturePad(canvas_kip, {
        backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
    });
    const bearer = $('#bearer-token').val();
    const csrf = $('#csrf-token').val();
    const base_url = $('#base_url').val();

    // event
    $('#signature-clear-invoice').on('click', (obj) => {
        signaturePadKip.clear();
    });

    $('#modal-kip').on('show.bs.modal', () => {
        signaturePadKip.clear();
    })

    $('#sendTtdKIP').on('click', () => {
        if(signaturePadKip.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Please provide a signature first.",
            });
        }

        const ttd = signaturePadKip.toDataURL();

        const formData = new FormData();
        formData.append('_token', csrf);
        formData.append('status', 2);
        formData.append('idKip', $('#idKip').val());
        formData.append('ttd_1', ttd);

        $.ajax({
            url: `${base_url}/api/lhu/validasiKIP`,
            method: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            headers: {
                'Authorization' : `Bearer ${bearer}`
            },
            data: formData
        }).done(result => {
            if(result.meta?.code == 200){
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.data.message
                });
                dt_permohonan?.ajax.reload();
                $('#modal-kip').modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi masalah',
                    text: result.meta?.message
                });
            }
        })
    });
})
