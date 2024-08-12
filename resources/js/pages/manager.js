import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // initialisasi
    const canvas_kip = document.getElementById('signature-kip');
    const signaturePadKip = new SignaturePad(canvas_kip, {
        backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
    });
    const canvas_lhu = document.getElementById('signature-lhu');
    const signaturePadLhu = new SignaturePad(canvas_lhu, {
        backgroundColor: 'rgb(255, 255, 255)'
    })
    const modal_lhu = $('#modal-lhu');

    // event
    $('#signature-clear-invoice').on('click', (obj) => {
        signaturePadKip.clear();
    });

    $('#signature-clear-lhu').on('click', (obj) => {
        signaturePadLhu.clear();
    });

    $('#modal-kip').on('show.bs.modal', () => {
        signaturePadKip.clear();
    })

    modal_lhu.on('show.bs.modal', () => {
        signaturePadLhu.clear();
    })

    $('#sendTtdLhu').on('click', () => {
        if(signaturePadLhu.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Please provide a signature first.",
            });
        }
        const ttd_lhu = signaturePadLhu.toDataURL();

        const formData = new FormData();
        formData.append('level', 4);
        formData.append('idLhu', $('#idLhu').val());
        formData.append('ttd_3', ttd_lhu);
        formData.append('ttd_3_by', userActive.user_hash);

        ajaxPost(`api/lhu/validasiLHU`, formData, result => {
            if(result.meta?.code == 200){
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.data.message
                });
                dt_permohonan?.ajax.reload();
                modal_lhu.modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi masalah',
                    text: result.meta?.message
                });
            }
        })
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
        formData.append('ttd_1_by', userActive.user_hash);

        ajaxPost(`api/lhu/validasiKIP`, formData, result => {
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
