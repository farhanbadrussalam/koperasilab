import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // initialisasi
    const modal = $('#modal-signature');
    const canvas = document.getElementById('signature-canvas')
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
    })
    // Method

    // Event
    modal.on('show.bs.modal', obj => {
        signaturePad.clear();
    })

    $('#signature-clear').on('click', (obj) => {
        signaturePad.clear();
    });

    $('#createSignature').on('click', obj => {
        const item = $(obj.target).data('item');
        const ttd = signaturePad.toDataURL();

        if(item.id_hash){
            const formData = new FormData();
            let text = '';
            switch (item.jenis) {
                case 'frontdesk':
                    text = 'Berkas permohonan lengkap'

                    formData.append('ttd_1', ttd);
                    formData.append('ttd_1_by', userActive.user_hash);
                    formData.append('status', 2);
                    formData.append('flag', 2);
                    formData.append('note', text)

                    break;
                case 'pelaksana':
                    text = 'Berkas diterima'

                    formData.append('ttd_2', ttd);
                    formData.append('ttd_2_by', userActive.user_hash);
                    formData.append('status', 3);
                    formData.append('flag', 3);
                    formData.append('note', text)

                    break;
                default:
                    break;
            }

            ajaxPost(`api/permohonan/update/${item.id_hash}`, formData, result => {
                if(result.success){
                    Swal.fire({
                        icon: 'success',
                        text: text,
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        modal.modal('hide')
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        text: 'Terjadi masalah saat menambah data',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    })
                }
            });
        }
    })
})
