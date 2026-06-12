let _password = false;
let _upload = false;
$(function () {
    $('#form-registration').parsley();

    $('#input-email').on('change', function () {
        checkEmail(this, $(this).val(), 'user');
    });
    $('#email_instansi').on('change', function () {
        checkEmail(this, $(this).val(), 'instansi');
    });

    $('input[name="pelanggan_tipe"]').on('change', function () {
        $('#section-instansi').slideDown();

        if ($(this).val() === 'baru') {
            $('#form-instansi-detail').slideDown();
            $('.instansi-detail-input').attr('required', true);

            // Tampilkan input text, sembunyikan select2
            $('#nama_instansi_lama').removeAttr('name required').next('.select2-container').hide();
            if ($('#nama_instansi_lama').parsley()) $('#nama_instansi_lama').parsley().reset();
            $('#nama_instansi_baru').attr({ 'name': 'nama_instansi', 'required': true }).show();
        } else {
            // Tampilkan select2, sembunyikan input text
            $('#nama_instansi_baru').removeAttr('name required').hide();
            if ($('#nama_instansi_baru').parsley()) $('#nama_instansi_baru').parsley().reset();
            $('#nama_instansi_lama').attr({ 'name': 'nama_instansi', 'required': true }).show();
            $('#nama_instansi_lama').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari Nama Instansi...',
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: `${base_url}/api/v1/profile/list/perusahaan`, // Pastikan endpoint ini sesuai dengan API backend Anda
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            filter: { search: params.term },
                            limit: 10
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: $.map(response.data, function (item) {
                                return { id: item.perusahaan_hash, text: item.nama_perusahaan }; // Sesuaikan key dari response API
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#form-instansi-detail').slideUp();
            $('.instansi-detail-input').removeAttr('required');
            $('.instansi-detail-input').each(function () {
                if ($(this).parsley()) {
                    $(this).parsley().reset();
                }
            });
        }
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
        form: true,
        template: {
            url: `${base_url}/assets/template/draft_surat_kuasa_rekanan.docx`,
            name: 'Template Surat Kuasa'
        }
    });
})

function searchAkun(obj) {
    let search = $('#input-cek-akun').val();

    let cekAkun = $('#input-cek-akun').parsley();
    cekAkun.validate();
    if (!cekAkun.isValid()) {
        return;
    }
    $('#alert-cek-akun').addClass('d-none');

    spinner('show', $(obj));
    const params = new FormData();
    params.append('search', search);
    ajaxPost('api/v1/search_akun', params, result => {
        $('#input-cek-akun').val('');
        if (result.meta.message != 'Fail') {
            $('#alert-cek-akun').html('NIK <b>' + search + '</b> Anda sudah terdaftar di sistem kami');
            $('#alert-cek-akun').removeClass('d-none');
        } else {
            $('#registration-form').removeClass('d-none');
            $('#cek-akun-form').addClass('d-none');

            $('#input-nik').val(search);
        }
        spinner('hide', $(obj));
    }, error => {
        spinner('hide', $(obj));
    }, {
        onMiddleware: false
    });
}

function changeNik() {
    const nik = $('#input-nik').val();

    $('#input-cek-akun').val(nik);
    $('#cek-akun-form').removeClass('d-none');
    $('#registration-form').addClass('d-none');
}

function simpan() {
    let statusForm = true;
    $('.is-invalid').each(function () {
        statusForm = false;
    });

    if (statusForm) {
        const cekSuratKuasa = _upload.getData();
        if (!cekSuratKuasa) {
            Swal.fire({
                icon: 'warning',
                text: 'Upload surat kuasa terlebih dahulu'
            });
            return;
        }

        // validate form
        if (!$('#form-registration').parsley().validate()) {
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

function previewAvatar(obj) {
    const file = obj.files[0];
    if (obj.files && file) {
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'warning',
                text: 'Format gambar harus PNG, JPG, atau JPEG'
            });
            obj.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'warning',
                text: 'Ukuran gambar maksimal 2MB'
            });
            obj.value = '';
            return;
        }

        const reader = new FileReader();
        const preview = document.getElementById('avatar-preview');

        reader.onload = function (e) {
            preview.src = e.target.result;
        }

        reader.readAsDataURL(file);
    }
}
