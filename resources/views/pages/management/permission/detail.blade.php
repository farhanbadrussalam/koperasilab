<div class="modal fade" id="detailPermissionModal" tabindex="-1" aria-labelledby="detailPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="detailPermissionModalLabel">Detail Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3 pb-4">
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Nama Permission</label>
                    <div id="detailPermissionName" class="fw-bold fs-5 text-dark"></div>
                </div>
                
                <div class="mb-2">
                    <label class="form-label text-muted small mb-1">Digunakan Oleh Role:</label>
                    <div id="detailRolesList" class="d-flex flex-wrap gap-2">
                        <!-- Roles akan dirender di sini -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
