@extends('layouts.app')

@section('content')
    <div class="hold-transition login-page">
        <div class="login-box">
            <!-- /.login-logo -->
            <div class="card shadow">
                <div class="card-header text-center">
                    <span class="h1"><b>Koperasi</b>LAB</span>
                </div>
                <div class="card-body">
                    <p class="login-box-msg">Sign in to start your session</p>

                    <form action="{{ route('login') }}" method="post">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                id="email" placeholder="Email" value="{{ old('email') }}" autofocus>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </div>
                            </div>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" id="password" placeholder="Password" value="{{ old('password') }}">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </div>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">

                            <!-- /.col -->
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>

                    <div class="social-auth-links text-center mt-2 mb-3">
                        <a href="{{ route('google.redirect') }}" class="btn btn-block btn-danger">
                            <i class="bi bi-google"></i> Sign in using Google
                        </a>
                    </div>
                    <!-- /.social-auth-links -->

                    <!-- <p class="mb-1">
                <a href="{{ route('password.request') }}">I forgot my password</a>
              </p> -->
                    <p class="mb-0">
                        <a href="{{ route('register') }}" class="text-center">Register a new akun</a>
                    </p>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
