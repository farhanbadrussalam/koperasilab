import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', function () {
    // Initialisasi
    const modal = $('#modal-surat-tugas');
    const canvas = document.getElementById('signature-canvas-surattugas')
    const inputDateStart = $('#inputDateStart');
    const inputDateEnd  = $('#inputDateEnd');
    const listPetugas = $('#listPetugas');
    const addPetugas = $('#addPetugas')
    const btnSendTugas = $('#btnSendTugas')
    const formSuratTugas = document.getElementById('formSuratTugas')
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
    })
    let countPetugas = 1

    inputDateStart.flatpickr({
        enableTime: true,
        minDate: 'today',
        dateFormat: "Y-m-d H:i",
        time_24hr: true
    })

    inputDateEnd.flatpickr({
        enableTime: true,
        minDate: 'today',
        dateFormat: "Y-m-d H:i",
        time_24hr: true
    })

    // Method
    function resetForm() {
        inputDateEnd.val('')
        listPetugas.empty()
        countPetugas = 1
    }
    function validateForm(form){
        let valid = true
        for (const [index, value] of form) {
            index == 'date_end' && (value == '' && (inputDateEnd.addClass('border-danger'), valid = false)) //required
        }

        return valid
    }
    function removePetugas(obj) {
        $(obj.target).parent().parent().parent().remove()
        countPetugas--
    }

    function createPetugas() {
        // Membuat elemen div utama dengan kelas 'row' dan 'mb-2'
        let mainDiv = document.createElement('div');
        mainDiv.classList.add('row', 'mb-2');

        // Membuat elemen div pertama (col-6)
        let col1Div = document.createElement('div');
        col1Div.classList.add('col-6');

        // Membuat elemen div dengan kelas 'input-group'
        let inputGroupDiv = document.createElement('div');
        inputGroupDiv.classList.add('input-group');

        // Membuat elemen tombol dengan kelas 'btn' dan 'btn-outline-danger'
        let button = document.createElement('button');
        button.classList.add('btn', 'btn-outline-danger', 'bi', 'bi-trash');
        button.setAttribute('type', 'button');
        button.onclick = removePetugas
        countPetugas != 1 && inputGroupDiv.appendChild(button);

        // Membuat elemen select dengan atribut name dan id yang diberikan
        let select = document.createElement('input');
        select.setAttribute('name', 'idPetugas[]');
        select.setAttribute('id', 'idPetugas');
        select.classList.add('form-control');
        select.placeholder = 'Nama petugas'
        inputGroupDiv.appendChild(select);

        // Menambahkan elemen inputGroupDiv ke dalam col1Div
        col1Div.appendChild(inputGroupDiv);

        // Menambahkan col1Div ke dalam mainDiv
        mainDiv.appendChild(col1Div);

        // Membuat elemen div kedua (col-6)
        let col2Div = document.createElement('div');
        col2Div.classList.add('col-6');

        // Membuat elemen input dengan tipe 'text' dan atribut name dan id yang diberikan
        let input = document.createElement('input');
        input.setAttribute('type', 'text');
        input.setAttribute('name', 'jobsPetugas[]');
        input.setAttribute('id', 'jobsPetugas');
        input.setAttribute('placeholder', 'Tugas');
        input.classList.add('form-control');

        // Menambahkan elemen input ke dalam col2Div
        col2Div.appendChild(input);

        // Menambahkan col2Div ke dalam mainDiv
        mainDiv.appendChild(col2Div);

        listPetugas.append(mainDiv)
        countPetugas++
    }

    // Event
    inputDateEnd.on('focus', obj => {
        $(obj.target).removeClass('border-danger')
    })

    modal.on('show.bs.modal', obj => {
        resetForm()
        createPetugas()
        signaturePad.clear();
    })

    $('#signature-clear-surattugas').on('click', (obj) => {
        signaturePad.clear();
    });

    addPetugas.on("click", obj => {
        createPetugas()
    })

    btnSendTugas.on("click", obj => {
        if(signaturePad.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Please provide a signature first.",
            });
        }

        const ttd = signaturePad.toDataURL();
        const formData = new FormData(formSuratTugas);
        formData.append('ttd_1', ttd)
        formData.append('ttd_1_by', userActive.user_hash);

        $(obj.target).attr('disabled', true).empty().html(`
            <span class="spinner-border spinner-border-sm" role="status"></span> Kirim tugas
        `)

        if(validateForm(formData)) {
            ajaxPost('api/jadwal/addJadwal', formData, result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        text: result.message,
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        modal.modal('hide')
                        dt_layanan?.ajax.reload()
                        resetForm()
                        $(obj.target).attr('disabled', false).empty().html(`
                            Kirim tugas
                        `)
                    });
                } else {
                    Swal.fire({
                        icon: 'danger',
                        text: 'Terjadi masalah saat menyimpan data',
                        timer: 2000,
                        timerProgressBar: true,
                    })
                }
            })
        }
    })
})
