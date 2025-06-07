<div class="modal fade" id="modal-add-pengguna" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambahkan pengguna</h1>
                <button type="button" class="btn-close" id="btn-close-pengguna" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body g-2 row">
                <div class="col-4">
                    <label for="nik_pengguna" class="col-form-label">NIK</label>
                    <input type="text" name="nik_pengguna" id="nik_pengguna" class="form-control maskNIK">
                </div>
                <div class="col-4">
                    <label for="kode_lencana" class="col-form-label">Kode Lencana</label>
                    <div class="input-group">
                        <input type="text" name="kode_lencana" id="kode_lencana" class="form-control maskNumber">
                        <div class="input-group-text">
                            <input type="checkbox" name="is_aktif" id="is_aktif" class="form-check-input mt-0">
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <label for="nama_pengguna" class="col-form-label">Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" id="nama_pengguna" class="form-control">
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
                    <label for="jenis_kelamin" class="col-form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
                        <option value=""></option>
                        <option value="laki-laki">Laki laki</option>
                        <option value="perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="divisi_pengguna" class="col-form-label">Divisi Pengguna</label>
                    <select name="divisi_pengguna" id="divisi_pengguna" class="form-select">
                        <option value=""></option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="jenis_radiasi" class="col-form-label">Jenis/Energi Radiasi</label>
                    <select name="jenis_radiasi" id="jenis_radiasi" class="form-select" multiple="multiple">
                        <option value=""></option>
                    </select>
                </div>
                <div>
                    <label for="upload_ktp" class="col-form-label">Upload KTP</label>
                    <div class="card mb-0" style="height: 150px;">
                        <input type="file" name="dokumen" id="uploadKtpPengguna" accept="image/*" class="form-control dropify">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-tambah-pengguna">Simpan</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // set Select2
        $('#jenis_radiasi').select2({
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

        $('#divisi_pengguna').select2({
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
    });
</script>
