<div class="modal fade" id="createTldModal" tabindex="-1" aria-labelledby="createTldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createTldModalLabel">Create TLD</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="form-create" method="post">
            <div class="modal-body row">
                @csrf
                <div class="mb-3 col-md-12">
                    <label for="inputNoSeri" class="form-label">Nomer Seri</label>
                    <input type="text" name="nomer_seri" id="inputNoSeri" class="form-control" autocomplete="false" required>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="inputJenisTld" class="form-label">Jenis</label>
                    <select name="jenis" id="inputJenisTld" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="kontrol">Kontrol</option>
                        <option value="pengguna">Pengguna</option>
                    </select>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="inputMerk" class="form-label">Merk</label>
                    <input type="text" name="merk" id="inputMerk" class="form-control" autocomplete="false" required>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="submit" class="btn btn-primary" id="btn-create">Save</button>
            </div>
        </form>
      </div>
    </div>
</div>