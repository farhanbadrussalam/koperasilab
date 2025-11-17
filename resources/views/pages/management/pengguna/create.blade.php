<div class="modal fade" id="modal-add-pengguna" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambahkan pengguna</h1>
                <button type="button" class="btn-close" id="btn-close-pengguna" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body g-2">
                <div id="loading-tambah-pengguna" class="text-center"></div>
                <form id="form-tambah-pengguna" class="row">
                    <div class="col-4">
                        <label for="nik_pengguna" class="col-form-label">NIK <span class="text-danger ms-1">*</span></label>
                        <input type="text" name="nik_pengguna" id="nik_pengguna" class="form-control maskNIK" data-parsley-required="true">
                    </div>
                    <div class="col-4">
                        <label for="kode_lencana" class="col-form-label">Kode Lencana <span class="text-danger ms-1">*</span></label>
                        <div class="input-group">
                            <input type="text" name="kode_lencana" id="kode_lencana" class="form-control maskNumber" data-parsley-required="true">
                            <div class="input-group-text rounded-end">
                                <input type="checkbox" name="is_aktif" id="is_aktif" class="form-check-input mt-0">
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <label for="nama_pengguna" class="col-form-label">Nama Pengguna <span class="text-danger ms-1">*</span></label>
                        <input type="text" name="nama_pengguna" id="nama_pengguna" class="form-control" data-parsley-required="true">
                    </div>
                    <div class="col-4">
                        <label for="tanggal_lahir" class="col-form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control">
                    </div>
                    <div class="col-4">
                        <label for="tempat_lahir" class="col-form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control">
                    </div>
                    <div class="col-4">
                        <label for="jenis_kelamin" class="col-form-label">Jenis Kelamin <span class="text-danger ms-1">*</span></label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" data-parsley-required="true">
                            <option value=""></option>
                            <option value="laki-laki">Laki laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="divisi_pengguna" class="col-form-label">Divisi Pengguna</label>
                        <select name="divisi_pengguna" id="divisi_pengguna" class="form-select"></select>
                    </div>
                    <div class="col-12">
                        <label for="jenis_radiasi" class="col-form-label">Jenis/Energi Radiasi</label>
                        <select name="jenis_radiasi" id="jenis_radiasi" class="form-select" multiple="multiple"></select>
                    </div>
                    <div>
                        <label for="upload_ktp" class="col-form-label">Upload KTP <span class="text-danger ms-1">*</span></label>
                        <div id="uploadKtpPengguna"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-tambah-pengguna">Simpan</button>
            </div>
        </div>
    </div>
