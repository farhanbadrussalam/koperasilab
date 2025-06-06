<div class="modal fade" id="createRoleModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Create Role</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          </button>
        </div>
        <form id="form-create" method="post">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label for="inputNameRole" class="form-label">Name role</label>
                    <input type="text" name="name" id="inputNameRole" class="form-control" autocomplete="false" required>
                </div>
                <div class="mb-3">
                    <label for="inputPermission" class="form-label">Permissions</label>
                    <div class="d-flex flex-wrap gap-2" id="list-permission">
                        @foreach ($permissions as $permission)
                        <div class="form-group mx-1">
                            <div class="custom-control custom-switch">
                              <input type="checkbox" class="custom-control-input" name="permission[]" value="{{ $permission->name }}" id="createPermission{{$permission->id}}">
                              <label class="custom-control-label cursor-pointer" for="createPermission{{$permission->id}}">{{ $permission->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer text-end">
              <button type="submit" class="btn btn-primary" id="btn-create">Create</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
