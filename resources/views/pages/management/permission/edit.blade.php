<div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-warning-subtle text-warning rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark" id="editModalTitle">Edit Permission</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-edit" method="post" data-parsley-validate>
                @csrf
                @method('PUT')
                <div class="modal-body px-4 pt-3 pb-2">
                    <div class="mb-3">
                        <label for="inputEditNamePermission" class="form-label fw-semibold text-secondary">Name Permission <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="name" 
                               id="inputEditNamePermission" 
                               class="form-control rounded-3" 
                               placeholder="e.g. Management-read"
                               autocomplete="off" 
                               required
                               data-parsley-minlength="3"
                               data-parsley-maxlength="50"
                               data-parsley-trigger="input"
                               data-parsley-required-message="Nama hak akses harus diisi."
                               data-parsley-minlength-message="Nama minimal 3 karakter."
                               data-parsley-maxlength-message="Nama maksimal 50 karakter.">
                        <input type="text" name="id_permission" id="inputEditIdPermission" class="form-control d-none">
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill" id="btn-edit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
