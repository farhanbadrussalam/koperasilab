let signaturePad;
let _uploadSuratKuasa = false;
let detail = false;
let uploadStempel = false;
let modalDoc = new ModalDocument();
$(function () {
    loadForm(profile);
    loadInstansi(profile);
    if (role.includes('Pelanggan') && profile.perusahaan) {
        loadDocumentKop();
    }
    // Cek hash di URL saat halaman dimuat
    const hash = window.location.hash;
    if (hash) {
        // Hapus # dari hash
        const tabId = hash.substring(1);
        // Cari tombol tab yang sesuai dan klik
        const tabButton = $(`#${tabId}-tab`);
        if (tabButton.length) {
            tabButton.click();
        }
    }

    // upload avatar profile instantly via AJAX
    $('#profile-avatar-input').on('change', function () {
        const file = this.files[0];
        if (!file) return;

        // validate size and mime type
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'warning',
                text: 'Format gambar harus PNG, JPG, atau JPEG'
            });
            this.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'warning',
                text: 'Ukuran gambar maksimal 2MB'
            });
            this.value = '';
            return;
        }

        // Show spinner/loading visual
        Swal.fire({
            title: 'Sedang Mengunggah...',
            text: 'Harap tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('file', file);
        formData.append('idHash', profile.user_hash);

        ajaxPost('api/v1/profile/uploadAvatar', formData, result => {
            Swal.close();
            if (result.meta.message === 'Fail') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: result.data.msg || 'Terjadi kesalahan saat mengunggah avatar.'
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    text: 'Foto profil berhasil diperbarui',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            }
        }, error => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan sistem.'
            });
        });
    });

    // upload stempel
    uploadStempel = new UploadComponent('upload-stempel', {
        camera: false,
        allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg'],
        data: profile?.perusahaan?.stempel_perusahaan ? [profile?.perusahaan?.stempel_perusahaan] : [],
        multiple: false,
        urlUpload: {
            url: `api/v1/profile/uploadStempel`,
            urlDestroy: `api/v1/profile/destroyStempel`,
            idHash: profile.perusahaan?.perusahaan_hash
        }
    })

    $('#btn-upload-ttd').click(function () {
        if (signaturePad.isEmpty()) {
            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Tanda tangan tidak boleh kosong'
            });
        }
        spinner('show', $(this));

        let ttd = signaturePad.toDataURL();
        const formData = new FormData();
        formData.append('ttd', ttd);
        formData.append('idProfile', profile.user_hash);
        ajaxPost(`api/v1/profile/action`, formData, result => {
            if (result.meta.code == 200) {
                profile.ttd_image = result.data.ttd_image;
                loadForm(profile);
                spinner('hide', $(this));
            }
        })
    });

    $(`#btn-hapus-ttd`).click(function () {
        spinner('show', $(this));
        const formData = new FormData();
        formData.append('idProfile', profile.user_hash);
        formData.append('ttd', '');
        ajaxPost(`api/v1/profile/action`, formData, result => {
            if (result.meta.code == 200) {
                document.getElementById('show-ttd').innerHTML = '';
                document.getElementById('ttd-preview').innerHTML = '';
                profile.ttd = '';
                loadForm(profile);
                spinner('hide', $(this));
            }
        })
    });

    $('#old_password').parsley({
        trigger: 'input'
    });

    const rulesPassword = {
        minLength: 8,
        lowerCase: true,
        upperCase: true,
    }

    // 3) Saat password berubah, re-validate konfirmasi
    $('#new_password').on('input', function () {
        const val = $(this).val();
        let cek = rules_password('update', rulesPassword, val, '2');
        $('#strengthBar').css('width', cek.percentage + '%');

        document.getElementById('strengthBar').className = 'progress-bar ' + cek.backgroundColor;
    });

    $('#form-change-password').parsley({
        trigger: 'input',
    });

    rules_password('create', rulesPassword, '#password-rules', '2');

    $('#email_instansi_new').on('change', function () {
        checkEmail(this, $(this).val(), 'instansi');
    });

    $('#btnEnableEdit').click(function () {
        // Aktifkan semua input kecuali email
        document.querySelectorAll('input:not([readonly]), textarea, button[title="Hapus File"], select').forEach(el => el.disabled = false);

        // Munculkan area upload & tombol simpan
        document.getElementById('actionButtons').classList.replace('d-none', 'd-flex');

        // Sembunyikan tombol Edit Profil
        this.classList.add('d-none');
    });

    $('#btnEditInstansi').click(function () {
        document.querySelectorAll('input:not([readonly]), textarea, button[title="Hapus File"], select').forEach(el => el.disabled = false);
        document.getElementById('btnSimpanInstansi').disabled = false;
        this.classList.add('d-none');
        $('#btnBackInstansi').show();
    });

    $('#btnCancelEdit').click(function () {
        window.location.href = window.location.pathname;
        location.reload();
    })

    $('#btnBackInstansi').click(function () {
        window.location.href = window.location.pathname + '#instansi';
        location.reload();
    })

    $('#btnTambahKopSurat').click(function () {
        openModalKopSurat('create');
        // $('#modal-kop-surat').modal('show');
    })

    detail = new Detail({
        jenis: 'history_pic',
        tab: {}
    });

    $('input[name="pelanggan_tipe"]').on('change', function () {
        $('#section-instansi-profile').slideDown();
        $('#action-pengajuan').slideDown();

        if ($(this).val() === 'baru') {
            $('#form-instansi-detail-profile').slideDown();
            $('.instansi-detail-input-profile').attr('required', true);

            // Tampilkan input text, sembunyikan select2
            $('#nama_instansi_lama_profile').removeAttr('name required').next('.select2-container').hide();
            if ($('#nama_instansi_lama_profile').parsley()) $('#nama_instansi_lama_profile').parsley().reset();
            $('#nama_instansi_baru_profile').attr({ 'name': 'nama_instansi', 'required': true }).show();
        } else {
            // Tampilkan select2, sembunyikan input text
            $('#nama_instansi_baru_profile').removeAttr('name required').hide();
            if ($('#nama_instansi_baru_profile').parsley()) $('#nama_instansi_baru_profile').parsley().reset();
            $('#nama_instansi_lama_profile').attr({ 'name': 'nama_instansi_lama', 'required': true }).show();
            $('#nama_instansi_lama_profile').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari Nama Instansi...',
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: `${base_url}/api/v1/profile/list/perusahaan`,
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
                                return { id: item.perusahaan_hash, text: item.nama_perusahaan };
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#form-instansi-detail-profile').slideUp();
            $('.instansi-detail-input-profile').removeAttr('required');
            $('.instansi-detail-input-profile').each(function () {
                if ($(this).parsley()) {
                    $(this).parsley().reset();
                }
            });
        }
    });
})

