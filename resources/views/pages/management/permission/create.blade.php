<div class="modal fade" id="create_modal" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Create Permission</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="form-create" method="post">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label for="inputNamePermission" class="form-label">Name permission</label>
                    <input type="text" name="name" id="inputNamePermission" class="form-control" autocomplete="false" required>
                </div>
            </div>
            <div class="modal-footer justify-content-between text-end">
              <button type="submit" class="btn btn-primary" id="btn-create">Save</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
