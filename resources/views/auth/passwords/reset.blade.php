@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">

<div class="row custom-container">
    <div class="d-none d-md-block col-md-5 col-lg-7">
        <div class="w-100 h-100">
            <img class="w-100 vh-100" src="{{ asset('/images/backgrounds/background_login.svg') }}" alt="" />
        </div>
    </div>
    <div class="col-12 col-md-7 col-lg-5">
        <div class="d-flex flex-column vh-100">
            <div class="d-flex justify-content-center align-items-center flex-fill">
                <div class="text-center border rounded-4 shadow p-4" style="width: 100%; max-width: 420px; background-color: #ffffff;">
                    <h4 class="mt-2"><b>NuklindoLab</b> Koperasi JKRL</h4>
                    <div class="text-grey mb-4">Atur Ulang Kata Sandi Anda</div>

                    <div class="alert alert-light border-start border-4 border-info shadow-sm mb-4 text-start">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                            <div>
                                <small class="text-muted d-block fw-bold">Amankan akun Anda</small>
                                <small class="text-muted" style="font-size: 0.8rem;">Gunakan kombinasi huruf besar, kecil, dan angka agar password sulit ditebak.</small>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" id="form-reset-password" novalidate>
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3 text-start">
                            <label for="email" class="form-label text-main body-medium">E-mail</label>
                            <div class="input-group">
                                <div class="input-group-text border-0 bg-body-secondary">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <input
                                    id="email"
                                    type="email"
                                    class="form-control px-3 @error('email') is-invalid @enderror"
                                    name="email"
                                    value="{{ $email ?? old('email') }}"
                                    placeholder="E-mail"
                                    required autocomplete="email" autofocus />
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold text-muted">Password Baru <span class="fw-bold fs-14 text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    class="form-control form-control input-login @error('password') is-invalid @enderror"
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    data-parsley-trigger="input"
                                    data-parsley-minlength="8"
                                    data-parsley-lowercase="true"
                                    data-parsley-uppercase="true"
                                    data-parsley-errors-container="#error-new-password"
                                    placeholder="Enter your new password" />
                                <button type="button" class="input-group-text btn border-start-0 border" onclick="showPasswordInput(this, 'password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-danger" id="strengthBar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <ul id="password-rules" class="p-3 bg-light rounded-3 mt-2 text-start" style="font-size: 0.85rem; list-style-type: none;">
                            </ul>
                            <div class="invalid-feedback d-none" id="error-new-password"></div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 text-start">
                            <label class="form-label small fw-bold text-muted">Konfirmasi Password <span class="fw-bold fs-14 text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    class="form-control form-control input-login"
                                    id="password-confirm"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    data-parsley-trigger="input"
                                    data-parsley-equalto="#password"
                                    data-parsley-equalto-message="Konfirmasi password tidak sama."
                                    data-parsley-required-message="Konfirmasi password wajib diisi."
                                    data-parsley-errors-container="#error-confirm-password"
                                    placeholder="Enter your confirm password" />
                                <button type="button" class="input-group-text btn border-start-0 border" onclick="showPasswordInput(this, 'password-confirm')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback d-block" id="error-confirm-password"></div>
                        </div>

                        <button class="btn btn-primary w-100 mb-3" type="submit">Atur Ulang Kata Sandi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showPasswordInput(obj, inputId) {
        var x = document.getElementById(inputId);
        var icon = obj.querySelector('i');
        if (x.type === "password") {
            x.type = "text";
            icon.className = 'bi bi-eye-slash';
        } else {
            x.type = "password";
            icon.className = 'bi bi-eye';
        }
    }

    $(function() {
        const rulesPassword = {
            minLength: 8,
            lowerCase: true,
            upperCase: true,
        };

        // Initialize parsley on the form
        $('#form-reset-password').parsley({
            trigger: 'input'
        });

        // Initialize rules list
        rules_password('create', rulesPassword, '#password-rules', '2');

        // Monitor password strength on input
        $('#password').on('input', function() {
            const val = $(this).val();
            let cek = rules_password('update', rulesPassword, val, '2');
            $('#strengthBar').css('width', cek.percentage + '%');
            document.getElementById('strengthBar').className = 'progress-bar ' + cek.backgroundColor;
        });
    });
</script>
@endsection
