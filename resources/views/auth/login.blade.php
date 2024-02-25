@extends('layouts.app')

@section('content')
<div class="container-fluid row">
    <div class="d-none d-md-flex col-md-6 col-lg-7 px-0">
        <div class="w-100 h-100 ms-5">
            <img class="w-100 vh-100" src="{{ asset('/images/backgrounds/background_login.svg') }}" alt="" style="object-fit: contain" />
        </div>
    </div>
    <div class="col-md-6 col-lg-5">
        <div style="height: 100vh" class="d-flex flex-column">
            <div class="d-flex justify-content-center align-items-center flex-fill">
                <div class="text-center">
                    <h4 class="mt-2"><b>NuklindoLab</b> Koperasi JKRL</h4>
                    <div class="text-grey">You need to be logged in to access</div>
                    <form action="{{ route('login') }}" method="post">
                        @csrf
                        <div class="mb-3 mt-4 text-start" style="width: 360px">
                            <label for="input_nik" class="form-label text-main body-medium">NIK</label>
                            <div class="input-group mb-1" style="border-radius: 20px">
                                <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <input
                                    type="text"
                                    class="form-control px-3 @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Email" autofocus />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4 text-start" style="width: 360px">
                                <label for="input_password" class="form-label text-main body-medium">Password</label>
                                <div class="input-group mb-2 mt-1" style="border-radius: 20px">
                                    <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1">
                                        <i class="bi bi-lock-fill"></i>
                                    </div>
                                    <input
                                        class="form-control form-control input-login @error('password') is-invalid @enderror"
                                        id="input_password"
                                        type="password"
                                        name="password"
                                        value="{{ old('password') }}"
                                        placeholder="Enter your password" />
                                    <div class="input-group-text border-0 bg-body-secondary" id="basic-addon1">
                                        <i class="bi bi-eye"></i>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                {!! NoCaptcha::renderJs() !!}
                                {!! NoCaptcha::display() !!}
                                @if ($errors->has('g-recaptcha-response'))
                                    <span class="help-block text-danger">
                                        <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <button class="btn btn-primary" style="width: 360px;" type="submit">Login</button>
                            <div class="social-auth-links text-center mt-2 mb-3">
                                <a href="{{ route('google.redirect') }}" class="btn btn-block btn-danger">
                                    <i class="bi bi-google"></i> Sign in using Google
                                </a>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('register') }}" class="text-center">Register a new akun</a>
                            </div>
                        </div>
                    </form>
                </div>
            <div>
        </div>
    </div>
</div>
@endsection
