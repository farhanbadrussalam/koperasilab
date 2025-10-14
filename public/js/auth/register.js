let _password = false;
let _upload = false;
$(function () {
    $('#form-registration').parsley();

    $('#input-email').on('change', function(){
        checkEmail(this, $(this).val(), 'user');
    });
    $('#email_instansi').on('change', function(){
        checkEmail(this, $(this).val(), 'instansi');
    });

    const rulesPassword = {
        minLength: 8,
        lowerCase: true,
        upperCase: true,
    }

    $('#input-password').parsley({
        trigger: 'input',
    });
    $('#password_confirmation').parsley({
        trigger: 'input',
    });
    rules_password('create', rulesPassword, '#password-rules', '1');

    $('#input-password').on('input', function () {
        const val = $(this).val();
        rules_password('update', rulesPassword, val, '1');
    });

    _upload = new UploadComponent("uploadSuratKuasa", {
        allowedFileExtensions: ['pdf'],
        camera: false,
        multiple: false,
        form: true
    });
})

function searchAkun(obj){
    let search = $('#input-cek-akun').val();

    let cekAkun = $('#input-cek-akun').parsley();
    cekAkun.validate();
    if(!cekAkun.isValid()){
        return;
    }
    $('#alert-cek-akun').addClass('d-none');

    spinner('show', $(obj));
    const params = new FormData();
    params.append('search', search);
    ajaxPost('api/v1/search_akun', params, result => {
        $('#input-cek-akun').val('');
        if(result.meta.message != 'Fail'){
            $('#alert-cek-akun').html('NIK <b>'+search+'</b> Anda sudah terdaftar di sistem kami');
            $('#alert-cek-akun').removeClass('d-none');
        } else {
            $('#registration-form').removeClass('d-none');
            $('#cek-akun-form').addClass('d-none');

            $('#input-nik').val(search);
        }
        spinner('hide', $(obj));
    }, error => {
        spinner('hide', $(obj));
    }, false, false);
}

function changeNik(){
    const nik = $('#input-nik').val();

    $('#input-cek-akun').val(nik);
    $('#cek-akun-form').removeClass('d-none');
    $('#registration-form').addClass('d-none');
}

function simpan(){
    let statusForm = true;
    $('.is-invalid').each(function(){
        statusForm = false;
    });

    if(statusForm){
        const cekSuratKuasa = _upload.getData();
        if(!cekSuratKuasa){
            Swal.fire({
                icon: 'warning',
                text: 'Upload surat kuasa terlebih dahulu'
            });
            return;
        }

        // validate form
        if(!$('#form-registration').parsley().validate()){
            return;
        }

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Apakah Anda ingin mendaftar?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Daftar!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#form-registration').submit();
            }
        })

    } else {
        Swal.fire({
            icon: 'warning',
            text: 'Lengkapi form terlebih dahulu'
        })
    }
}
