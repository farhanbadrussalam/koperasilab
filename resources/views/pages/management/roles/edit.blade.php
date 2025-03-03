<div class="modal fade" id="editRoleModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Role</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          </button>
        </div>
        <form id="form-edit" method="post">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="mb-3">
                    <label for="inputEditNameRole" class="form-label">Name role</label>
                    <input type="text" name="name" id="inputEditNameRole" class="form-control" autocomplete="false" required>
                    <input type="text" name="id_role" id="inputEditIdRole" class="form-control d-none">
                </div>
                <div class="mb-3">
                    <label for="inputEditPermission" class="form-label">Permissions</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($permissions as $permission)
                        <div class="form-group mx-1">
                            <div class="custom-control custom-switch">
                              <input type="checkbox" class="custom-control-input" name="permissionEdit[]" value="{{ $permission->name }}" id="checkPermission{{$permission->id}}">
                              <label class="custom-control-label cursor-pointer" for="checkPermission{{$permission->id}}">{{ $permission->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
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
