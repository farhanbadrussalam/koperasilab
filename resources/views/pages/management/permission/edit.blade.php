<div class="modal fade" id="editPermissionModal">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Permission</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="form-edit" method="post">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="mb-3">
                    <label for="inputEditNamePermission" class="form-label">Name permission</label>
                    <input type="text" name="name" id="inputEditNamePermission" class="form-control" autocomplete="off" required>
                    <input type="text" name="id_permission" id="inputEditIdPermission" class="form-control d-none">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="submit" class="btn btn-primary" id="btn-edit">Save changes</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
