import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // initialisasi
    const canvas_lhu = document.getElementById('signature-lhu');
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

    $('#sendTtdLhu').on('click', () => {
        if(signaturePadLhu.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Please provide a signature first.",
            });
        }

        const ttd = signaturePadLhu.toDataURL();

        const formData = new FormData();
        formData.append('_token', csrf);
        formData.append('level', 4);
        formData.append('idLhu', $('#idLhu').val());
        formData.append('ttd_2', ttd);

        $.ajax({
            url: `${base_url}/api/lhu/validasiLHU`,
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
                    text: result.data.message
                });
                dt_permohonan?.ajax.reload();
                $('#modal-lhu').modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi masalah',
                    text: result.meta?.message
                });
            }
        })
    });

    $('#modal-lhu').on('show.bs.modal', () => {
        signaturePadLhu.clear();
    })
})
