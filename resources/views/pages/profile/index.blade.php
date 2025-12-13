@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content col-md-12">
            <div class="container py-4">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-4 position-sticky" style="top: 7rem;">
                            <div class="position-relative d-inline-block mx-auto mb-3">
                                <div class="rounded-circle d-flex justify-content-center align-items-center me-3 text-white fw-bold shadow-sm border border-4 border-white shadow fs-1"
                                        style="width: 120px; height: 120px; background-color: #55c57a;">
                                    {{ substr($profile->name, 0, 1) }}
                                </div>
                                <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 border border-2 border-white"
                                        title="Ganti Foto" style="width: 35px; height: 35px;">
                                    <i class="bi bi-camera-fill"></i>
                                </button>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $profile->name }}</h5>
                            <p class="text-muted small mb-3">{{ $profile->jabatan ?? 'Pelanggan' }}</p>

                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i> Akun Aktif
                                </span>
                            </div>

                            <div class="list-group list-group-flush text-start custom-menu">
                                <div id="detail-tab" class="list-group-item list-group-item-action active border-0 rounded-3 mb-1 cursor-pointer" data-bs-toggle="tab" data-bs-target="#detail-tab-pane" role="tab" aria-controls="detail-tab-pane" aria-selected="true">
                                    <i class="bi bi-person me-2"></i> Biodata Diri
                                </div>
                                <div id="instansi-tab" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 cursor-pointer" data-bs-toggle="tab" data-bs-target="#instansi-tab-pane" role="tab" aria-controls="instansi-tab-pane" aria-selected="true">
                                    <i class="bi bi-building me-2"></i> Data Instansi
                                </div>
                                <div id="ttd-tab" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 cursor-pointer" data-bs-toggle="tab" data-bs-target="#ttd-tab-pane" role="tab" aria-controls="ttd-tab-pane" aria-selected="true">
                                    <i class="bi bi-file-earmark-text me-2"></i> Tanda Tangan
                                </div>
                                <div id="changepassword-tab" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 text-danger cursor-pointer" data-bs-toggle="tab" data-bs-target="#changepassword-tab-pane" role="tab" aria-controls="changepassword-tab-pane" aria-selected="true">
                                    <i class="bi bi-key me-2"></i> Ganti Password
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="detail-tab-pane" role="tabpanel" aria-labelledby="detail-tab" tabindex="0">
                                <div class="card border-0 shadow-sm rounded-4 mb-0">
                                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                                        <h6 class="m-0 fw-bold text-dark">Informasi Biodata</h6>
                                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btnEnableEdit">
                                            <i class="bi bi-pencil-square me-1"></i> Edit Profil
                                        </button>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small text-muted fw-bold text-uppercase">Email Akun</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                                    <input type="text" class="form-control bg-light" id="email_pic" readonly>
                                                </div>
                                            </div>

                                            <form id="form-biodata" class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted fw-bold">NIK</label>
                                                    <input type="text" class="form-control" name="nik_pic" id="nik_pic" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted fw-bold">Nama Lengkap PIC</label>
                                                    <input type="text" class="form-control" name="nama_pic" id="nama_pic" disabled>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted fw-bold">Jabatan</label>
                                                    <input type="text" class="form-control" name="jabatan_pic" id="jabatan_pic" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted fw-bold">Jenis Kelamin</label>
                                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-select me-2" disabled>
                                                        <option value="laki-laki">Laki-laki</option>
                                                        <option value="perempuan">Perempuan</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-12">
                                                    <label class="form-label small text-muted fw-bold">No. Telepon / WA</label>
                                                    <input type="text" class="form-control" name="telepon" id="telepon" disabled>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small text-muted fw-bold">Alamat Domisili</label>
                                                    <textarea class="form-control" name="alamat_pic" id="alamat_pic" rows="3" disabled>-</textarea>
                                                </div>
                                            </form>

                                            <div class="col-12 mt-4">
                                                <div class="p-3 bg-light rounded-3 border border-dashed">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label small fw-bold mb-0">Dokumen Surat Kuasa</label>
                                                    </div>

                                                    <div id="uploadSuratKuasa" class="bg-white p-2 rounded"></div>

                                                    {{-- <div class="d-flex align-items-center bg-white p-2 rounded border mb-2 shadow-sm">
                                                        <i class="bi bi-file-earmark-pdf text-danger fs-4 me-3"></i>
                                                        <div class="flex-grow-1 overflow-hidden">
                                                            <h6 class="mb-0 text-truncate small fw-bold">10368-01 0256 ZC-II-2024.pdf</h6>
                                                            <small class="text-muted">1.63 MB</small>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-light text-danger border-0" title="Hapus File" disabled>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>

                                                    <div class="input-group mt-2" id="uploadArea">
                                                        <input type="file" class="form-control" name="surat_kuasa">
                                                        <button class="btn btn-outline-secondary" type="button">Upload</button>
                                                    </div> --}}
                                                </div>
                                            </div>

                                            <div class="d-none justify-content-end gap-2 mt-4 pt-3 border-top" id="actionButtons">
                                                <button type="button" class="btn btn-light" id="btnCancelEdit">Batal</button>
                                                <button type="submit" class="btn btn-primary px-4" onclick="simpanPerubahanBiodata(this)">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="tab-pane fade show" id="instansi-tab-pane" role="tabpanel" aria-labelledby="instansi-tab" tabindex="0">
                                <div class="card border-0 shadow-sm" id="card-instansi-aktif">
                                    <div class="card-body">
                                        @if (Auth::user()->status == 1)
                                            <div class="d-flex justify-content-center gap-2 mb-2">
                                                <button class="btn btn-outline-primary btn-sm" type="button" onclick="openModalPic()"><i class="bi bi-person-bounding-box"></i> Ganti PIC</button>
                                                <button class="btn btn-outline-info btn-sm" type="button" onclick="openModalHistoryPic()"><i class="bi bi-journal-text"></i> History PIC</button>
                                            </div>
                                        @else
                                            <div class="d-flex gap-2 mb-2">
                                                <div class="alert alert-info d-flex align-items-center w-100" role="alert">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <div>Anda sudah bukan PIC dari perusahaan <b>{{ Auth::user()->perusahaan->nama_perusahaan }}</b>, saat ini PIC anda adalah <b>{{ Auth::user()->perusahaan->pic->name }}</b></div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-md-4 fw-bolder mb-3">
                                                <h2>Detail</h2>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex mb-2">
                                                    <div class="flex-fill">
                                                        <label for="kode_instansi" class="form-label">Kode instansi</label>
                                                        <div class="d-flex align-items-center">
                                                            <input type="text" class="form-control me-2" id="kode_instansi" name="kode_instansi" placeholder="" disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex mb-2">
                                                    <div class="flex-fill">
                                                        <label for="idPerusahaan" class="form-label">Nama instansi</label>
                                                        <div class="d-flex align-items-center">
                                                            <div class="w-100 me-2">
                                                                {{-- <select name="idPerusahaan" class="form-select" id="idPerusahaan" disabled></select> --}}
                                                                <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" placeholder=""
                                                                    data-parsley-required-message="Nama instansi harus diisi"
                                                                    data-parsley-errors-container="#message-nama_perusahaan"
                                                                    required disabled>
                                                            </div>
                                                            @if(Auth::user()->status == 1)
                                                            <div id="btnEditDiv-nama_perusahaan" class="d-block" data-field="nama_perusahaan">
                                                                <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this, 'instansi')"><i class="bi bi-pencil"></i></button>
                                                            </div>
                                                            <div id="btnActionDiv-nama_perusahaan" class="d-none d-flex" data-field="nama_perusahaan">
                                                                <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this, 'instansi')"><i class="bi bi-x"></i></button>
                                                                <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this, 'instansi')"><i class="bi bi-check"></i></button>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div id="message-nama_perusahaan" class="invalid-feedback d-block"></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex mb-2">
                                                    <div class="flex-fill">
                                                        <label for="email" class="form-label">E-mail</label>
                                                        <div class="d-flex align-items-center">
                                                            <input type="email" class="form-control me-2" id="email" name="email" placeholder=""
                                                                data-parsley-errors-container="#message-email"
                                                                disabled>
                                                            @if(Auth::user()->status == 1)
                                                            <div id="btnEditDiv-email" class="d-block" data-field="email">
                                                                <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this, 'instansi')"><i class="bi bi-pencil"></i></button>
                                                            </div>
                                                            <div id="btnActionDiv-email" class="d-none d-flex" data-field="email">
                                                                <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this, 'instansi')"><i class="bi bi-x"></i></button>
                                                                <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this, 'instansi')"><i class="bi bi-check"></i></button>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div id="message-email" class="invalid-feedback d-block"></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex mb-2">
                                                    <div class="flex-fill">
                                                        <label for="npwp" class="form-label">NPWP</label>
                                                        <div class="d-flex align-items-center">
                                                            <input type="text" class="form-control me-2 maskNPWP" id="npwp" name="npwp" disabled autocomplete="true">
                                                            @if(Auth::user()->status == 1)
                                                            <div id="btnEditDiv-npwp" class="d-block" data-field="npwp">
                                                                <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this, 'instansi')"><i class="bi bi-pencil"></i></button>
                                                            </div>
                                                            <div id="btnActionDiv-npwp" class="d-none d-flex" data-field="npwp">
                                                                <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this, 'instansi')"><i class="bi bi-x"></i></button>
                                                                <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this, 'instansi')"><i class="bi bi-check"></i></button>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2" id="form-alamat-perusahaan">
                                            <hr>
                                            <div class="col-md-4 fw-bolder mb-3">
                                                <h2>Alamat Perusahaan</h2>
                                            </div>
                                            <div class="col-md-6" id="list-alamat">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card border-0 shadow-sm p-2" id="card-instansi-nonaktif">
                                    <form id="form-instansi-nonaktif" novalidate>
                                        <div class="border rounded my-4 p-3 mx-xl-5 position-relative">
                                            <span class="position-absolute top-0 start-50 translate-middle bg-white px-2 fs-5">
                                                Data Instansi
                                            </span>
                                            <div class="row mt-3">
                                                <div class="col-md-12 mb-2">
                                                    <label for="nama_instansi_new" class="form-label">Nama Instansi <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="nama_instansi_new" name="nama_instansi" required data-parsley-required-message="Nama instansi harus diisi">
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="email_instansi_new" class="form-label">Email instansi <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <input type="email" class="form-control maskEmail" id="email_instansi_new" name="email_instansi" required
                                                        data-parsley-required-message="Email instansi harus diisi"
                                                        data-parsley-errors-container="#email_instansi_error">
                                                    <div id="email_instansi_error" class="invalid-feedback d-block"></div>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="npwp_new" class="form-label">NPWP <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <input type="text" class="form-control maskNPWP" id="npwp_new" name="npwp" required data-parsley-required-message="NPWP harus diisi">
                                                </div>
                                                <div class="col-md-12 mb-2">
                                                    <label for="kode_pos_new" class="form-label">Kode Pos <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <input type="text" class="form-control maskNumber" id="kode_pos_new" name="kode_pos" placeholder="" autocomplete="true" required data-parsley-required-message="Kode Pos harus diisi">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="alamat_instansi_new" class="form-label">Alamat <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <textarea name="alamat_instansi" id="alamat_instansi_new" cols="30" rows="5" class="form-control" required data-parsley-required-message="Alamat instansi harus diisi"></textarea>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="button" onclick="tambahInstansi(this)" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="tab-pane fade show" id="ttd-tab-pane" role="tabpanel" aria-labelledby="ttd-tab" tabindex="0">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                                        <h6 class="m-0 fw-bold text-dark">
                                            <i class="bi bi-pen-fill me-2 text-primary"></i>Update Tanda Tangan
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-hapus-ttd">
                                            <i class="bi bi-eraser me-1"></i> Bersihkan
                                        </button>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="row g-4">

                                            <div class="col-md-8">
                                                <label class="form-label small fw-bold text-muted mb-2">Area Tanda Tangan</label>

                                                <div class="signature-wrapper position-relative border rounded-3 bg-light overflow-hidden"
                                                    style="height: 300px; cursor: crosshair; width: 444px;">

                                                    <div id="show-ttd" class="w-100 h-100"></div>

                                                    <div class="position-absolute top-50 start-0 w-100 border-top border-secondary opacity-25" style="border-style: dashed !important; pointer-events: none;"></div>

                                                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 text-muted opacity-50 small user-select-none" style="pointer-events: none;">
                                                        Tanda tangan di sini
                                                    </div>
                                                </div>

                                                <div class="form-text small mt-2">
                                                    <i class="bi bi-info-circle me-1"></i> Gunakan mouse atau jari (layar sentuh) untuk tanda tangan.
                                                </div>
                                            </div>

                                            <div class="col-md-4 d-flex flex-column">
                                                <label class="form-label small fw-bold text-muted mb-2">Tanda Tangan Saat Ini</label>

                                                <div class="border rounded-3 p-3 bg-white d-flex align-items-center justify-content-center flex-grow-1 text-center position-relative mb-3"
                                                    style="min-height: 200px; background-image: radial-gradient(#dee2e6 1px, transparent 1px); background-size: 20px 20px;">

                                                    <div id="show-ttd-preview d-none" >
                                                        <div id="ttd-preview"></div>
                                                        <span class="badge bg-success position-absolute top-0 end-0 m-2 shadow-sm">Aktif</span>
                                                    </div>

                                                    <div class="text-muted opacity-50" id="empty-ttd-preview">
                                                        <i class="bi bi-x-circle fs-1 d-block mb-2"></i>
                                                        <small>Belum ada data</small>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="signature_data" id="signature-data">

                                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="btn-upload-ttd">
                                                    Simpan
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade show" id="changepassword-tab-pane" role="tabpanel" aria-labelledby="changepassword-tab" tabindex="0">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-header bg-white py-3 border-bottom rounded-top-4">
                                        <h6 class="m-0 fw-bold text-dark">
                                            <i class="bi bi-shield-lock me-2 text-danger"></i>Keamanan Akun
                                        </h6>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="alert alert-light border-start border-4 border-info shadow-sm mb-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                                                <div>
                                                    <small class="text-muted d-block fw-bold">Amankan akun Anda</small>
                                                    <small class="text-muted" style="font-size: 0.8rem;">Gunakan kombinasi huruf besar, kecil, dan angka agar password sulit ditebak.</small>
                                                </div>
                                            </div>
                                        </div>

                                        <form id="form-change-password" novalidate>
                                            <div class="mb-4 bg-light p-3 rounded-3 border">
                                                <label class="form-label small fw-bold text-muted">Password Saat Ini <span class="fw-bold fs-14 text-danger">*</span></label>

                                                <div class="input-group mb-2 mt-1">
                                                    <input
                                                        class="form-control form-control input-login"
                                                        id="old_password"
                                                        type="password"
                                                        name="old_password"
                                                        required
                                                        data-parsley-required-message="Password Lama harus diisi"
                                                        data-parsley-errors-container="#error-old-password"
                                                        placeholder="Enter your old password" />
                                                    <button class="input-group-text btn border-start-0 border" onclick="showPassword(this)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="invalid-feedback d-block" id="error-old-password"></div>
                                            </div>

                                            <hr class="my-4 opacity-10">

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Password Baru <span class="fw-bold fs-14 text-danger">*</span></label>

                                                    <div class="input-group">
                                                        <input
                                                            class="form-control form-control input-login"
                                                            id="new_password"
                                                            type="password"
                                                            name="new_password"
                                                            required
                                                            data-parsley-trigger="input"
                                                            data-parsley-minlength="8"
                                                            data-parsley-lowercase="true"
                                                            data-parsley-uppercase="true"
                                                            data-parsley-errors-container="#error-new-password"
                                                            placeholder="Enter your new password" />
                                                        <button class="input-group-text btn border-start-0 border" onclick="showPassword(this)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                    <div class="progress mt-2" style="height: 4px;">
                                                        <div class="progress-bar bg-danger" id="strengthBar" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <ul id="password-rules" class="p-3 bg-light rounded-3 mt-2">
                                                    </ul>
                                                    <div class="invalid-feedback d-none" id="error-new-password"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Konfirmasi Password <span class="fw-bold fs-14 text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input
                                                            class="form-control form-control input-login"
                                                            id="confirm_password"
                                                            type="password"
                                                            name="confirm_password"
                                                            required
                                                            data-parsley-trigger="input"
                                                            data-parsley-equalto="#new_password"
                                                            data-parsley-equalto-message="Konfirmasi password tidak sama."
                                                            data-parsley-required-message="Konfirmasi password wajib diisi."
                                                            data-parsley-errors-container="#error-confirm-password"
                                                            placeholder="Enter your confirm password" />
                                                        <button class="input-group-text btn border-start-0 border" onclick="showPassword(this)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback d-block" id="error-confirm-password"></div>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary px-4" onclick="gantiPassword(this)">
                                                Update Password
                                            </button>
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

    @include('pages.profile.component.modal_pic')
@endsection
@push('scripts')
    <script>
        @if ($errors->any())
            editProfile($('#btnEditProfile'));
        @endif

        let profile = @json($profile);
        const isPassword = {{ $isPassword ? 1 : 0 }};
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
