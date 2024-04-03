import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // Initialisasi
    const modal = $('#create-lhu');
    const contentPertanyaan = document.getElementById('content-pertanyaan');
    const sendLhu = $('#send-lhu');
    const canvas = document.getElementById('signature-canvas-createlhu')
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
    })

    // Method

    // Event
    sendLhu.on('click', obj => {
        if(signaturePad.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Please provide a signature first.",
            });
        }

        const ttd = signaturePad.toDataURL()
        const idJadwal = $('#idJadwal').val()
        const arr_answer = [];

        for (const pertanyaan of pertanyaan_lhu) {
            arr_answer.push({
                pertanyaan_id : pertanyaan.pertanyaan_lhu_hash,
                jawaban : $(`#lhu_${pertanyaan.pertanyaan_lhu_hash}`).val()
            })
        }

        const formData = new FormData();
        formData.append('ttd_1', ttd)
        formData.append('id_jadwal', idJadwal)
        formData.append('answer', JSON.stringify(arr_answer))

        $(obj.target).attr('disabled', true).empty().html(`Kirim document <span class="spinner-border spinner-border-sm" role="status"></span>`)

        ajaxPost('api/lhu/sendDokumen', formData, result => {
            if(result.success){
                Swal.fire({
                    icon: 'success',
                    text: result.message,
                    timer: 1000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    modal.modal('hide')
                    dt_tugas?.ajax.reload()
                    $(obj.target).attr('disabled', false).empty().html(`
                        Kirim document <i class="bi bi-send"></i>
                    `)
                });
            }
        })

    })

    $('#signature-clear-createlhu').on('click', (obj) => {
        signaturePad.clear();
    });

    modal.on('show.bs.modal', obj => {
        contentPertanyaan.innerHTML = "";
        signaturePad.clear();

        for (const pertanyaan of pertanyaan_lhu) {
            const divMain = document.createElement('div');
            divMain.className = 'mb-3 col-md-12';

            const title = document.createElement('label')
            title.className = 'label-form';

            const inputAnswer = document.createElement('input')
            inputAnswer.className = 'form-control'
            switch (pertanyaan.type) {
                case 1:
                    title.innerHTML = pertanyaan.title
                    inputAnswer.id = `lhu_${pertanyaan.pertanyaan_lhu_hash}`

                    divMain.appendChild(title)
                    divMain.appendChild(inputAnswer)

                    contentPertanyaan.appendChild(divMain)
                    break;

                case 2:
                    title.innerHTML = pertanyaan.title
                    inputAnswer.id = `lhu_${pertanyaan.pertanyaan_lhu_hash}`

                    divMain.appendChild(title)
                    divMain.appendChild(inputAnswer)

                    contentPertanyaan.appendChild(divMain)
                    break;
            }
        }
    })
})
