<div class="modal fade" id="editTldModal" tabindex="-1" aria-labelledby="editTldModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit TLD</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="form-edit" method="post">
            @csrf
            @method('PUT')
            <input type="text" name="id_tld" id="inputIdTldEdit" class="d-none">
            <div class="modal-body row">
                <div class="mb-3 col-md-12">
                    <label for="inputNoSeriEdit" class="form-label">Nomer Seri</label>
                    <input type="text" name="nomer_seri" id="inputNoSeriEdit" class="form-control" autocomplete="false" required>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="inputJenisTld" class="form-label">Jenis</label>
                    <select name="jenis" id="inputJenisTldEdit" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="kontrol">Kontrol</option>
                        <option value="pengguna">Pengguna</option>
                    </select>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="inputMerkEdit" class="form-label">Merk</label>
                    <input type="text" name="merk" id="inputMerkEdit" class="form-control" autocomplete="false" required>
                </div>
            </div>
            <div class="modal-footer text-end">
              <button type="submit" class="btn btn-primary" id="btn-edit">Update</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog --> 
</div>