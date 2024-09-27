let signaturePad = false;
$(function () {
    const periode = JSON.parse(dataPermohonan.periode_pemakaian);
    $('#periode-pemakaian').val(periode.length + ' Periode');
    // const conten_1 = document.getElementById("content-ttd-1");
    // signature(conten_1, {
    //     text: 'Manager',
    //     defaultSig: `${base_url}/icons/default/white.png`
    // })
    const conten_2 = document.getElementById("content-ttd-2");
    signaturePad = signature(conten_2, {
        text: 'Front desk'
    });
    loadPengguna();
});

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
            pengguna.radiasi?.map(data => txtRadiasi += `<span class="badge rounded-pill text-bg-secondary me-1 mb-1">${data.nama_radiasi}</span>`);
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
                if(signaturePad.isEmpty()){
                    return Swal.fire({
                        icon: "warning",
                        text: "Harap berikan tanda tangan terlebih dahulu.",
                    });
                }

                let ttd = signaturePad.toDataURL();
                let formData = new FormData();
                formData.append('_token', csrf);
                formData.append('ttd', ttd);
                formData.append('status', status);
                formData.append('idPermohonan', dataPermohonan.permohonan_hash);

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
    ajaxPost(`api/v1/keuangan/keuanganAction`, formData, result => {})
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