let signaturePad = false;
let periodeJs = false;
$(function () {
    const arrPeriode = JSON.parse(dataPermohonan.periode_pemakaian);
    $('#periode-pemakaian').val(arrPeriode.length + ' Periode');
    
    const conten_2 = document.getElementById("content-ttd-2");
    signaturePad = signature(conten_2, {
        text: 'Front desk'
    });
    loadPengguna();
    if(tandaterima){
        loadPertanyaan();
    }

    periodeJs = new Periode(arrPeriode, {
        preview: false,
        max: arrPeriode.length
    });
    
    $('#btn-periode').on('click', () => {
        periodeJs.show();
    });

    periodeJs.on('periode.simpan', () => {
        const dataPeriode = periodeJs.getData();
        const params = new FormData();
        params.append('idPermohonan', dataPermohonan.permohonan_hash);
        params.append('periodePemakaian', JSON.stringify(dataPeriode));

        ajaxPost(`api/v1/permohonan/tambahPengajuan`, params, result => {
            Swal.fire({
                icon: 'success',
                text: 'Update periode successfully',
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }, error => {
            const result = error.responseJSON;
            if(result?.meta?.code && result.meta.code == 500){
                Swal.fire({
                    icon: "error",
                    text: 'Server error',
                });
                console.error(result.data.msg);
            }else{
                Swal.fire({
                    icon: "error",
                    text: 'Server error',
                });
                console.error(result.message);
            }
        });
    });

    loadPelanggan();
});

function loadPelanggan() {
    const pelanggan = dataPermohonan.pelanggan;
    const perusahaan = pelanggan.perusahaan;
    
    $('#nama-instansi').val(perusahaan.nama_perusahaan);
    $('#nama-pic').val(pelanggan.name);
    $('#jabatan-pic').val(pelanggan.jabatan);
    $('#email-pic').val(pelanggan.email);
    $('#telepon-pic').val(pelanggan.telepon);
    $('#npwp-pic').val(perusahaan.npwp_perusahaan);

    // Alamat
    let alamatUtama = false;
    let kodeposUtama = false;
    for (const value of perusahaan.alamat) {
        let valAlamat = value.alamat;
        let valKodepos = value.kode_pos;

        if(value.jenis == 'utama'){
            alamatUtama = value.alamat;
            kodeposUtama = value.kode_pos;
        }else{
            if(value.status){
                valAlamat = value.alamat;
                valKodepos = value.kode_pos;
            }else{
                valAlamat = alamatUtama;
                valKodepos = kodeposUtama;
            }
        }

        $(`#alamat-${value.jenis}`).val(valAlamat);
        $(`#txt-kode-pos-${value.jenis}`).val(valKodepos);
    }
}

function loadPertanyaan(){
    let html = '';
    $('#content-pertanyaan').html('');
    for (const [i, value] of tandaterima.entries()) {
        let htmlAnswer = ``;
        if(value.type == 1){
            htmlAnswer = `<textarea name="answer_${i}" id="answer_${i}" cols="30" rows="3" class="form-control"></textarea>`;
        }else if(value.type == 2){
            htmlAnswer = `
                <div class="my-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="answer_${i}" id="answer_${i}_baik" value="baik" onclick="toggleReason(${i}, false)">
                        <label class="form-check-label" for="answer_${i}_baik">Baik</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="answer_${i}" id="answer_${i}_cacat" value="cacat" onclick="toggleReason(${i}, true)">
                        <label class="form-check-label" for="answer_${i}_cacat">Cacat</label>
                    </div>
                    <div>
                        <input type="text" class="form-control w-100" id="reason_${i}" placeholder="Bila cacat, sebutkan : ....." disabled>
                    </div>
                </div>
            `;
        }

        html += `
            <div class="col-sm-6 mt-2">
                <label for="">${value.pertanyaan} :</label>
                ${htmlAnswer}
            </div>
        `;
    }

    $('#content-pertanyaan').html(html);

}

function toggleReason(index, enable) {
    $(`#reason_${index}`).prop('disabled', !enable);
}

function loadPengguna(){
    let params = {
        idPermohonan: dataPermohonan.permohonan_hash
    }
    $('#pengguna-placeholder').show();
    $('#pengguna-list-container').hide();

    ajaxGet(`api/v1/permohonan/listPengguna`, params, result => {
        let html = '';
        for (const [i,pengguna] of result.data.entries()) {
            let txtRadiasi = '';
            pengguna.radiasi?.map(nama_radiasi => txtRadiasi += `<span class="badge rounded-pill text-bg-secondary me-1 mb-1">${nama_radiasi}</span>`);
            html += `
                <div class="card mb-2 border-dark">
                    <div class="card-body row align-items-center">
                        <div class="col-md-5 lh-sm d-flex align-items-center">
                            <span class="col-form-label me-2">${i + 1}</span>
                            <div class="mx-2">
                                <div class="fw-bolder">${pengguna.nama}</div>
                                <small class="text-body-secondary fw-light">${pengguna.posisi}</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                        ${[1,2].includes(pengguna.status) ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-danger">Inactive</span>'}
                        </div>
                        <div class="col-md-3 d-flex flex-wrap justify-content-center">${txtRadiasi}</div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-sm btn-outline-secondary" data-path="${pengguna.media.file_path}" data-file="${pengguna.media.file_hash}" onclick="showPreviewKtp(this)" title="Show ktp">
                                <i class="bi bi-file-person-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        if(result.data.length == 0){
            html = `
                <div class="d-flex flex-column align-items-center py-3">
                    <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                    <span class="fw-bold mt-3 text-muted">No Data Available</span>
                </div>
            `;
        }

        $('#pengguna-list-container').html(html);
        $('#pengguna-placeholder').hide();
        $('#pengguna-list-container').show();
    }, error => {
        const result = error.responseJSON;
        if(result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }
    })

}

function verif_kelengkapan(status, obj){
    if(status == 'lengkap'){
        const formQuestion = $('#content-pertanyaan'); // You already have this
        
        // Get all form elements within #content-pertanyaan
        const answerTandaterima = [];
        if(tandaterima){
            for (const [i, value] of tandaterima.entries()) {
                let elementAnswer = false;
                if(value.type == 1){
                    elementAnswer = $(`#answer_${i}`).val();
                    answerTandaterima.push({
                        id: value.pertanyaan_hash,
                        answer: elementAnswer,
                        note: ''
                    });
                } else if(value.type == 2) {
                    elementAnswer = $(`[name="answer_${i}"]:checked`).val();
                    let note = '';
                    if(elementAnswer == 'cacat'){
                        note = $(`#reason_${i}`).val();
                    }
                    answerTandaterima.push({
                        id: value.pertanyaan_hash,
                        answer: elementAnswer,
                        note: note
                    });
                }
            }
        }
        
        if(signaturePad.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Harap berikan tanda tangan terlebih dahulu.",
            });
        }

        Swal.fire({
            icon: 'warning',
            title: 'Apakah data sudah lengkap?',
            showCancelButton: true,
            confirmButtonText: 'Iya',
            cancelButtonText: 'Tidak',
            customClass: {
                confirmButton: 'btn btn-outline-success mx-1',
                cancelButton: 'btn btn-outline-danger mx-1'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then(result => {
            if(result.isConfirmed){

                let ttd = signaturePad.toDataURL();
                let formData = new FormData();
                formData.append('_token', csrf);
                formData.append('ttd', ttd);
                formData.append('status', status);
                formData.append('idPermohonan', dataPermohonan.permohonan_hash);
                formData.append('tandaterima', JSON.stringify(answerTandaterima));

                spinner('show', obj);
                ajaxPost(`api/v1/permohonan/verifikasi/cek`, formData, result => {
                    Swal.fire({
                        icon: 'success',
                        text: 'Permohonan terverifikasi',
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        createInvoice(dataPermohonan.permohonan_hash);
                        createPenyelia(dataPermohonan.permohonan_hash);
                        window.location.href = base_url+"/staff/permohonan";
                    });
                }, error => {
                    const result = error.responseJSON;
                    if(result.meta.code == 500){
                        spinner('hide', obj);
                        Swal.fire({
                            icon: "error",
                            text: 'Server error',
                        });
                        console.error(result.data.msg);
                    }
                });
            }
        })
    }else if(status == 'tidak_lengkap'){
        $('#modal-verif-invalid').modal('show');
    }
}

function createInvoice(idPermohonan){
    const formData = new FormData();
    formData.append('idPermohonan', idPermohonan);
    formData.append('status', 1);
    ajaxPost(`api/v1/keuangan/action`, formData, result => {})
}

function createPenyelia(idPermohonan){
    const formData = new FormData();
    formData.append('idPermohonan', idPermohonan);
    formData.append('status', 1);
    ajaxPost(`api/v1/penyelia/action`, formData, result => {})
}


function return_permohonan(obj){
    let note = $('#txt_note').val();
    spinner('show', obj);

    let formData = new FormData();
    formData.append('_token', csrf);
    formData.append('status', 'tidak_lengkap');
    formData.append('note', note);
    formData.append('idPermohonan', dataPermohonan.permohonan_hash);
    ajaxPost(`api/v1/permohonan/verifikasi/cek`, formData, result => {
        Swal.fire({
            icon: 'success',
            text: 'Permohonan dikembalikan',
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            window.location.href = base_url+"/staff/permohonan";
        });
    }, error => {
        const result = error.responseJSON;
        if(result.meta.code == 500){
            spinner('hide', obj);
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }
    })
}

function areThereEmptyFields(formElements) {
    let isEmpty = false; // Assume no empty fields initially
  
    // Iterate through each form element
    formElements.each(function() {
      const element = $(this); // Get the jQuery object for the element
  
      // Check for empty values based on element type
      if (element.is('input[type="text"], input[type="email"], input[type="number"], textarea') && element.val().trim() === "") {
        isEmpty = true; // Found an empty field
        return false; // Exit the .each() loop early
      } else if (element.is('input[type="radio"], input[type="checkbox"]') && !element.is(':checked')) {
        // Check if at least one radio button in a group is selected
        const name = element.attr('name');
        if ($(`input[name="${name}"]:checked`).length === 0) {
          isEmpty = true;
          return false;
        }
      } else if (element.is('select') && element.val() === null) {
        isEmpty = true;
        return false;
      }
    });
  
    return isEmpty;
}