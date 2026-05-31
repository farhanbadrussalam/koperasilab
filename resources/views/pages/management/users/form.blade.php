@extends('layouts.main')

@section('content')
<style>
    .card-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        position: relative;
        padding-left: 12px;
    }
    .card-section-title::before {
        content: "";
        position: absolute;
        left: 0;
        top: 4px;
        bottom: 4px;
        width: 4px;
        background-color: var(--bs-primary);
        border-radius: 4px;
    }
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
    }
    /* Parsley validation styling mapping */
    .parsley-errors-list {
        list-style: none;
        padding-left: 0;
        font-size: 0.75rem;
        color: var(--bs-danger);
        margin-top: 4px;
        margin-bottom: 0;
    }
</style>

<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-2 px-3 bg-light rounded-pill fs-6 shadow-sm d-inline-flex">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="bi bi-house-door-fill"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-muted">Users</a></li>
                    <li class="breadcrumb-item active text-primary fw-medium" aria-current="page">{{ isset($d_user) ? 'Update' : 'Create' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-primary-subtle text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                    <i class="bi bi-person-badge-fill fs-5"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark">{{ isset($d_user) ? 'Update User Account' : 'Register New User' }}</h3>
                    <p class="text-muted small mb-0">{{ isset($d_user) ? 'Perbarui kredensial, peran, dan biodata pribadi pengguna.' : 'Buat akun pengguna baru dengan menetapkan peran serta satuan kerja.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <form action="{{ isset($d_user) ? route('users.update', encryptor($d_user->id)) : route('users.store') }}" 
          method="post" 
          enctype="multipart/form-data" 
          id="user-form"
          data-parsley-validate>
        @csrf
        @if(isset($d_user))
            @method('PUT')
        @endif

        <div class="row g-4">
            <!-- Left Side: Profile Photo & Account Details -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow">
                    <div class="card-body p-4">
                        <h5 class="card-section-title text-dark mb-4">Informasi Akun & Akses</h5>

                        <!-- Profile Avatar Upload -->
                        <div class="form-group mb-4 text-center">
                            <div class="profile-avatar-container">
                                <img src="{{ (isset($d_user) && $d_user->profile && $d_user->profile->avatar) ? ($d_user->profile->media ? asset('storage/' . $d_user->profile->media->file_path . '/' . $d_user->profile->media->file_hash) : asset('storage/images/avatar/'. $d_user->profile->avatar)) : asset('assets/img/default-avatar.jpg') }}" 
                                     onerror="this.src='{{ asset('assets/img/default-avatar.jpg') }}';" 
                                     id="avatar-preview" 
                                     alt="Avatar Preview" 
                                     class="profile-avatar-preview">
                                <label for="uploadavatar" class="profile-avatar-upload-btn btn-blue" title="Ganti Foto">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" 
                                       name="avatar" 
                                       accept="image/png, image/gif, image/jpeg" 
                                       id="uploadavatar" 
                                       onchange="previewAvatar(this)" 
                                       class="d-none">
                            </div>
                            <small class="text-muted d-block mt-2">Mendukung format PNG, JPG, atau JPEG (Maks. 2MB)</small>
                        </div>

                        <div class="row">
                            <!-- Fullname -->
                            <div class="col-12 mb-3">
                                <label for="inputFullname" class="form-label fw-semibold text-secondary">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" 
                                           class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" 
                                           name="name" 
                                           id="inputFullname" 
                                           value="{{ old('name', $d_user->name ?? '') }}"
                                           placeholder="e.g. John Doe"
                                           required
                                           data-parsley-minlength="3"
                                           data-parsley-maxlength="100"
                                           data-parsley-required-message="Nama lengkap harus diisi."
                                           data-parsley-minlength-message="Nama minimal 3 karakter.">
                                </div>
                                @error('name')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- NIK (16 Digits check) -->
                            <div class="col-12 mb-3">
                                <label for="inputNik" class="form-label fw-semibold text-secondary">NIK (Nomor Induk Kependudukan) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-image"></i></span>
                                    <input type="text" 
                                           name="nik" 
                                           id="inputNik" 
                                           class="form-control border-start-0 ps-0 maskNIK @error('nik') is-invalid @enderror" 
                                           value="{{ old('nik', $d_user->profile->nik ?? '') }}"
                                           placeholder="16 Digit Angka NIK"
                                           required
                                           data-parsley-pattern="^\d{16}$"
                                           data-parsley-required-message="NIK wajib diisi."
                                           data-parsley-pattern-message="NIK harus tepat 16 digit angka.">
                                </div>
                                @error('nik')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-12 mb-3">
                                <label for="inputEmail" class="form-label fw-semibold text-secondary">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" 
                                           name="email" 
                                           id="inputEmail" 
                                           class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $d_user->email ?? '') }}"
                                           placeholder="e.g. johndoe@example.com"
                                           required
                                           data-parsley-type="email"
                                           data-parsley-required-message="Email address wajib diisi."
                                           data-parsley-type-message="Format email tidak valid.">
                                </div>
                                @error('email')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Roles -->
                            <div class="col-12 mb-3">
                                <label for="inputRole" class="form-label fw-semibold text-secondary">Akses Peran (Roles) <span class="text-danger">*</span></label>
                                <select name="role[]" 
                                        id="inputRole" 
                                        class="form-control @error('role') is-invalid @enderror" 
                                        multiple="multiple"
                                        required
                                        data-parsley-required-message="Pilih minimal satu peran sistem.">
                                    @foreach ($role as $value)
                                        <option value="{{ encryptor($value->id) }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Assignments & Personal Details -->
            <div class="col-lg-6 d-flex flex-column gap-4">
                <!-- Card 2: Assignments -->
                <div class="card border-0 shadow-sm rounded-4 hover-shadow">
                    <div class="card-body p-4">
                        <h5 class="card-section-title text-dark mb-4">Konfigurasi Penugasan</h5>

                        <div class="row">
                            <!-- Satuan Kerja -->
                            <div class="col-12 mb-3">
                                <label for="inputSatuanKerja" class="form-label fw-semibold text-secondary">Satuan Kerja <span class="text-danger">*</span></label>
                                <select name="satuanKerja[]" 
                                        id="inputSatuanKerja" 
                                        class="form-control @error('satuanKerja') is-invalid @enderror" 
                                        multiple="multiple"
                                        required
                                        data-parsley-required-message="Pilih minimal satu satuan kerja.">
                                    @foreach ($satuankerja as $value)
                                        <option value="{{ $value->satuan_hash }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @error('satuanKerja')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Tugas LHU (Conditionally Displayed) -->
                            <div class="col-12 mb-2 d-none" id="tugas_lhu" style="transition: all 0.3s ease;">
                                <label for="inputTugasLhu" class="form-label fw-semibold text-secondary">Tugas LHU</label>
                                <select name="tugas_lhu[]" 
                                        id="inputTugasLhu" 
                                        class="form-control @error('tugas_lhu') is-invalid @enderror" 
                                        multiple="multiple">
                                    @foreach ($jobs as $value)
                                        <option value="{{ $value->jobs_hash }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @error('tugas_lhu')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Personal Information -->
                <div class="card border-0 shadow-sm rounded-4 flex-grow-1 hover-shadow">
                    <div class="card-body p-4">
                        <h5 class="card-section-title text-dark mb-4">Informasi Pribadi</h5>

                        <div class="row">
                            <!-- Jenis Kelamin -->
                            <div class="col-md-6 mb-3">
                                <label for="inputJenisKelamin" class="form-label fw-semibold text-secondary">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" 
                                        id="inputJenisKelamin" 
                                        class="form-select rounded-3 @error('jenis_kelamin') is-invalid @enderror"
                                        required
                                        data-parsley-required-message="Jenis kelamin harus dipilih.">
                                    <option value="">--- Select ---</option>
                                    <option value="laki-laki" {{ old('jenis_kelamin', $d_user->profile->jenis_kelamin ?? '') == 'laki-laki' ? 'selected' : '' }}>Laki laki</option>
                                    <option value="perempuan" {{ old('jenis_kelamin', $d_user->profile->jenis_kelamin ?? '') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6 mb-3">
                                <label for="inputNoHp" class="form-label fw-semibold text-secondary">Nomor Telepon <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone-fill"></i></span>
                                    <input type="text" 
                                           name="no_telepon" 
                                           id="inputNoHp" 
                                           class="form-control border-start-0 ps-0 maskTelepon @error('no_telepon') is-invalid @enderror" 
                                           value="{{ old('no_telepon', $d_user->profile->no_hp ?? '') }}"
                                           placeholder="08xx-xxxx-xxxx"
                                           required
                                           data-parsley-pattern="^\d{10,13}$"
                                           data-parsley-required-message="Nomor telepon wajib diisi."
                                           data-parsley-pattern-message="Nomor telepon harus terdiri dari 10-13 digit angka.">
                                </div>
                                @error('no_telepon')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Alamat -->
                            <div class="col-12 mb-3">
                                <label for="inputAlamat" class="form-label fw-semibold text-secondary">Alamat Rumah</label>
                                <textarea name="alamat" 
                                          id="inputAlamat" 
                                          cols="30" 
                                          rows="3" 
                                          class="form-control rounded-3" 
                                          placeholder="Tulis alamat rumah lengkap di sini...">{{ old('alamat', $d_user->profile->alamat ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Setting (Only visible on Create mode) -->
        @if(!isset($d_user))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 hover-shadow">
                        <div class="card-body p-4">
                            <h5 class="card-section-title text-dark mb-3">Keamanan Kata Sandi</h5>
                            <p class="text-muted small mt-n1 mb-4">Buat kata sandi yang kuat untuk menjaga keamanan akun pengguna baru.</p>

                            <div class="row">
                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="inputPassword" class="form-label fw-semibold text-secondary">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" 
                                               name="password" 
                                               id="inputPassword" 
                                               class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror"
                                               required
                                               data-parsley-minlength="8"
                                               data-parsley-lowercase="true"
                                               data-parsley-uppercase="true"
                                               data-parsley-digit="true"
                                               data-parsley-required-message="Kata sandi harus diisi."
                                               data-parsley-minlength-message="Sandi harus minimal 8 karakter.">
                                    </div>
                                    <div class="mt-2 text-start">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Persyaratan Sandi:</small>
                                        <ul class="text-muted ps-3 mb-0" id="password-rules" style="font-size: 0.75rem; list-style-type: none; padding-left: 0 !important;">
                                            <!-- Rules will be validated by rules_password helper in setting.js -->
                                        </ul>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Retype Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="password-confirm" class="form-label fw-semibold text-secondary">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock-fill"></i></span>
                                        <input type="password" 
                                               name="password_confirmation" 
                                               id="password-confirm" 
                                               class="form-control border-start-0 ps-0"
                                               required
                                               data-parsley-equalto="#inputPassword"
                                               data-parsley-required-message="Ketik ulang kata sandi Anda."
                                               data-parsley-equalto-message="Konfirmasi password tidak sama.">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="row mt-4 mb-5">
            <div class="col-12 d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    @if(isset($d_user))
                        <button type="button" class="btn btn-outline-danger px-4 rounded-pill shadow-sm py-2 hover-scale transition-all" onclick="deleteUser()">
                            <i class="bi bi-trash3-fill me-2"></i> Hapus Pengguna
                        </button>
                    @endif
                </div>
                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('users.index') }}" class="btn btn-light px-4 rounded-pill py-2 shadow-sm border border-light-subtle">Batal</a>
                    <button type="submit" class="btn btn-primary px-5 rounded-pill py-2 shadow-sm hover-scale transition-all">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ isset($d_user) ? 'Perbarui Data' : 'Simpan Pengguna' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        const isEdit = @json(isset($d_user));
        const roleUser = @json(isset($d_user) ? $d_user->roles->map(function($role) { return encryptor($role->id); }) : []);
        const profile = @json(isset($d_user) ? $d_user->profile : null);
        const d_user = @json(isset($d_user) ? $d_user : null);

        $(function() {
            // Initialize select validation styling
            $('#inputRole').on('change', function(evt){
                let role = $('#inputRole').val();
                $('#tugas_lhu').addClass('d-none');
                $('#inputTugasLhu').val(null).trigger('change');
                
                if(role && role.length > 0) {
                    ajaxGet(`management/getPermisionInRole`, {'role' : role}, result => {
                        let data = result.data;
                        if(data.includes('Staff/lhu')) {
                            $('#tugas_lhu').removeClass('d-none').hide().fadeIn(300);
                            if (isEdit && d_user && d_user.jobs) {
                                $('#inputTugasLhu').val(d_user.jobs).trigger('change');
                            }
                        }
                    })
                }
            });

            if(profile?.jenis_kelamin){
                $('#inputJenisKelamin').val(profile.jenis_kelamin);
            }

            let arrSatuanId = [];
            if(d_user?.satuankerja) {
                arrSatuanId = d_user.satuankerja.map(function(item) {
                    return item.satuan_hash
                })
            }

            // Beautiful select2 inputs
            $('#inputSatuanKerja').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Satuan Kerja",
                allowClear: true
            }).val(arrSatuanId).trigger('change');

            $('#inputTugasLhu').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Tugas LHU",
                allowClear: true
            });

            $('#inputRole').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Akses Peran",
                allowClear: true
            });

            if (isEdit) {
                $('#inputRole').val(roleUser).trigger('change');
            }

            // Initialize rules for Password if in Create mode
            if (!isEdit) {
                rules_password('create', {
                    minLength: 8,
                    lowerCase: true,
                    upperCase: true,
                    digit: true
                }, '#password-rules', 'create');

                $('#inputPassword').on('input', function() {
                    rules_password('check', {
                        minLength: 8,
                        lowerCase: true,
                        upperCase: true,
                        digit: true
                    }, $(this).val(), 'create');
                });
            }

            // Real-time styling triggers on Parsley validate
            $('#user-form').parsley().on('field:validated', function(field) {
                const element = field.$element;
                // Special handling for Select2 components
                if (element.hasClass('select2-hidden-accessible')) {
                    const select2Container = element.next('.select2-container').find('.select2-selection');
                    if (field.isValid()) {
                        select2Container.removeClass('border-danger').addClass('border-success');
                    } else {
                        select2Container.removeClass('border-success').addClass('border-danger');
                    }
                } else {
                    if (field.isValid()) {
                        element.removeClass('is-invalid').addClass('is-valid');
                    } else {
                        element.removeClass('is-valid').addClass('is-invalid');
                    }
                }
            });
        });

        // Function
        function previewAvatar(obj){
            const file = obj.files[0];
            if(obj.files && file){
                const reader = new FileReader();
                const preview = document.getElementById('avatar-preview');

                reader.onload = function(e){
                    preview.src = e.target.result;
                }

                reader.readAsDataURL(file);
            }
        }

        function deleteUser() {
            if (!d_user) return;
            ajaxDelete(`management/users/${d_user.user_hash}`, (result) => {
                if (result.meta.code == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil dihapus',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '/management/users';
                    })
                }
            })
        }
    </script>
@endpush
