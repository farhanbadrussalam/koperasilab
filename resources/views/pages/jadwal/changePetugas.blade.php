<div class="modal fade" id="changePetugas">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h4 class="modal-title">Ganti petugas</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12 mb-2">
                    <label for="selectChangePetugas" class="form-label">Petugas <span class="fw-bold fs-14 text-danger">*</span></label>
                    <div class="input-group mb-3">
                        <input type="hidden" name="idJadwalPetugas" id="idJadwalPetugas">
                        <select name="petugas" id="selectChangePetugas" class="form-control">
                            <option value="">--- Select ---</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" onclick="updatePetugas()">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
