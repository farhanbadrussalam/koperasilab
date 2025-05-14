<div class="modal fade" id="modal-add-pengguna" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambahkan pengguna</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body g-2 row">
                <div>
                    <label for="nama_pengguna" class="col-form-label">Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" id="nama_pengguna" class="form-control">
                </div>
                <div>
                    <label for="divisi_pengguna" class="col-form-label">Divisi Pengguna</label>
                    <select name="divisi_pengguna" id="divisi_pengguna" class="form-select">
                        <option value=""></option>
                        @foreach ($divisi as $value)
                            <option value="{{ $value->divisi_hash }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jenis_radiasi" class="col-form-label">Jenis/Energi Radiasi</label>
                    <select name="jenis_radiasi" id="jenis_radiasi" class="form-select" multiple="multiple">
                        @foreach ($radiasi as $value)
                            <option value="{{ $value->radiasi_hash }}">{{ $value->nama_radiasi }}</option>
                        @endforeach
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