function showFormPengajuan() {
    $('#card-instansi-nonaktif').hide();
    $('#card-form-instansi').fadeIn();
}

function batalPengajuan() {
    $('#card-form-instansi').hide();
    $('#card-instansi-nonaktif').fadeIn();
    $('#form-pengajuan-instansi')[0].reset();
    $('#section-instansi-profile').hide();
    $('#action-pengajuan').hide();
    $('#form-instansi-detail-profile').hide();
    $('#form-pengajuan-instansi').parsley().reset();
}

function ajukanInstansi(obj) {
    let form = $('#form-pengajuan-instansi');
    form.parsley().validate();
    if (!form.parsley().isValid()) {
        return;
    }

    spinner('show', $(obj));

    const formParams = new FormData(form[0]);

    ajaxPost(`api/v1/profile/action/ajukan_instansi`, formParams, result => {
        spinner('hide', $(obj));
        if (result.data.status == 'success') {
            Swal.fire({
                icon: 'success',
                text: 'Pengajuan instansi berhasil dikirim',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            }).then(() => {
                window.location.href = window.location.pathname + '#instansi';
                window.location.reload();
            });
        }
    }, error => {
        spinner('hide', $(obj));
    });
}

function loadInstansi(data) {
    $('#card-instansi-aktif').hide();
    $('#card-instansi-nonaktif').hide();
    $('#card-kop-surat').hide();
    $('#card-detail-lokasi').hide();

    let htmlAlamat = '';
    if (data.perusahaan) {
        if (data.perusahaan.kode_perusahaan) {
            $('#kode_instansi').removeClass('text-danger border-danger');
            $('#kode_instansi').addClass('text-success border-success');
        } else {
            $('#kode_instansi').removeClass('text-success border-success');
            $('#kode_instansi').addClass('text-danger border-danger');
        }

        $('#npwp').val(data.perusahaan?.npwp_perusahaan ? data.perusahaan.npwp_perusahaan : '-');
        $('#kode_instansi').html(data.perusahaan ? (data.perusahaan.kode_perusahaan ?? 'Belum terverifikasi') : '-');
        $('#email').val(data.perusahaan?.email ? data.perusahaan.email : '-');
        $('#nama_perusahaan').val(data.perusahaan?.nama_perusahaan ? data.perusahaan.nama_perusahaan : '-');

        $('#card-instansi-aktif').show();
        $('#card-kop-surat').show();
        $('#card-detail-lokasi').show();

        // alamat
        if (data.perusahaan.alamat && data.perusahaan.alamat.length > 0) {
            for (const alamat of data.perusahaan.alamat) {
                let jenis = '';
                let checkbox = `
                    <div class="form-check form-switch">
                        <input class="form-check-input" onclick="changeAlamat(this)" data-jenis="${alamat.jenis}" type="checkbox" role="switch" id="switch-alamat-${alamat.jenis}" ${alamat.status == 1 ? 'checked' : ''}>
                    </div>
                `;
                switch (alamat.jenis) {
                    case 'utama':
                        jenis = 'Utama';
                        checkbox = '';
                        break;
                    case 'tld':
                        jenis = 'TLD';
                        break;
                    case 'lhu':
                        jenis = 'LHU';
                        break;
                    case 'invoice':
                        jenis = 'Invoice';
                        break;
                }

                htmlAlamat += `
                    <div class="mb-3" data-idalamat="${alamat.alamat_hash}">
                        <div class="d-flex" id="divLabel-${alamat.jenis}">
                            <label class="form-label me-3">Alamat ${jenis}</label>
                            ${statusUser == 1 ? checkbox : ''}
                        </div>
                        <div id="alamat-${alamat.jenis}-inactive" class="${alamat.status == 1 ? 'd-none' : 'd-block'}">
                            <p>Alamat sesuai dengan Alamat Utama</p>
                        </div>
                        <div id="alamat-${alamat.jenis}-active" class="d-flex align-items-center ${alamat.status == 1 ? 'd-block' : 'd-none'}">
                            <div class="flex-fill me-2" id="formAlamat-${alamat.jenis}">
                                <textarea name="txt-alamat-${alamat.jenis}" data-field="alamat" id="txt-alamat-${alamat.jenis}" cols="30" rows="3" class="form-control mb-2" disabled>${alamat.alamat ?? ''}</textarea>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control" data-field="kota" placeholder="Kota" id="txt-kota-${alamat.jenis}" value="${alamat.kota ?? ''}" disabled>
                                    <input type="text" class="form-control" data-field="kode_pos" placeholder="Kode pos" id="txt-kode-pos-${alamat.jenis}" value="${alamat.kode_pos ?? ''}" disabled>
                                </div>
                            </div>
                            <div id="btnEditDiv-${alamat.jenis}" class="d-block ${statusUser == 1 ? 'd-block' : 'd-none'}" data-field="${alamat.jenis}">
                                <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this, 'alamat')"><i class="bi bi-pencil"></i></button>
                            </div>
                            <div id="btnActionDiv-${alamat.jenis}" class="d-none d-flex" data-field="${alamat.jenis}">
                                <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this, 'alamat')"><i class="bi bi-x"></i></button>
                                <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this, 'alamat')" data-idalamat="${alamat.alamat_hash}"><i class="bi bi-check"></i></button>
                            </div>
                        </div>
                    </div>
                `;
            }
        } else {
            // Form jika alamat tidak ada
            htmlAlamat += `
                <div class="alert alert-info border-start border-4 border-info shadow-sm mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-4 text-info me-3"></i>
                        <div>
                            <small class="d-block fw-bold text-dark">Data Alamat Belum Lengkap</small>
                            <small class="text-muted">Silakan lengkapi data 4 alamat (Utama, TLD, LHU, dan Invoice) di bawah ini untuk melanjutkan.</small>
                        </div>
                    </div>
                </div>
                <form id="form-tambah-semua-alamat" novalidate>
            `;

            const arrJenis = [
                { id: 'utama', label: 'Utama' },
                { id: 'tld', label: 'TLD' },
                { id: 'lhu', label: 'LHU' },
                { id: 'invoice', label: 'Invoice' }
            ];

            arrJenis.forEach((j) => {
                let isUtama = j.id === 'utama';
                let checkbox = '';
                if (!isUtama) {
                    checkbox = `
                        <div class="form-check form-switch">
                            <input class="form-check-input switch-sama-utama" data-target="${j.id}" type="checkbox" role="switch" id="switch-sama-${j.id}">
                        </div>
                    `;
                }

                htmlAlamat += `
                    <div class="mb-3">
                        <div class="d-flex">
                            <label class="form-label me-3">Alamat ${j.label}</label>
                            ${checkbox}
                        </div>
                        <div id="body-alamat-${j.id}-inactive" class="${!isUtama ? 'd-block' : 'd-none'}">
                            <p>Alamat sesuai dengan Alamat Utama</p>
                        </div>
                        <div id="body-alamat-${j.id}-active" class="d-flex align-items-center ${!isUtama ? 'd-none' : 'd-block'}">
                            <div class="flex-fill">
                                <textarea name="alamat_${j.id}" id="alamat_${j.id}" cols="30" rows="3" class="form-control mb-2" placeholder="Alamat Lengkap" ${isUtama ? 'required data-parsley-required-message="Alamat harus diisi"' : ''}></textarea>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control" name="kota_${j.id}" id="kota_${j.id}" placeholder="Kota" ${isUtama ? 'required data-parsley-required-message="Kota harus diisi"' : ''}>
                                    <input type="text" class="form-control maskNumber" name="kode_pos_${j.id}" id="kode_pos_${j.id}" placeholder="Kode pos" ${isUtama ? 'required data-parsley-required-message="Kode pos harus diisi"' : ''}>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            htmlAlamat += `
                    <div class="text-end mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="simpanSemuaAlamat(this)">
                            <i class="bi bi-save me-1"></i> Simpan Alamat
                        </button>
                    </div>
                </form>
            `;
        }
    } else {
        $('#card-instansi-nonaktif').show();

        // Cek dummy status untuk membedakan antara ditolak dan belum diverifikasi
        // Anda bisa mengganti nilai data.dummy_status di respons API menjadi 'ditolak' untuk melakukan pengetesan
        let statusVerifikasi = 'belum_verifikasi';
        if (data.request_verify_instansi) {
            statusVerifikasi = data.request_verify_instansi.status == 1 ? 'pending' : 'ditolak';
        }

        let logs = data.request_verify_instansi?.logs;
        if (statusVerifikasi === 'ditolak') {
            $('#icon-status-instansi').html('<i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>');
            $('#title-status-instansi').html('Verifikasi Ditolak');
            $('#title-status-instansi').removeClass('text-dark').addClass('text-danger');
            $('#desc-status-instansi').html(`
                Mohon maaf, pengajuan instansi <b>${data.request_verify_instansi?.perusahaan?.nama_perusahaan}</b> telah ditolak. Silakan periksa kembali data Anda.
                <div class="mt-2">
                    <b>Alasan:</b> - ${logs[0].properties.catatan}
                </div>
            `);
            $('#action-status-instansi').html(`
                <button class="btn btn-outline-danger px-4 rounded-pill mt-2" onclick="showFormPengajuan()">
                    <i class="bi bi-arrow-repeat me-2"></i> Ajukan Ulang Instansi
                </button>
            `);
        } else if (statusVerifikasi === 'pending') {
            $('#icon-status-instansi').html('<i class="bi bi-hourglass-split text-warning" style="font-size: 4rem;"></i>');
            $('#title-status-instansi').html('Proses Verifikasi');
            $('#title-status-instansi').removeClass('text-danger').addClass('text-dark');
            $('#desc-status-instansi').html('Data instansi Anda sedang dalam proses verifikasi oleh tim kami. Silakan tunggu beberapa saat.');
            $('#action-status-instansi').html('');
        } else {
            $('#icon-status-instansi').html('<i class="bi bi-building-exclamation text-info" style="font-size: 4rem;"></i>');
            $('#title-status-instansi').html('Instansi Belum Tersedia');
            $('#title-status-instansi').removeClass('text-danger').addClass('text-dark');
            $('#desc-status-instansi').html('Anda belum memiliki atau belum terhubung dengan instansi manapun. Silakan tambahkan instansi Anda.');
            $('#action-status-instansi').html(`
                <button class="btn btn-primary px-4 rounded-pill mt-2" onclick="showFormPengajuan()">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Instansi
                </button>
            `);
        }
    }

    $('#list-alamat').html(htmlAlamat);
}

function loadForm(data) {
    // Menetapkan default value (pastikan ID ada dalam opsi yang dimuat)
    if (!_uploadSuratKuasa) {
        _uploadSuratKuasa = new UploadComponent("uploadSuratKuasa", {
            allowedFileExtensions: ['pdf'],
            camera: false,
            multiple: false,
            data: data.profile?.suratkuasa ? [data.profile?.suratkuasa] : [],
            urlUpload: {
                url: 'api/v1/profile/uploadSuratKuasa',
                urlDestroy: 'api/v1/profile/destroySuratKuasa',
                idHash: data.user_hash
            },
            template: {
                url: `${base_url}/assets/template/draft_surat_kuasa_rekanan.docx`,
                name: 'Template Surat Kuasa'
            }
        });
    }


    $('#nik_pic').val(data.profile.nik ? data.profile.nik : '-');
    $('#nama_pic').val(data.name ? data.name : '-');
    $('#jabatan_pic').val(data.jabatan ? data.jabatan : '-');
    $('#email_pic').val(data.email ? data.email : '-');
    $('#telepon').val(data.profile.no_hp ? data.profile.no_hp : '-');
    $('#jenis_kelamin').val(data.profile.jenis_kelamin);
    $('#alamat_pic').html(data.profile.alamat ? data.profile.alamat : '-');
    isPassword ? $('#form-old-password').removeClass('d-none') : $('#form-old-password').addClass('d-none');

    document.getElementById('show-ttd').innerHTML = '';
    signaturePad = signature(document.getElementById('show-ttd'), {
        width: 442,
        height: 298
    });

    if (data.ttd || data.ttd_image) {
        document.getElementById('ttd-preview').innerHTML = '';
        signature(document.getElementById('ttd-preview'), {
            width: '100%',
            height: '100%',
            persentage: true,
            defaultSig: data.ttd_image ? data.ttd_image : data.ttd
        });

        $('#show-ttd-preview').removeClass('d-none');
        $('#empty-ttd-preview').addClass('d-none');
    } else {
        $('#show-ttd-preview').addClass('d-none');
        $('#empty-ttd-preview').removeClass('d-none');
    }
}

function changeAlamat(obj) {
    let check = $(obj).is(":checked");
    let idAlamat = $(obj).parent().parent().parent().data('idalamat');
    let jenis = $(obj).data('jenis');
    const formParams = new FormData();

    if (check) {
        formParams.append('status', 1);

        $(`#alamat-${jenis}-inactive`).addClass('d-none').removeClass('d-block');
        $(`#alamat-${jenis}-active`).addClass('d-block').removeClass('d-none');
    } else {
        formParams.append('status', 0);

        $(`#alamat-${jenis}-inactive`).addClass('d-block').removeClass('d-none');
        $(`#alamat-${jenis}-active`).addClass('d-none').removeClass('d-block');
    }

    saveUpdateForm($(obj).parent(), formParams, idAlamat);
}

function saveUpdateForm(obj, params, id) {
    spinner('show', obj, {
        place: 'after'
    });

    params.append('_token', csrf);
    params.append('idAlamat', id);
    ajaxPost(`api/v1/profile/action/alamat`, params, result => {
        spinner('hide', obj);
    }, error => {
        spinner('hide', obj);
    })
}

function enableEdit(obj, tab) {
    const inputId = $(obj).parent().data('field');

    // change button to action
    $(`#btnEditDiv-${inputId}`).addClass('d-none').removeClass('d-block');
    $(`#btnActionDiv-${inputId}`).addClass('d-block').removeClass('d-none');

    // change form to canedit
    if (tab == 'alamat') {
        $(`#formAlamat-${inputId}`).find('input, textarea').each(function () {
            $(this).attr('disabled', false);
            $(this).data('tmpvalue', this.value);
        });
    } else {
        $(`#${inputId}`).attr('disabled', false);
        $(`#${inputId}`).focus();
        $(`#btnActionDiv-${inputId}`).data('tmpvalue', $(`#${inputId}`).val());
    }
}

function batalEdit(obj, tab) {
    const inputId = $(obj).parent().data('field');

    // change button to Edit
    $(`#btnEditDiv-${inputId}`).addClass('d-block').removeClass('d-none');
    $(`#btnActionDiv-${inputId}`).addClass('d-none').removeClass('d-block');

    // change form to canedit
    if (tab == 'alamat') {
        $(`#formAlamat-${inputId}`).find('input, textarea').each(function () {
            $(this).attr('disabled', true);
            $(this).val($(this).data('tmpvalue'));
        });
    } else {
        $(`#${inputId}`).attr('disabled', true);
        $(`#${inputId}`).val($(`#btnActionDiv-${inputId}`).data('tmpvalue'));
        $(`#${inputId}`).parsley().reset();
        $(`#${inputId}`).removeClass('is-valid is-invalid');
        $(`#message-${inputId}`).html('');
    }
}

function simpanEdit(obj, tab) {
    const inputId = $(obj).parent().data('field');

    // save edit
    let spinObj = false;
    if (tab == 'alamat') {
        spinObj = $(`#divLabel-${inputId}`);
        let idAlamat = $(obj).data('idalamat');
        const formParams = new FormData();
        $(`#formAlamat-${inputId}`).find('input, textarea').each(function () {
            let field = $(this).data('field');
            formParams.append(field, this.value);
        });

        saveUpdateForm(spinObj, formParams, idAlamat);
        $(`#formAlamat-${inputId}`).find('input, textarea').each(function () {
            $(this).attr('disabled', true);
        });
        // change button to Edit
        $(`#btnEditDiv-${inputId}`).addClass('d-block').removeClass('d-none');
        $(`#btnActionDiv-${inputId}`).addClass('d-none').removeClass('d-block');
    } else {
        let inputParsley = $(`#${inputId}`).parsley();
        if (inputParsley) {
            inputParsley.validate();
            if (!inputParsley.isValid()) {
                return;
            }
        }
        if (inputId == 'nik_pic') { }

        spinObj = $(obj).parent().parent().parent().children('label');
        const value = $(`#${inputId}`).val();

        const formParams = new FormData();
        formParams.append(inputId, value);

        spinner('show', $(spinObj), {
            place: 'after'
        });

        if (tab == 'instansi') {
            formParams.append('idPerusahaan', profile.perusahaan?.perusahaan_hash);
            ajaxPost(`api/v1/profile/action/perusahaan`, formParams, result => {
                spinner('hide', $(spinObj));
                // change form to canedit
                $(`#${inputId}`).attr('disabled', true);
                // change button to Edit
                $(`#btnEditDiv-${inputId}`).addClass('d-block').removeClass('d-none');
                $(`#btnActionDiv-${inputId}`).addClass('d-none').removeClass('d-block');
                $(`#${inputId}`).removeClass('is-valid is-invalid');
            }, error => {
                spinner('hide', $(spinObj));
            })
        } else {
            formParams.append('idProfile', profile.user_hash);

            ajaxPost(`api/v1/profile/action`, formParams, result => {
                if (result.meta.message == 'Fail') {
                    spinner('hide', $(spinObj));
                    Swal.fire({
                        icon: 'warning',
                        text: result.data.msg
                    });
                    return;
                }

                profile = result.data;
                loadForm(profile);
                spinner('hide', $(spinObj));
                // change form to canedit
                $(`#${inputId}`).attr('disabled', true);
                // change button to Edit
                $(`#btnEditDiv-${inputId}`).addClass('d-block').removeClass('d-none');
                $(`#btnActionDiv-${inputId}`).addClass('d-none').removeClass('d-block');
                $(`#${inputId}`).removeClass('is-valid is-invalid');
            }, error => {
                spinner('hide', $(spinObj));
            })
        }

    }
}

function simpanPerubahanInstansi(obj) {
    const formInstansi = $('#form-instansi');
    spinner('show', $(obj));

    formInstansi.parsley().validate();
    if (!formInstansi.parsley().isValid()) {
        return;
    }

    const formParams = new FormData();
    for (const element of formInstansi[0]) {
        let field = $(element).attr('name');
        formParams.append(field, element.value);
    }
    formParams.append('idPerusahaan', profile.perusahaan?.perusahaan_hash);

    ajaxPost(`api/v1/profile/action/perusahaan`, formParams, result => {
        if (result.meta.message == 'Fail') {
            spinner('hide', $(obj));
            Swal.fire({
                icon: 'warning',
                text: result.data.msg
            });
            return;
        }

        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            window.location.href = window.location.pathname + '#instansi';
            location.reload();
        })
    }, error => {
        spinner('hide', $(obj));
    });
}
function simpanPerubahanBiodata(obj) {
    const formBiodata = $('#form-biodata');
    spinner('show', $(obj));

    formBiodata.parsley().validate();
    if (!formBiodata.parsley().isValid()) {
        return;
    }

    const formParams = new FormData();
    for (const element of formBiodata[0]) {
        let field = $(element).attr('name');
        formParams.append(field, element.value);
    }
    formParams.append('idProfile', profile.user_hash);

    ajaxPost(`api/v1/profile/action`, formParams, result => {
        if (result.meta.message == 'Fail') {
            spinner('hide', $(obj));
            Swal.fire({
                icon: 'warning',
                text: result.data.msg
            });
            return;
        }

        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            location.reload();
        })
    }, error => {
        spinner('hide', $(obj));
    })
}

