<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-warning-subtle text-warning rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark" id="editRoleTitle">Edit Role</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-edit" method="post" data-parsley-validate>
                @csrf
                @method('PUT')
                <div class="modal-body px-4 pt-3 pb-2">
                    <div class="mb-3">
                        <label for="inputEditNameRole" class="form-label fw-semibold text-secondary">Name Role <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" id="inputEditNameRole" class="form-control rounded-3"
                            placeholder="e.g. Staff Administrasi" autocomplete="off" required data-parsley-minlength="3"
                            data-parsley-maxlength="50" data-parsley-trigger="input"
                            data-parsley-required-message="Nama peran harus diisi."
                            data-parsley-minlength-message="Nama minimal 3 karakter.">
                        <input type="text" name="id_role" id="inputEditIdRole" class="form-control d-none">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary d-block">Permissions <span
                                class="text-danger">*</span></label>
                        <p class="text-muted small mt-n1 mb-3">Sesuaikan hak akses yang dimiliki oleh peran ini. Pilih
                            berdasarkan kategori di bawah.</p>

                        @php
                            $groupedPermissions = [];
                            foreach ($permissions as $permission) {
                                $parts = explode('/', $permission->name);
                                $category = count($parts) > 1 ? $parts[0] : 'Umum';
                                $groupedPermissions[$category][] = $permission;
                            }
                        @endphp

                        <div class="accordion overflow-auto" id="accordionPermissionsEdit" style="max-height: 300px;">
                            @foreach ($groupedPermissions as $category => $items)
                                @php
                                    $catSlug = Str::slug($category);
                                @endphp
                                <div class="accordion-item border border-light-subtle rounded-3 mb-2 overflow-hidden">
                                    <h2 class="accordion-header">
                                        <button
                                            class="accordion-button bg-light-subtle fw-semibold text-dark py-3 px-3 d-flex align-items-center justify-content-between"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseCatEdit{{ $catSlug }}" aria-expanded="true">
                                            <span class="d-flex align-items-center">
                                                <i class="bi bi-folder2-open text-primary me-2"></i>
                                                {{ strtoupper($category) }}
                                                <span
                                                    class="badge bg-secondary-subtle text-secondary ms-2 fs-12">{{ count($items) }}
                                                    Hak Akses</span>
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapseCatEdit{{ $catSlug }}"
                                        class="accordion-collapse collapse show"
                                        data-bs-parent="#accordionPermissionsEdit">
                                        <div class="accordion-body bg-white border-top border-light-subtle p-3">
                                            <!-- Select All Button for this category -->
                                            <div class="d-flex justify-content-end mb-2">
                                                <button type="button"
                                                    class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 btn-toggle-group"
                                                    data-group="{{ $catSlug }}">
                                                    <i class="bi bi-check2-all me-1"></i> Pilih Semua
                                                </button>
                                            </div>
                                            <div class="row row-cols-1 row-cols-md-2 g-2">
                                                @foreach ($items as $permission)
                                                    <div class="col">
                                                        <div
                                                            class="form-check form-switch p-3 rounded border border-light-subtle bg-light-subtle hover-bg transition-all h-100 d-flex align-items-center">
                                                            <input type="checkbox"
                                                                class="form-check-input ms-0 me-3 flex-shrink-0 edit-perm-checkbox"
                                                                name="permissionEdit[]" value="{{ $permission->name }}"
                                                                id="checkPermission{{ $permission->id }}"
                                                                data-group-class="{{ $catSlug }}">
                                                            <label
                                                                class="form-check-label cursor-pointer text-dark fw-medium small mb-0 w-100"
                                                                for="checkPermission{{ $permission->id }}">
                                                                {{ $permission->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light px-4 rounded-pill"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill" id="btn-edit">Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
