import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // initialisasi
    const modal = $('#modal-confirm-ttd');
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
    });
    const contentPertanyaan = document.getElementById('content-pertanyaan');

    // event
    $('#btn-invalid').on('click', (obj) => {
        const formData = new FormData();
        formData.append('_token', csrf);
        formData.append('active', 99);
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
                    icon: 'danger',
                    text: 'LHU tidak valid'
                });
                dt_lhu?.ajax.reload();
                dt_layanan?.ajax.reload();
                modal.modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi masalah',
                    text: result.meta?.message
                });
            }
        })
    });

    $('#btn-valid').on('click', (obj) => {
        if(signaturePad.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Please provide a signature first.",
            });
        }

        let ttd = signaturePad.toDataURL();

        const formData = new FormData();
        formData.append('level', 3);
        formData.append('idLhu', $('#idLhu').val());
        formData.append('ttd_2', ttd);
        formData.append('ttd_2_by', userActive.user_hash);

        ajaxPost(`api/lhu/validasiLHU`, formData, result => {
            if(result.meta?.code == 200){
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.data.message
                });
                dt_lhu?.ajax.reload();
                modal.modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi masalah',
                    text: result.meta?.message
                });
            }
        })
    });

    $('#signature-clear').on('click', (obj) => {
        signaturePad.clear();
    });

    modal.on('show.bs.modal', obj => {
        signaturePad.clear();
        let idLhu = $('#idLhu').val();

        contentPertanyaan.innerHTML = '';
        ajaxGet(`api/lhu/getDokumenLHU/${idLhu}`, false, lhu => {
            const data = lhu.data;
            for (const jawaban of data.jawaban) {
                createPertanyaan(jawaban);
            }

            $('#nameSignature').html(userActive.name)

            $('#ttd_1_lhu').attr('src', data.ttd_1)
            $('#ttd_1_by_lhu').html(data.signature_1.name);
        })
    })


    // method
    function createPertanyaan(list){
        const mainDiv = document.createElement('div');
        mainDiv.className = 'col-md-6 mb-2'

        const labelP = document.createElement('label')
        labelP.className = 'fw-bolder';
        labelP.innerHTML = list.pertanyaan.title

        const inputP = document.createElement('input')
        inputP.className = 'form-control'
        inputP.disabled = true
        inputP.value = list.jawaban

        mainDiv.appendChild(labelP)
        mainDiv.appendChild(inputP)

        contentPertanyaan.appendChild(mainDiv)
    }

});
