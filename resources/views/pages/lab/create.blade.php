<div class="modal fade" id="createLabModal">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Create Lab</h4>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('lab.store') }}" method="post">
            @csrf
            <div class="modal-body">
                <div class="mb-3 row">
                    <label for="inputNameLab" class="form-label">Name lab</label>
                    <input type="text" name="name" id="inputNameLab" class="form-control" autocomplete="off" required>
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
