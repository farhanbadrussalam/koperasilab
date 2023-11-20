import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // initialisasi
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
    });
    const bearer = $('#bearer-token').val();
    const csrf = $('#csrf-token').val();
    const base_url = $('#base_url').val();

    // event
    $('#btn-invalid').on('click', (obj) => {

    });

    $('#btn-valid').on('click', (obj) => {
        const formData = new FormData();
        formData.append('_token', csrf);
        formData.append('level', 3);
        formData.append('idLhu', $('#idLhu').val());

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
                dt_lhu?.ajax.reload();
                $('#modal-confirm-ttd').modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi masalah',
                    text: result.meta?.message
                });
            }
        });
    });

    $('#signature-clear').on('click', (obj) => {
        signaturePad.clear();
    });

    $('#modal-confirm-ttd').on('show.bs.modal', obj => {
        signaturePad.clear();
    })

    // method
    function confirmLhu(status){
        console.log(status);
    }

});
