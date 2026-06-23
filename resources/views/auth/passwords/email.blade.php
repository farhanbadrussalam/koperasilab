@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset_versioned('css/auth/login.css') }}">

    <div class="row custom-container">
        <div class="d-none d-md-block col-md-5 col-lg-7">
            <div class="w-100 h-100">
                <img class="w-100 vh-100" src="{{ asset('/images/backgrounds/background_login.svg') }}" alt="" />
            </div>
        </div>
        <div class="col-12 col-md-7 col-lg-5">
            <div class="d-flex flex-column vh-100">
                <div class="d-flex justify-content-center align-items-center flex-fill">
                    <div class="text-center border rounded-4 shadow p-4"
                        style="width: 100%; max-width: 420px; background-color: #ffffff;">
                        <h4 class="mt-2"><b>NuklindoLab</b> Koperasi JKRL</h4>
                        <div class="text-grey mb-4">Lupa Kata Sandi? Masukkan email Anda untuk menerima link pemulihan.
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show text-start mb-3" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('password.email') }}" method="post">
                            @csrf
                            <div class="mb-4 text-start">
                                <label for="email" class="form-label text-main body-medium">E-mail</label>
                                <div class="input-group">
                                    <div class="input-group-text border-0 bg-body-secondary">
                                        <i class="bi bi-envelope"></i>
                                    </div>
                                    <input type="email" class="form-control px-3 @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}"
                                        placeholder="Masukkan email terdaftar" required autofocus />
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-primary w-100 mb-3" type="submit">Kirim Link Pemulihan</button>

                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-decoration-none"
                                    style="color: #0d6efd; font-size: 14px; font-weight: 500;">Kembali ke Halaman Masuk</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
