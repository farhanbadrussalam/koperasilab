<div class="modal fade" id="addPetugas">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h4 class="modal-title">Tambah petugas</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12 mb-2">
                    <label for="selectPetugas" class="form-label">Petugas <span class="fw-bold fs-14 text-danger">*</span></label>
                    <div class="input-group mb-3">
                        <select name="petugas" id="selectPetugas" class="form-control">
                            <option value="">--- Select ---</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" onclick="storePetugas()">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
