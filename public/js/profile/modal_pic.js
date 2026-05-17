let _uploadSuratKuasaPic = false;

$(document).ready(function () {
    $("#input-nik-pic").on("change", function () {
        const nik = $(this).val();
        checkNIK(this, nik);
    });

    $("#input-email-pic").on("change", function () {
        const email = $(this).val();
        checkEmail(this, email, 'user');
    });
    $("#add-modal-pic").on("hidden.bs.modal", function () {
        $('#form-pic').parsley().reset();
    });

    const rulesPassword = {
        minLength: 8,
        lowerCase: true,
        upperCase: true,
    }

    $('#input-password-pic').parsley({
        trigger: 'input',
    });
    $('#password_confirmation-pic').parsley({
        trigger: 'input',
    });
    rules_password('create', rulesPassword, '#password-rules-pic', '1');

    $('#input-password-pic').on('input', function () {
        const val = $(this).val();
        rules_password('update', rulesPassword, val, '1');
    });

    _uploadSuratKuasaPic = new UploadComponent('uploadSuratKuasa-pic', {
        allowedFileExtensions: ['pdf'],
        camera: false,
        multiple: false,
        template: {
            url: `${base_url}/assets/template/draft_surat_kuasa_rekanan.docx`,
            name: 'Template Surat Kuasa'
        }
    })
})

function openModalPic() {
    $("#add-modal-pic").modal("show");
}


function simpanPic(obj) {
    let parValidate = $('#form-pic').parsley();
    let cekSuratKuasa = _uploadSuratKuasaPic.getData();

    if (!cekSuratKuasa) {
        Swal.fire({
            icon: 'warning',
            text: 'Upload surat kuasa terlebih dahulu'
        });
        return;
    }

    let statusForm = true;
    $('.is-invalid').each(function () {
        statusForm = false;
    });
    if (!statusForm) {
        Swal.fire({
            icon: 'warning',
            text: 'Lengkapi form terlebih dahulu'
        });
        return;
    };

    parValidate.validate();
    if (!parValidate.isValid()) {
        return;
    }

    spinner('show', $(obj));

    Swal.fire({
        title: 'Yakin ingin mengganti data PIC?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak',
    }).then((result) => {
        if (result.isConfirmed) {
            const formParams = new FormData();
            formParams.append('nik_pic', $('#input-nik-pic').val());
            formParams.append('email_pic', $('#input-email-pic').val());
            formParams.append('nama_pic', $('#nama_pic-pic').val());
            formParams.append('jabatan', $('#jabatan_pic-pic').val());
            formParams.append('jenis_kelamin', $('#jenis_kelamin-pic').val());
            formParams.append('telepon', $('#telepon-pic').val());
            formParams.append('alamat', $('#alamat-pic').val());
            formParams.append('password', $('#input-password-pic').val() ?? '');
            formParams.append('password_confirmation', $('#password_confirmation-pic').val());
            formParams.append('surat_kuasa_pic', cekSuratKuasa[0].file);

            let detail_id_hash = $('#detail_id_hash').val();
            if (detail_id_hash) {
                formParams.append('id_perusahaan', detail_id_hash);
            }

            ajaxPost(`api/v1/profile/action/change_pic`, formParams, result => {
                if (result.data.status != 'fail') {
                    Swal.fire({
                        icon: "success",
                        text: result.data.msg,
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        $("#add-modal-pic").modal("hide");
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        text: result.data.msg,
                    });
                }
                spinner('hide', $(obj));
            }, error => {
                spinner('hide', $(obj));
            });
        } else {
            spinner('hide', $(obj));
        }
    })
}
