<div class="modal fade" id="createTldModal" tabindex="-1" aria-labelledby="createTldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createTldModalLabel">Create TLD</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="form-create" method="post">
            <div class="modal-body">
                @csrf
                <div class="mb-3">
                    <label for="inputKodeLencanaTld" class="form-label">Kode lencana TLD</label>
                    <input type="text" name="kode_lencana" id="inputKodeLencanaTld" class="form-control" autocomplete="false" required>
                </div>
                <div class="mb-3">
                    <label for="inputJenisTld" class="form-label">Jenis</label>
                    <select name="jenis" id="inputJenisTld" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="kontrol">Kontrol</option>
                        <option value="pengguna">Pengguna</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="submit" class="btn btn-primary" id="btn-create">Save</button>
            </div>
        </form>
      </div>
    </div>
</div>