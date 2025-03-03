<div class="modal fade" id="editRadiasiModal" tabindex="-1" aria-labelledby="editRadiasiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editRadiasiModalLabel">Edit Radiasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="form-edit" method="post">
            <div class="modal-body">
                @csrf
                @method('PUT')
                <input type="hidden" name="id_radiasi" id="id_radiasi">
                <div class="mb-3">
                    <label for="inputNamaRadiasiEdit" class="form-label">Nama Radiasi</label>
                    <input type="text" name="nama_radiasi" id="inputNamaRadiasiEdit" class="form-control" autocomplete="false" required>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="submit" class="btn btn-primary" id="btn-edit">Update</button>
            </div>    
        </form>
      </div>
    </div>
</div>