function gantiPassword(obj) {
    const oldPassword = $('#old_password').val();
    const newPassword = $('#new_password').val();
    const confirmPassword = $('#confirm_password').val();

    $('#form-change-password').parsley().validate();
    if (!$('#form-change-password').parsley().isValid()) {
        return;
    }
    const formParams = new FormData();
    formParams.append('old_password', oldPassword);
    formParams.append('new_password', newPassword);

    if (newPassword == confirmPassword) {
        formParams.append('idProfile', profile.user_hash);
        spinner('show', $(obj));
        ajaxPost(`api/v1/profile/changePassword`, formParams, result => {
            if (result.data.status != 'fail') {
                Swal.fire({
                    icon: "success",
                    text: result.data.msg,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Tambahkan hash ke URL dan refresh halaman
                    window.location.href = window.location.pathname + '#changepassword';
                    location.reload();
                });
            } else {
                $('#old_password').addClass('is-invalid');
                $('#error-old-password').html(result.data.msg);
                $('#error-old-password').removeClass('d-none');
                spinner('hide', $(obj));
            }
        }, error => {
            spinner('hide', $(obj));
        })
    }
}

function openModalHistoryPic() {
    detail.show(`api/v1/profile/getHistoryPic/${profile.perusahaan?.perusahaan_hash}`);
}

