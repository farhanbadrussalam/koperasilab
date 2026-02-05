<div class="modal fade" id="modal-add-tld-pengguna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 d-block">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="modal-title fw-bold">Tambahkan Pengguna</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" id="btn-trigger-create-user">
                            <i class="bi bi-plus-lg me-1"></i> Buat Baru
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="position-relative mb-3">
                    <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="customSearch" class="form-control form-control-lg ps-5 bg-light border-0"
                        placeholder="Cari nama atau ID karyawan...">
                </div>
            </div>

            <div class="modal-body p-1">
                <div class="p-2">
                    <table id="table-user" class="table table-borderless w-100 align-middle">
                        <thead class="d-none">
                            <tr><th>User Data</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div id="emptyState" class="text-center py-5 d-none">
                        <i class="bi bi-person-x text-muted opacity-25" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">Pengguna tidak ditemukan.</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top-0 pt-2 justify-content-center">
                <div id="customPagination" class="w-100 d-flex justify-content-center"></div>
            </div>
        </div>
    </div>
</div>

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

<script src="{{ asset('js/component/TldPenggunaSelector.js') }}"></script>
<script src="{{ asset('js/component/PenggunaForm.js') }}"></script>
