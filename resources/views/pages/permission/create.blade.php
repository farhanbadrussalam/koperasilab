<div class="modal fade" id="createPermissionModal">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Create Permission</h4>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('permission.store') }}" method="post">
            @csrf
            <div class="modal-body">
                <div class="mb-3 row">
                    <label for="inputNamePermission" class="form-label">Name permission</label>
                    <input type="text" name="name" id="inputNamePermission" class="form-control" autocomplete="false" required>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