</div>
<script>
    let formValidate = false;
    let jenisRadiasi = false;
    let divisiPengguna = false;
    let ktpPenggunaUpload = false;
    let selectData = false;
    $(function () {
        formValidate = new FormValidation('form-tambah-pengguna');

        // set upload image
        ktpPenggunaUpload = new UploadComponent('uploadKtpPengguna', {
            allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg'],
            camera: false,
            multiple: false,
            preview: {
                fullwidth: true,
                height: 300
            }
        })

        $('#tanggal_lahir').flatpickr({
            enableTime: false,
            dateFormat: "Y-m-d"
        });

        // set Select2
        jenisRadiasi = $('#jenis_radiasi').select2({
            theme: "bootstrap-5",
            tags: true,
            placeholder: "Pilih Jenis Radiasi",
            dropdownParent: $('#modal-add-pengguna'),
            createTag: (params) => {
                return {
                    id: params.term,
                    text: params.term,
                    newTag: true
                };
            },
            ajax: {
                url: `${base_url}/api/v1/pengguna/getRadiasi`,
                dataType: 'json',
                delay: 250,
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${bearer}`,
                    'Content-Type': 'application/json'
                },
                data: params => {
                    return {
                        name_radiasi: params.term
                    }
                },
                processResults: (data) => {
                    return {
                        results: $.map(data.data, function (item) {
                            return {
                                text: item.nama_radiasi,
                                id: item.radiasi_hash
                            }
                        })
                    };
                }
            }
        });

        divisiPengguna = $('#divisi_pengguna').select2({
            theme: "bootstrap-5",
            tags: true,
            placeholder: "Pilih Divisi",
            dropdownParent: $('#modal-add-pengguna'),
            createTag: (params) => {
                return {
                    id: params.term,
                    text: params.term,
                    newTag: true
                };
            },
            ajax: {
                url: `${base_url}/api/v1/pengguna/getDivisi`,
                dataType: 'json',
                delay: 250,
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${bearer}`,
                    'Content-Type': 'application/json'
                },
                data: params => {
                    return {
                        name_divisi: params.term
                    }
                },
                processResults: (data) => {
                    return {
                        results: $.map(data.data, function (item) {
                            return {
                                text: item.name,
                                id: item.divisi_hash
                            }
                        })
                    };
                }
            }
        });

        $('#is_aktif').on('change', obj => {
            if ($(obj.target).is(':checked')) {
                $('#kode_lencana').val('');
                $('#kode_lencana').attr('readonly', true);
                $('#kode_lencana').attr('placeholder', 'Auto Generate');
                $('#kode_lencana').addClass('bg-secondary-subtle');
                $('#kode_lencana').attr('data-parsley-required', 'false');
            } else {
                $('#kode_lencana').attr('readonly', false);
                $('#kode_lencana').attr('placeholder', '');
                $('#kode_lencana').removeClass('bg-secondary-subtle');
                $('#kode_lencana').attr('data-parsley-required', 'true');
            }
        });

        $('#modal-add-pengguna').on('hidden.bs.modal', event => {
            formValidate.reset();
            $('#nik_pengguna').val('');
            $('#nama_pengguna').val('');
            $('#jenis_radiasi').empty();
            $('#jenis_radiasi').val(null).trigger('change');
            $('#divisi_pengguna').val(null).trigger('change');
            $('#jenis_kelamin').val('');
            $('#tanggal_lahir').val('');
            $('#tempat_lahir').val('');
            $('#is_aktif').show();
            $('#is_aktif').prop('checked', false);
            $('#kode_lencana').attr('readonly', false);
            $('#kode_lencana').attr('placeholder', '');
            $('#kode_lencana').removeClass('bg-secondary-subtle');
            $('#kode_lencana').val('');
            ktpPenggunaUpload.addData([]);
        });

        $('#btn-tambah-pengguna').on('click', obj => {
            spinner('show', obj.target);
            if(!formValidate.validate()){
                return spinner('hide', obj.target);
            }
            const imageKtp = ktpPenggunaUpload.getData();

            // cek dropify ada gambar
            if(imageKtp.length === 0){
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Data berikut masih kosong: KTP Pengguna'
                })
                return spinner('hide', obj.target);
            }

            const namaPengguna = $('#nama_pengguna').val();
            const divisiPengguna = $('#divisi_pengguna').val();
            const jenisRadiasi = $('#jenis_radiasi').val();
            // const imageKtp = $('#uploadKtpPengguna')[0].files[0];
            const nikPengguna = $('#nik_pengguna').val();
            const jenisKelamin = $('#jenis_kelamin').val();
            const tanggalLahir = $('#tanggal_lahir').val();
            const tempatLahir = $('#tempat_lahir').val();
            const kodeLencana = $('#kode_lencana').val();
            const isAktif = $('#is_aktif').is(':checked') ? 1 : 0;

            const formData = new FormData();
            formData.append('nik', nikPengguna);
            formData.append('kode_lencana', kodeLencana);
            formData.append('is_aktif', isAktif);
            formData.append('jenis_kelamin', jenisKelamin);
            formData.append('tanggal_lahir', tanggalLahir);
            formData.append('tempat_lahir', tempatLahir);
            formData.append('ktp', imageKtp[0].file);
            formData.append('name', namaPengguna);
            divisiPengguna != 'null' ?? formData.append('divisi', divisiPengguna);
            formData.append('radiasi', JSON.stringify(jenisRadiasi));

            if(selectData){
                formData.append('id', selectData.pengguna_hash);
            }

            ajaxPost(`api/v1/pengguna/action`, formData, result => {
                if (result.meta.code == 200) {
                    Swal.fire({
                        icon: "success",
                        text: result.data.msg,
                    });
                    reload();
                    spinner('hide', obj.target);
                    $('#modal-add-pengguna').modal('hide');
                } else {
                    Swal.fire({
                        icon: "error",
                        text: result.data.msg,
                    });
                }
            }, error => {
                spinner('hide', obj.target);
            })
        });
    });

    function tambahPengguna() {
        $('#modal-add-pengguna').modal('show');
    }

    function editPengguna(obj) {
        let id = $(obj).data('id');

        spinner('show', $("#loading-tambah-pengguna"), {
            height: "100px",
            width: "100px"
        });

        $('#form-tambah-pengguna').hide();

        $('#modal-add-pengguna').modal('show');
        ajaxGet(`api/v1/pengguna/getDataById/${id}`, false, result => {
            let data = result.data;

            selectData = data;
            $('#nik_pengguna').val(data.nik);
            $('#nama_pengguna').val(data.name);

            // set radiasi
            data.radiasi.forEach(element => {
                jenisRadiasi.append(new Option(element.nama_radiasi, element.radiasi_hash, true, true));
            });
            jenisRadiasi.trigger('change');

            // set divisi
            if(data.divisi) {
                divisiPengguna.append(new Option(data.divisi.name, data.divisi.divisi_hash, true, true)).trigger('change');
            }

            // set image ktp
            if(data.media_ktp) {
                ktpPenggunaUpload.setData(data.media_ktp);
            }

            $('#jenis_kelamin').val(data.jenis_kelamin);
            $('#tanggal_lahir').val(data.tanggal_lahir);
            $('#tempat_lahir').val(data.tempat_lahir);
            $('#kode_lencana').val(data.kode_lencana);
            $('#kode_lencana').attr('readonly', true);
            $('#is_aktif').hide();

            spinner('hide', $("#loading-tambah-pengguna"));
            $('#form-tambah-pengguna').show();
        })
    }
</script>