function loadDocumentKop(page = 1) {
    // Loading data
    $('#tbody-kop-surat').html(`
        <tr>
            <td colspan="2" class="text-center">Loading...</td>
        </tr>
    `);
    ajaxGet('management/document/load', {
        page: page,
        limit: 5,
        jenis: 'header',
        idPerusahaan: profile.perusahaan?.perusahaan_hash
    }, result => {
        let html = createTabel(result.data, 'header');

        if (result.data.length == 0) {
            html = `
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data</td>
                </tr>
            `;
        }
        $('#tbody-kop-surat').html(html);

        $('#pagination-kop-surat').html(createPaginationHTML(result.pagination));
    }, err => {
        console.error(err);
    });
}

$('#pagination-kop-surat').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;

    loadDocumentKop(pageno);
});
function createTabel(data) {
    let html = '';
    data.map((item, index) => {
        let isActive = item.view;
        html += `
            <tr>
                <td class="d-flex align-items-center gap-2">${index + 1}. ${item.name} ${isActive == 1 ? '<span class="badge bg-success">Aktif</span>' : ''}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-info" data-id="${item.doc_hash}" data-title="${item.name}" onclick="previewKopSurat(this)"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-sm btn-warning" onclick="openModalKopSurat('edit','${item.doc_hash}')"><i class="bi bi-pencil-square"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteKopSurat('${item.doc_hash}')"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;
    });
    return html;
}

function deleteKopSurat(hash) {
    ajaxDelete(`management/document/${hash}`, () => {
        Swal.fire({
            icon: 'success',
            text: 'Data berhasil dihapus',
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            loadDocumentKop();
        })
    }, err => {
        console.error(err);
    });
}

function previewKopSurat(obj) {
    const id = $(obj).data('id');
    const title = $(obj).data('title') || 'Dokumen';
    modalDoc.show(`laporan/template_default/kop_surat/${id}`, {
        title: title
    });
}

$(document).on('change', '.switch-sama-utama', function() {
    let target = $(this).data('target');
    if ($(this).is(':checked')) {
        $(`#body-alamat-${target}-active`).removeClass('d-none').addClass('d-block');
        $(`#body-alamat-${target}-inactive`).removeClass('d-block').addClass('d-none');
        $(`#kota_${target}, #kode_pos_${target}, #alamat_${target}`).attr('required', true);
    } else {
        $(`#body-alamat-${target}-active`).removeClass('d-block').addClass('d-none');
        $(`#body-alamat-${target}-inactive`).removeClass('d-none').addClass('d-block');
        $(`#kota_${target}, #kode_pos_${target}, #alamat_${target}`).removeAttr('required');
        
        let parsleyKota = $(`#kota_${target}`).parsley();
        let parsleyKodePos = $(`#kode_pos_${target}`).parsley();
        let parsleyAlamat = $(`#alamat_${target}`).parsley();
        if(parsleyKota) parsleyKota.reset();
        if(parsleyKodePos) parsleyKodePos.reset();
        if(parsleyAlamat) parsleyAlamat.reset();
    }
});

function simpanSemuaAlamat(btn) {
    let form = $('#form-tambah-semua-alamat');
    form.parsley().validate();
    if (!form.parsley().isValid()) return;

    spinner('show', $(btn));
    let params = new FormData(form[0]);
    params.append('idPerusahaan', profile.perusahaan?.perusahaan_hash);

    const arrJenis = ['tld', 'lhu', 'invoice'];
    arrJenis.forEach(j => {
        if ($(`#switch-sama-${j}`).is(':checked')) {
            params.append(`status_${j}`, 1); 
        } else {
            params.append(`status_${j}`, 0);
        }
    });

    ajaxPost('api/v1/profile/action/tambah_semua_alamat', params, result => {
        spinner('hide', $(btn));
        if(result.meta.code == 200) {
            Swal.fire({
                icon: 'success',
                text: 'Data alamat berhasil disimpan',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        }
    }, err => {
        spinner('hide', $(btn));
    });
}
