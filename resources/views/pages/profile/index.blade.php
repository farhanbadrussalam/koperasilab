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
                                                    <input type="text" class="form-control me-2" id="nama_instansi" name="nama_instansi" placeholder="" value="PT Sejahtera" disabled>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="nama_pic" class="form-label">Nama PIC</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2" id="nama_pic" name="nama_pic" placeholder="" value="{{ Auth::user()->name }}" disabled>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="jabatan_pic" class="form-label">Jabatan PIC</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2" id="jabatan_pic" name="jabatan_pic" placeholder="" value="Manager" disabled>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="email" class="form-control me-2" id="email" name="email" placeholder="" value="Sejahtera@gmail.com" disabled>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="telepon" class="form-label">Telepon</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2" id="telepon" name="telepon" value="08962736152" disabled>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <div class="flex-fill">
                                                <label for="npwp" class="form-label">NPWP</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="text" class="form-control me-2" id="npwp" name="npwp" value="890948347363748547" disabled>
                                                    <a href="#">Edit</a>
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
                                    <div class="col-md-6">
                                        <div class="d-flex mb-3">
                                            <div class="flex-fill">
                                                <label for="alamat_utama" class="form-label">Alamat utama</label>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-fill me-2">
                                                        <textarea name="alamat_utama" id="alamat_utama" cols="30" rows="3" class="form-control mb-2" disabled></textarea>
                                                        <input type="text" class="form-control me-2" placeholder="Kode pos" disabled>
                                                    </div>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-3">
                                            <div class="flex-fill">
                                                <div class="d-flex">
                                                    <label for="alamat_tld" class="form-label me-3">Alamat TLD</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="switchAlamatTld">
                                                    </div>
                                                </div>
                                                <div id="alamat_tld_inactive">
                                                    <p>Alamat sesuai dengan alamat utama</p>
                                                </div>
                                                <div class="d-flex align-items-center d-none" id="alamat_tld_active">
                                                    <div class="flex-fill me-2">
                                                        <textarea name="alamat_utama" id="alamat_utama" cols="30" rows="3" class="form-control mb-2" disabled></textarea>
                                                        <input type="text" class="form-control me-2" placeholder="Kode pos" disabled>
                                                    </div>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-3">
                                            <div class="flex-fill">
                                                <div class="d-flex">
                                                    <label for="alamat_lhu" class="form-label me-3">Alamat LHU</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="switchAlamatLhu" checked>
                                                    </div>
                                                </div>
                                                <div id="alamat_lhu_inactive" class="d-none">
                                                    <p>Alamat sesuai dengan alamat utama</p>
                                                </div>
                                                <div class="d-flex align-items-center" id="alamat_lhu_active">
                                                    <div class="flex-fill me-2">
                                                        <textarea name="alamat_utama" id="alamat_utama" cols="30" rows="3" class="form-control mb-2" disabled></textarea>
                                                        <input type="text" class="form-control me-2" placeholder="Kode pos" disabled>
                                                    </div>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-3">
                                            <div class="flex-fill">
                                                <div class="d-flex">
                                                    <label for="alamat_invoice" class="form-label me-3">Alamat Invoice</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="switchAlamatInvoice">
                                                    </div>
                                                </div>
                                                <div id="alamat_invoice_inactive">
                                                    <p>Alamat sesuai dengan alamat utama</p>
                                                </div>
                                                <div class="d-flex align-items-center d-none" id="alamat_invoice_active">
                                                    <div class="flex-fill me-2">
                                                        <textarea name="alamat_utama" id="alamat_utama" cols="30" rows="3" class="form-control mb-2" disabled></textarea>
                                                        <input type="text" class="form-control me-2" placeholder="Kode pos" disabled>
                                                    </div>
                                                    <a href="#">Edit</a>
                                                </div>
                                            </div>
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
@endpush
