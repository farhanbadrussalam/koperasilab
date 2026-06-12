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
                    <div class="text-center border rounded-4 shadow p-4">
                        <h4 class="mt-2"><b>NuklindoLab</b> Koperasi JKRL</h4>
                        <div class="text-grey">Anda perlu login untuk mengakses</div>
                        <form action="{{ route('login') }}" method="post">
                            @csrf
                            <div class="mb-3 text-start">
                                <label for="input_nik" class="form-label text-main body-medium">E-mail</label>
                                <div class="input-group">
                                    <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1">
                                        <i class="bi bi-envelope"></i>
                                    </div>
                                    <input type="text" class="form-control px-3 @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}" placeholder="E-mail"
                                        autofocus />
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4 text-start">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="input_password"
                                        class="form-label text-main body-medium mb-0">Password</label>
                                </div>
                                <div class="input-group mb-2">
                                    <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1">
                                        <i class="bi bi-lock-fill"></i>
                                    </div>
                                    <input
                                        class="form-control form-control input-login @error('password') is-invalid @enderror"
                                        id="input_password" type="password" name="password"
                                        value="{{ env('APP_ENV') != 'production' ? old('password') ?? env('DEFAULT_PASSWORD') : old('password') }}"
                                        placeholder="Masukkan kata sandi Anda" />
                                    <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1"
                                        onclick="showPassword(this)">
                                        <i class="bi bi-eye"></i>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('password.request') }}" class="text-decoration-none"
                                        style="color: #0d6efd; font-size: 14px; font-weight: 500;">Lupa password?</a>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            @if (env('APP_ENV') == 'production')
                                <div class="form-group">
                                    {!! NoCaptcha::renderJs() !!}
                                    {!! NoCaptcha::display() !!}
                                    @if ($errors->has('g-recaptcha-response'))
                                        <span class="help-block text-danger">
                                            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            @endif
                            <button class="btn btn-primary" style="width: 360px;" type="submit">Masuk</button>
                            <div class="social-auth-links text-center mt-2 mb-3">
                                <a href="{{ route('google.redirect') }}" class="btn btn-block btn-danger">
                                    <i class="bi bi-google"></i> Masuk menggunakan Google
                                </a>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('register') }}" class="text-center">Daftar akun baru</a>
                            </div>
                            <div class="text-center mt-3 small text-muted">
                                Dengan masuk, Anda menyetujui
                                <div>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold"
                                        onclick="showTermsModal(event)">Syarat &amp; Ketentuan</a>
                                    penggunaan layanan kami.
                                </div>
                            </div>
                        </form>
                    </div>
                    <div>
                    </div>
                </div>
            </div>

            {{-- Modal Terms & Conditions --}}
            <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                        {{-- Header --}}
                        <div class="modal-header border-0 pb-0"
                            style="background: linear-gradient(135deg, #1a3a6b 0%, #0d6efd 100%);">
                            <div class="py-2">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
                                        style="width:36px;height:36px;">
                                        <i class="bi bi-shield-check text-white fs-5"></i>
                                    </div>
                                    <h5 class="modal-title text-white fw-bold mb-0" id="termsModalLabel">Syarat &amp;
                                        Ketentuan Penggunaan</h5>
                                </div>
                                <p class="text-white text-opacity-75 small mb-0">NuklindoLab &mdash; Koperasi JKRL</p>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"
                                aria-label="Tutup"></button>
                        </div>

                        {{-- Body --}}
                        <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">

                            <div class="alert alert-primary d-flex align-items-start gap-2 rounded-3 mb-4" role="alert">
                                <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                                <div class="small">
                                    Harap baca syarat dan ketentuan berikut dengan seksama sebelum menggunakan layanan
                                    <strong>NuklindoLab Koperasi JKRL</strong>.
                                </div>
                            </div>

                            {{-- Pasal 1 --}}
                            <div class="terms-section mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-pill">Pasal 1</span>
                                    <h6 class="fw-bold mb-0 text-dark">Definisi dan Ruang Lingkup</h6>
                                </div>
                                <div class="ps-2 text-muted small">
                                    <p class="mb-2">Aplikasi <strong>NuklindoLab Koperasi JKRL</strong> adalah sistem
                                        informasi manajemen layanan laboratorium yang dikelola oleh Koperasi JKRL. Layanan
                                        ini digunakan untuk pengelolaan permohonan pengujian, pengelolaan kontrak, serta
                                        administrasi keanggotaan koperasi.</p>
                                    <p class="mb-0">Dengan mengakses dan menggunakan aplikasi ini, Anda (pengguna)
                                        menyatakan telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang
                                        berlaku.</p>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Pasal 2 --}}
                            <div class="terms-section mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-pill">Pasal 2</span>
                                    <h6 class="fw-bold mb-0 text-dark">Syarat Penggunaan Akun</h6>
                                </div>
                                <ul class="text-muted small ps-3 mb-0">
                                    <li class="mb-2">Pengguna wajib mendaftarkan diri dengan data yang valid, termasuk
                                        NIK, e-mail, dan informasi instansi yang benar.</li>
                                    <li class="mb-2">Setiap akun bersifat pribadi dan tidak boleh dipindahtangankan atau
                                        digunakan oleh pihak lain.</li>
                                    <li class="mb-2">Pengguna bertanggung jawab penuh atas kerahasiaan kata sandi akun
                                        miliknya.</li>
                                    <li class="mb-0">Penyalahgunaan akun dapat mengakibatkan penonaktifan atau
                                        penghapusan akun tanpa pemberitahuan sebelumnya.</li>
                                </ul>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Pasal 3 --}}
                            <div class="terms-section mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-pill">Pasal 3</span>
                                    <h6 class="fw-bold mb-0 text-dark">Layanan dan Permohonan Pengujian</h6>
                                </div>
                                <ul class="text-muted small ps-3 mb-0">
                                    <li class="mb-2">Permohonan pengujian laboratorium diajukan melalui sistem ini dan
                                        tunduk pada jadwal serta kapasitas laboratorium.</li>
                                    <li class="mb-2">Setiap permohonan yang telah disetujui dan diproses bersifat
                                        mengikat secara administratif.</li>
                                    <li class="mb-2">Pengguna wajib melengkapi dokumen pendukung yang dipersyaratkan
                                        sesuai jenis layanan yang dimohon.</li>
                                    <li class="mb-0">Hasil pengujian bersifat rahasia dan hanya dapat diakses oleh
                                        pengguna yang bersangkutan dan pihak Koperasi JKRL.</li>
                                </ul>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Pasal 4 --}}
                            <div class="terms-section mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-pill">Pasal 4</span>
                                    <h6 class="fw-bold mb-0 text-dark">Kewajiban dan Tanggung Jawab Pengguna</h6>
                                </div>
                                <ul class="text-muted small ps-3 mb-0">
                                    <li class="mb-2">Pengguna dilarang menggunakan sistem ini untuk tujuan yang melanggar
                                        hukum, termasuk pemalsuan data dan manipulasi dokumen.</li>
                                    <li class="mb-2">Pengguna wajib mematuhi seluruh kebijakan dan prosedur yang
                                        ditetapkan oleh Koperasi JKRL.</li>
                                    <li class="mb-2">Setiap tindakan yang merugikan sistem atau pengguna lain dapat
                                        dikenakan sanksi sesuai ketentuan yang berlaku.</li>
                                    <li class="mb-0">Pengguna bertanggung jawab atas keabsahan dokumen dan data yang
                                        diunggah ke dalam sistem.</li>
                                </ul>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Pasal 5 --}}
                            <div class="terms-section mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-pill">Pasal 5</span>
                                    <h6 class="fw-bold mb-0 text-dark">Kerahasiaan dan Perlindungan Data</h6>
                                </div>
                                <div class="ps-2 text-muted small">
                                    <p class="mb-2">Data pribadi pengguna yang dikumpulkan melalui sistem ini akan dijaga
                                        kerahasiaannya sesuai dengan peraturan perundang-undangan yang berlaku di Indonesia,
                                        termasuk ketentuan mengenai perlindungan data pribadi.</p>
                                    <p class="mb-0">Data pengguna tidak akan disebarluaskan kepada pihak ketiga tanpa
                                        persetujuan pengguna, kecuali diwajibkan oleh hukum atau regulasi yang berlaku.</p>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Pasal 6 --}}
                            <div class="terms-section mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-pill">Pasal 6</span>
                                    <h6 class="fw-bold mb-0 text-dark">Perubahan Syarat &amp; Ketentuan</h6>
                                </div>
                                <div class="ps-2 text-muted small">
                                    <p class="mb-0">Koperasi JKRL berhak mengubah syarat dan ketentuan ini sewaktu-waktu.
                                        Perubahan akan diinformasikan melalui sistem kepada seluruh pengguna. Penggunaan
                                        aplikasi secara berkelanjutan setelah adanya perubahan dianggap sebagai persetujuan
                                        atas perubahan tersebut.</p>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Pasal 7 --}}
                            <div class="terms-section">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-pill">Pasal 7</span>
                                    <h6 class="fw-bold mb-0 text-dark">Hukum yang Berlaku</h6>
                                </div>
                                <div class="ps-2 text-muted small">
                                    <p class="mb-0">Syarat dan ketentuan ini diatur dan ditafsirkan berdasarkan hukum
                                        Republik Indonesia. Setiap sengketa yang timbul akan diselesaikan melalui
                                        musyawarah, dan apabila tidak tercapai kesepakatan, akan diselesaikan melalui jalur
                                        hukum yang berlaku.</p>
                                </div>
                            </div>

                        </div>

                        {{-- Footer --}}
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <div class="w-100">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        Berlaku sejak: 1 Januari 2025 &bull; Versi 1.0
                                    </small>
                                    <button type="button" class="btn btn-primary rounded-pill px-4"
                                        data-bs-dismiss="modal">
                                        <i class="bi bi-check-circle me-1"></i> Saya Mengerti
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <script>
                @if (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: '{{ session('error') }}',
                        showConfirmButton: false,
                        timer: 1500
                    })
                @endif

                function showPassword(obj) {
                    var x = document.getElementById("input_password");
                    if (x.type === "password") {
                        x.type = "text";
                        obj.innerHTML = '<i class="bi bi-eye-slash"></i>';
                    } else {
                        x.type = "password";
                        obj.innerHTML = '<i class="bi bi-eye"></i>';
                    }
                }

                function showTermsModal(event) {
                    event.preventDefault();
                    var termsModal = new bootstrap.Modal(document.getElementById('termsModal'), {
                        keyboard: true,
                        backdrop: true
                    });
                    termsModal.show();
                }
            </script>
        @endsection
