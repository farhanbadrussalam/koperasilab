<div class="modal fade" id="modal-add-tld-pengguna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 d-block">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="modal-title fw-bold">Tambahkan Pengguna</h5>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3"
                            id="btn-trigger-create-user">
                            <i class="bi bi-plus-lg me-1"></i> Buat Baru
                        </button>
                    </div>
                    <div class="d-flex gap-2">
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
                            <tr>
                                <th>User Data</th>
                            </tr>
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
                <button type="button" class="btn-close" id="btn-close-pengguna" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body g-2">
                <div id="loading-tambah-pengguna" class="text-center"></div>
                <form id="form-tambah-pengguna" class="row">
                    <div class="col-6">
                        <label for="nik_pengguna" class="col-form-label">NIK <span
                                class="text-danger ms-1">*</span></label>
                        <input type="text" name="nik_pengguna" id="nik_pengguna" class="form-control maskNIK"
                            data-parsley-required="true">
                    </div>
                    <div class="col-6">
                        <label for="nama_pengguna" class="col-form-label">Nama Pengguna <span
                                class="text-danger ms-1">*</span></label>
                        <input type="text" name="nama_pengguna" id="nama_pengguna" class="form-control"
                            data-parsley-required="true">
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
                        <label for="jenis_kelamin" class="col-form-label">Jenis Kelamin <span
                                class="text-danger ms-1">*</span></label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select"
                            data-parsley-required="true">
                            <option value=""></option>
                            <option value="laki-laki">Laki laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>

                    <!-- Multi-Divisi & Kode Lencana -->
                    <div class="col-12 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="col-form-label fw-bold">Divisi & Kode Lencana Pengguna <span
                                    class="text-danger">*</span></label>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill"
                                id="btn-add-divisi-row">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Divisi
                            </button>
                        </div>
                        <div id="container-divisi-rows" class="d-flex flex-column gap-2">
                            <!-- Rows divisi dimasukkan secara dinamis via PenggunaForm.js -->
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <label for="jenis_radiasi" class="col-form-label">Jenis/Energi Radiasi</label>
                        <select name="jenis_radiasi" id="jenis_radiasi" class="form-select"
                            multiple="multiple"></select>
                    </div>
                    <div>
                        <label for="upload_ktp" class="col-form-label">Upload KTP <span
                                class="text-danger ms-1">*</span></label>
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

<!-- Modal Pilihan Divisi & Kode Lencana Pengguna -->
<div class="modal fade" id="modal-select-divisi-user" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Pilih Divisi & Kode Lencana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-muted small mb-3">Tentukan Divisi dan Kode Lencana pengguna yang akan digunakan.</p>
                <form id="form-select-divisi-user">
                    <input type="hidden" id="select_divisi_user_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Pengguna</label>
                        <input type="text" id="select_divisi_user_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Divisi <span class="text-danger">*</span></label>
                        <select id="select_divisi_user_id_divisi" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kode Lencana <span class="text-danger">*</span></label>
                        <select id="select_divisi_user_kode_lencana" class="form-select"></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" id="btn-confirm-select-divisi-user">
                    <i class="bi bi-check-lg me-1"></i> Gunakan
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset_versioned('js/component/TldPenggunaSelector.js') }}"></script>
<script src="{{ asset_versioned('js/component/PenggunaForm.js') }}"></script>
