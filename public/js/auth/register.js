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

    _password = $('#password_confirmation').parsley();
    $('#password_confirmation').on('input', function(obj) {
        _password.removeError('manualError');
        if($(this).val() != $('#input-password').val()){
            $(this).addClass('is-invalid');
            if(!_password.errorsMessages || !_password.errorsMessages['passwordMessage']) {
                _password.addError('manualError', {
                    message: 'Password tidak sama'
                });
            }
        } else {
            $(this).removeClass('is-invalid');
        }
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

    if(search.length == 0){
        return Swal.fire({
            icon: 'warning',
            text: `Silahkan masukan NIK Anda terlebih dahulu`
        });
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

function checkEmail(obj, email, jenis){
    // mengecek formatnya email
    if(!email.match(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/)){
        return;
    };

    const params = new FormData();
    params.append('email', email);
    params.append('jenis', jenis);
    spinner('show', $(obj).parent().find('.form-label'), {
        place: 'after'
    })
    ajaxPost('api/v1/check_email', params, result => {
        let _email = $(obj).parsley();
        if(result.meta.message == 'Fail'){
            $(obj).removeClass('is-invalid').addClass('is-valid');
            _email.removeError('emailMessage');
        } else {
            $(obj).addClass('is-invalid');
            _email.addError('emailMessage', {
                message: 'E-mail sudah terdaftar',
                updateClass: true
            });
        }
        spinner('hide', $(obj).parent().find('.form-label'));
    }, error => {
        console.log(error);
    }, false, false);
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

        // const forms = Array.from(document.getElementById('form-registration').elements);

        // const formDatas = new FormData();

        // forms.forEach(form => {
        //     if(form.name){
        //         formDatas.append(form.name, form.value);
        //     }
        // });

        // let suratKuasa = _upload.getData();
        // formDatas.append('surat_kuasa', suratKuasa[0].file);

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
                // ajaxPost('register', formDatas, result => {

                // }, error => {

                // }, false, false);
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
