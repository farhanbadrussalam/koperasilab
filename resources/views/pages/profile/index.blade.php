@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content col-md-12">
            <div class="container">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail-tab-pane" type="button" role="tab" aria-controls="detail-tab-pane" aria-selected="true">My Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="alamat-tab" data-bs-toggle="tab" data-bs-target="#alamat-tab-pane" type="button" role="tab" aria-controls="alamat-tab-pane" aria-selected="true">Alamat</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ttd-tab" data-bs-toggle="tab" data-bs-target="#ttd-tab-pane" type="button" role="tab" aria-controls="ttd-tab-pane" aria-selected="true">Tanda Tangan</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="changepassword-tab" data-bs-toggle="tab" data-bs-target="#changepassword-tab-pane" type="button" role="tab" aria-controls="changepassword-tab-pane" aria-selected="true">Change Password</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active pt-3" id="detail-tab-pane" role="tabpanel" aria-labelledby="detail-tab" tabindex="0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 fw-bolder mb-3">
                                        <h2>Biodata PIC</h2>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="nama_instansi" class="form-label">Nama instansi</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2" id="nama_instansi" name="nama_instansi" placeholder="" disabled>
                                                    <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm" type="button"><i class="bi bi-pencil"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="nama_pic" class="form-label">Nama PIC</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2" id="nama_pic" name="nama_pic" placeholder="" disabled autocomplete="true">
                                                    <div id="btnEditDiv-nama_pic" class="d-block" data-field="nama_pic">
                                                        <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this)"><i class="bi bi-pencil"></i></button>
                                                    </div>
                                                    <div id="btnActionDiv-nama_pic" class="d-none d-flex" data-field="nama_pic">
                                                        <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this)"><i class="bi bi-x"></i></button>
                                                        <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this)"><i class="bi bi-check"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="jabatan_pic" class="form-label">Jabatan PIC</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2" id="jabatan_pic" name="jabatan_pic" placeholder="" disabled autocomplete="true">
                                                    <div id="btnEditDiv-jabatan_pic" class="d-block" data-field="jabatan_pic">
                                                        <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this)"><i class="bi bi-pencil"></i></button>
                                                    </div>
                                                    <div id="btnActionDiv-jabatan_pic" class="d-none d-flex" data-field="jabatan_pic">
                                                        <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this)"><i class="bi bi-x"></i></button>
                                                        <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this)"><i class="bi bi-check"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="email" class="form-control me-2" id="email" name="email" placeholder="" disabled>
                                                    <div id="btnEditDiv-email" class="d-block" data-field="email">
                                                        <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this)"><i class="bi bi-pencil"></i></button>
                                                    </div>
                                                    <div id="btnActionDiv-email" class="d-none d-flex" data-field="email">
                                                        <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this)"><i class="bi bi-x"></i></button>
                                                        <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this)"><i class="bi bi-check"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="telepon" class="form-label">Telepon</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2 maskTelepon" id="telepon" name="telepon" disabled autocomplete="true">
                                                    <div id="btnEditDiv-telepon" class="d-block" data-field="telepon">
                                                        <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this)"><i class="bi bi-pencil"></i></button>
                                                    </div>
                                                    <div id="btnActionDiv-telepon" class="d-none d-flex" data-field="telepon">
                                                        <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this)"><i class="bi bi-x"></i></button>
                                                        <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this)"><i class="bi bi-check"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="npwp" class="form-label">NPWP</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2 maskNPWP" id="npwp" name="npwp" disabled autocomplete="true">
                                                    <div id="btnEditDiv-npwp" class="d-block" data-field="npwp">
                                                        <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this)"><i class="bi bi-pencil"></i></button>
                                                    </div>
                                                    <div id="btnActionDiv-npwp" class="d-none d-flex" data-field="npwp">
                                                        <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this)"><i class="bi bi-x"></i></button>
                                                        <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this)"><i class="bi bi-check"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show pt-3" id="alamat-tab-pane" role="tabpanel" aria-labelledby="alamat-tab" tabindex="0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 fw-bolder mb-3">
                                        <h2>Alamat Perusahaan</h2>
                                    </div>
                                    <div class="col-md-6" id="list-alamat">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show pt-3" id="ttd-tab-pane" role="tabpanel" aria-labelledby="ttd-tab" tabindex="0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 fw-bolder mb-3 text-center">
                                        <div id="show-ttd"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-4 text-start d-flex flex-column gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-upload-ttd"><i class="bi bi-upload"></i> Upload Tanda Tangan</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" id="btn-hapus-ttd"><i class="bi bi-trash"></i> Hapus Tanda Tangan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show pt-3" id="changepassword-tab-pane" role="tabpanel" aria-labelledby="changepassword-tab" tabindex="0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 fw-bolder mb-3">
                                        <h2>Change Password</h2>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4 text-start">
                                            <label for="old_password" class="form-label text-main body-medium">Old Password</label>
                                            <div class="input-group mb-2 mt-1">
                                                <input
                                                    class="form-control form-control input-login"
                                                    id="old_password"
                                                    type="password"
                                                    name="old_password"
                                                    placeholder="Enter your old password" />
                                                <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1">
                                                    <i class="bi bi-eye"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4 text-start">
                                            <label for="new_password" class="form-label text-main body-medium">New Password</label>
                                            <div class="input-group mb-2 mt-1">
                                                <input
                                                    class="form-control form-control input-login"
                                                    id="new_password"
                                                    type="password"
                                                    name="new_password"
                                                    placeholder="Enter your new password" />
                                                <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1">
                                                    <i class="bi bi-eye"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4 text-start">
                                            <label for="confirm_password" class="form-label text-main body-medium">Confirm Password</label>
                                            <div class="input-group mb-2 mt-1">
                                                <input
                                                    class="form-control form-control input-login"
                                                    id="confirm_password"
                                                    type="password"
                                                    name="confirm_password"
                                                    placeholder="Enter your confirm password" />
                                                <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1">
                                                    <i class="bi bi-eye"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <button class="btn btn-primary">Change</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        @if ($errors->any())
            editProfile($('#btnEditProfile'));
        @endif

        const user = @json($profile);

        function editProfile(obj) {
            const name = $('#inputName');
            const nik = $('#inputNik');
            const email = $('#inputEmail');
            const nomer = $('#inputNomer');
            const jenisKelamin = $('#inputJenisKelamin');
            const alamat = $('#inputAlamat');

            name.removeAttr('readonly');
            nik.removeAttr('readonly');
            email.removeAttr('readonly');
            nomer.removeAttr('readonly');
            jenisKelamin.removeAttr('disabled');
            alamat.removeAttr('readonly');

            name.focus();

            $(obj).addClass('d-none');
            $('#actionBtnProfile').removeClass('d-none');
        }
    </script>
    <script src="{{ asset('js/profile.js') }}"></script>
@endpush
