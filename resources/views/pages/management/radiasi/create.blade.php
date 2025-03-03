<div class="modal fade" id="createRadiasiModal" tabindex="-1" aria-labelledby="createRadiasiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createRadiasiModalLabel">Create Radiasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="form-create" method="post">
            <div class="modal-body">
                @csrf
                <div class="mb-3">
                    <label for="inputNamaRadiasi" class="form-label">Nama Radiasi</label>
                    <input type="text" name="nama_radiasi" id="inputNamaRadiasi" class="form-control" autocomplete="false" required>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="submit" class="btn btn-primary" id="btn-create">Save</button>
            </div>
        </form>
      </div>
    </div>
</div>