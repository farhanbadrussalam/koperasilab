<div class="modal fade" id="add-modal-pic" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambahkan PIC</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

                <div class="modal-body">
                    <form id="form-pic" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="nik" class="form-label">NIK <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" class="form-control maskNIK" id="input-nik-pic" name="nik" placeholder="" autocomplete="true"
                                    required
                                    minlength="16"
                                    data-parsley-minlength-message="NIK minimal 16 karakter"
                                    data-parsley-required-message="NIK harus diisi"
                                    data-parsley-errors-container="#nik_error">
                                <div id="nik_error" class="invalid-feedback d-block"></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="email" class="form-label">Email <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="email" class="form-control maskEmail" id="input-email-pic" name="email" autocomplete="true" required
                                    data-parsley-required-message="Email harus diisi"
                                    data-parsley-errors-container="#email_error">
                                <div id="email_error" class="invalid-feedback d-block"></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="nama_pic" class="form-label">Nama <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_pic-pic" name="nama_pic"
                                    required
                                    data-parsley-required-message="Nama harus diisi">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="jabatan_pic" class="form-label">Jabatan</label>
                                <input type="text" class="form-control" id="jabatan_pic-pic" name="jabatan_pic" placeholder="" value="">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="jenis_kelamin" id="jenis_kelamin-pic" class="form-select">
                                    <option value="laki-laki">Laki-laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="telepon" class="form-label">Telepon <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" class="form-control maskTelepon" id="telepon-pic" name="telepon"
                                    required
                                    data-parsley-required-message="Telepon harus diisi">
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat <span class="fw-bold fs-14 text-danger">*</span></label>
                                <textarea name="alamat" id="alamat-pic" cols="30" rows="5" class="form-control"
                                    required
                                    data-parsley-required-message="Alamat harus diisi"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="uploadSuratKuasa-pic" class="form-label">Surat Kuasa <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div id="uploadSuratKuasa-pic"></div>
                            </div>
                            @role('Pelanggan')
                            <div class="col-md-6 mb-2">
                                <label for="input-password-pic" class="form-label">Password <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div class="input-group mb-2 mt-1">
                                    <input type="password" class="form-control" id="input-password-pic" name="password" required
                                        data-parsley-required-message="Password harus diisi"
                                        data-parsley-trigger="input"
                                        data-parsley-minlength="8"
                                        data-parsley-lowercase="true"
                                        data-parsley-uppercase="true"
                                        data-parsley-errors-container="#password_error">
                                    <div class="input-group-text border-0 bg-body-secondary" onclick="showPassword(this)">
                                        <i class="bi bi-eye"></i>
                                    </div>
                                </div>
                                <div id="password_error" class="invalid-feedback d-none"></div>
                                <ul id="password-rules-pic" class="mt-2 small ps-0" style="list-style: none;"></ul>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="password_confirmation-pic" class="form-label">Konfirmasi Password <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div class="input-group mb-2 mt-1">
                                    <input type="password" class="form-control" id="password_confirmation-pic" name="password_confirmation" required
                                        data-parsley-trigger="input"
                                        data-parsley-equalto="#input-password-pic"
                                        data-parsley-equalto-message="Konfirmasi password tidak sama."
                                        data-parsley-required-message="Konfirmasi password wajib diisi."
                                        data-parsley-errors-container="#konfirm_password_error">
                                    <div class="input-group-text border-0 rounded-end bg-body-secondary" onclick="showPassword(this)">
                                        <i class="bi bi-eye"></i>
                                    </div>
                                </div>
                                <div id="konfirm_password_error" class="invalid-feedback d-block"></div>
                            </div>
                            @endrole
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="simpanPic(this)">Save</button>
                </div>
        </div>
    </div>
</div>
<script src="{{ asset('js/profile/modal_pic.js') }}"></script>
