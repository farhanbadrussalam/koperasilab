@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset_versioned('css/auth/registrasi.css') }}">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-5">
                            <h2 class="fw-bold text-primary">Pendaftaran Akun</h2>
                            <p class="text-muted">Silakan lengkapi data diri Anda untuk menggunakan layanan kami</p>
                        </div>

                        <!-- Step 1: Cek NIK -->
                        <div class="registration-form" id="cek-akun-form">
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div>
                                        <label class="form-label fw-semibold mb-3">Masukkan NIK Anda</label>
                                        <div class="input-group input-group-lg shadow-sm rounded">
                                            <input type="text" class="form-control maskNIK" placeholder="Contoh: 3201..."
                                                autocomplete="false" id="input-cek-akun" required
                                                data-parsley-minlength-message="NIK minimal 16 karakter"
                                                data-parsley-errors-container="#message-input-nik" minlength="16">
                                            <button type="button" class="btn btn-primary px-4" onclick="searchAkun(this)"
                                                id="btn-cek-akun">Cari</button>
                                        </div>
                                        <div id="message-input-nik" class="invalid-feedback d-block mt-2"></div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <a class="btn btn-link text-decoration-none text-muted" href="{{ route('login') }}">
                                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                                        </a>
                                        <div class="alert alert-info mb-0 d-none" role="alert" id="alert-cek-akun"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Form Registrasi -->
                        <div class="registration-form d-none" id="registration-form">
                            <form action="{{ route('register') }}" method="post" enctype="multipart/form-data"
                                id="form-registration">
                                @csrf

                                <!-- Section: Data PIC -->
                                <div class="mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                            style="width: 32px; height: 32px;">1</div>
                                        <h5 class="fw-bold mb-0">Informasi Pribadi (PIC)</h5>
                                    </div>

                                    <div class="row g-3 px-md-4">
                                        <div class="col-md-6">
                                            <label for="nik"
                                                class="form-label small fw-bold text-uppercase text-muted">NIK <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control bg-light" id="input-nik"
                                                    name="nik" readonly>
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="changeNik()">Ganti</button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email"
                                                class="form-label small fw-bold text-uppercase text-muted">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control maskEmail" id="input-email"
                                                name="email" required data-parsley-required-message="Email harus diisi"
                                                data-parsley-errors-container="#email_error">
                                            <div id="email_error" class="invalid-feedback d-block"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="nama_pic"
                                                class="form-label small fw-bold text-uppercase text-muted">Nama Lengkap
                                                <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nama_pic" name="nama_pic"
                                                required data-parsley-required-message="Nama harus diisi">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="jabatan_pic"
                                                class="form-label small fw-bold text-uppercase text-muted">Jabatan</label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('jabatan_pic') ? 'is-invalid' : '' }}"
                                                id="jabatan_pic" name="jabatan_pic" value="{{ old('jabatan_pic') }}">
                                            @error('jabatan_pic')
                                                <span class="invalid-feedback"
                                                    role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="jenis_kelamin"
                                                class="form-label small fw-bold text-uppercase text-muted">Jenis Kelamin
                                                <span class="text-danger">*</span></label>
                                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
                                                <option value="laki-laki">Laki-laki</option>
                                                <option value="perempuan">Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="telepon"
                                                class="form-label small fw-bold text-uppercase text-muted">Telepon <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control maskTelepon" id="telepon"
                                                name="telepon" required
                                                data-parsley-required-message="Telepon harus diisi">
                                        </div>
                                        <div class="col-12">
                                            <label for="alamat"
                                                class="form-label small fw-bold text-uppercase text-muted">Alamat Domisili
                                                <span class="text-danger">*</span></label>
                                            <textarea name="alamat" id="alamat" rows="2" class="form-control" required
                                                data-parsley-required-message="Alamat harus diisi"></textarea>
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <label
                                                class="form-label small fw-bold text-uppercase text-muted d-block mb-3">Foto
                                                Profil (Avatar)</label>
                                            <div class="profile-avatar-container">
                                                <img src="{{ asset('assets/img/default-avatar.jpg') }}"
                                                    id="avatar-preview" alt="Avatar Preview"
                                                    class="profile-avatar-preview">
                                                <label for="avatar" class="profile-avatar-upload-btn"
                                                    title="Pilih Foto">
                                                    <i class="bi bi-camera-fill"></i>
                                                </label>
                                                <input type="file" name="avatar"
                                                    accept="image/png, image/jpeg, image/jpg" id="avatar"
                                                    onchange="previewAvatar(this)" class="d-none">
                                            </div>
                                            <small class="text-muted d-block mt-2">Mendukung format PNG, JPG, atau JPEG
                                                (Maks. 2MB)</small>
                                            <div id="avatar_error" class="invalid-feedback d-block mt-1"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password"
                                                class="form-label small fw-bold text-uppercase text-muted">Password <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="input-password"
                                                    name="password" required data-parsley-minlength="8"
                                                    data-parsley-errors-container="#password_error">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    onclick="showPassword(this)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div id="password_error" class="invalid-feedback d-none"></div>
                                            <ul id="password-rules" class="mt-2 small text-muted ps-0"
                                                style="list-style: none;"></ul>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password_confirmation"
                                                class="form-label small fw-bold text-uppercase text-muted">Konfirmasi
                                                Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="password_confirmation"
                                                    name="password_confirmation" required data-parsley-trigger="input"
                                                    data-parsley-equalto="#input-password"
                                                    data-parsley-equalto-message="Konfirmasi password tidak sama."
                                                    data-parsley-required-message="Konfirmasi password wajib diisi."
                                                    data-parsley-errors-container="#konfirm_password_error">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    onclick="showPassword(this)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div id="konfirm_password_error" class="invalid-feedback d-block"></div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-uppercase text-muted">Surat Kuasa
                                                <span class="text-danger">*</span></label>
                                            <div id="uploadSuratKuasa"
                                                class="border border-dashed p-3 rounded bg-light text-center">
                                                <!-- Konten file upload akan dimuat oleh JS -->
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input" id="p_baru"
                                                    name="pelanggan_tipe" value="baru" required
                                                    data-parsley-errors-container="#pelanggan_tipe_error"
                                                    data-parsley-required-message="Silakan pilih tipe pelanggan terlebih dahulu">
                                                <label class="form-check-label" for="p_baru">Pelanggan Baru</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input" id="p_lama"
                                                    name="pelanggan_tipe" value="lama">
                                                <label class="form-check-label" for="p_lama">Pelanggan Lama</label>
                                            </div>
                                        </div>
                                        <div id="pelanggan_tipe_error" class="col-12 mt-1"></div>
                                    </div>
                                </div>

                                <!-- Section: Data Instansi -->
                                <div class="mb-5" id="section-instansi" style="display: none;">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                            style="width: 32px; height: 32px;">2</div>
                                        <h5 class="fw-bold mb-0">Informasi Instansi</h5>
                                    </div>

                                    <div class="row g-3 px-md-4">
                                        <div class="col-12">
                                            <label for="nama_instansi"
                                                class="form-label small fw-bold text-uppercase text-muted">Nama Instansi
                                                <span class="text-danger">*</span></label>
                                            <input type="hidden" name="type_instansi" id="type_instansi">

                                            <!-- Input Teks untuk Pelanggan Baru -->
                                            <input type="text" class="form-control" id="nama_instansi_baru"
                                                name="nama_instansi" required
                                                data-parsley-required-message="Nama instansi harus diisi">

                                            <!-- Select2 untuk Pelanggan Lama (Disembunyikan secara default) -->
                                            <select class="form-select" id="nama_instansi_lama" style="display: none;"
                                                data-parsley-required-message="Pilih instansi Anda"
                                                name="nama_instansi_lama"></select>
                                        </div>
                                        <div id="form-instansi-detail" class="col-12 m-0 p-0" style="display: none;">
                                            <div class="row g-3 mt-0">
                                                <div class="col-md-6">
                                                    <label for="email_instansi"
                                                        class="form-label small fw-bold text-uppercase text-muted">Email
                                                        Instansi <span class="text-danger">*</span></label>
                                                    <input type="email"
                                                        class="form-control maskEmail instansi-detail-input"
                                                        id="email_instansi" name="email_instansi"
                                                        data-parsley-errors-container="#email_instansi_error">
                                                    <div id="email_instansi_error" class="invalid-feedback d-block"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="npwp"
                                                        class="form-label small fw-bold text-uppercase text-muted">NPWP
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control maskNPWP instansi-detail-input" id="npwp"
                                                        name="npwp">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="kota"
                                                        class="form-label small fw-bold text-uppercase text-muted">Kota
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control instansi-detail-input"
                                                        id="kota" name="kota">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="kode_pos"
                                                        class="form-label small fw-bold text-uppercase text-muted">Kode Pos
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control maskNumber instansi-detail-input"
                                                        id="kode_pos" name="kode_pos">
                                                </div>
                                                <div class="col-12">
                                                    <label for="alamat_instansi"
                                                        class="form-label small fw-bold text-uppercase text-muted">Alamat
                                                        Instansi <span class="text-danger">*</span></label>
                                                    <textarea name="alamat_instansi" id="alamat_instansi" rows="2" class="form-control instansi-detail-input"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ReCaptcha & Action -->
                                <div class="px-md-4 text-center">
                                    @if (env('APP_ENV') == 'production')
                                        <div class="form-group mb-4 d-inline-block">
                                            {!! NoCaptcha::renderJs() !!}
                                            {!! NoCaptcha::display() !!}
                                            @if ($errors->has('g-recaptcha-response'))
                                                <span class="help-block text-danger small">
                                                    <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                        <button class="btn btn-outline-danger px-5 py-2 rounded-pill" type="button"
                                            onclick="location.reload()">Batal</button>
                                        <button class="btn btn-primary px-5 py-2 rounded-pill shadow-sm"
                                            onclick="simpan()" type="button">
                                            Selesaikan Pendaftaran <i class="bi bi-check2-circle ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset_versioned('js/auth/register.js') }}"></script>
@endpush
