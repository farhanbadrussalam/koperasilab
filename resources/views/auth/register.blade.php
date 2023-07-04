@extends('layouts.app')

@section('content')
<div class="hold-transition register-page">
<div class="register-box">
  <div class="card shadow">
    <div class="card-header text-center">
      <span class="h1"><b>Koperasi</b>LAB</span>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Register a new akun</p>

      <form action="{{ route('register') }}" method="post">
        @csrf
        <div class="form-group mb-3">
          <input type="text" class="form-control @error('name')
            is-invalid
          @enderror" value="{{ old('name') }}" name="name" placeholder="Full name" autofocus>

          @error('name')
              <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
              </span>
          @enderror
        </div>
        <div class="form-group mb-3">
          <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Email">

          @error('email')
              <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
              </span>
          @enderror
        </div>
        <div class="form-group mb-3">
          <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password">
          @error('password')
              <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
              </span>
          @enderror
        </div>
        <div class="form-group mb-3">
          <input type="password" id="password-confirm" name="password_confirmation" class="form-control" placeholder="Retype password">

        </div>
        <div class="row mb-3">
          <!-- /.col -->
          <div class="col">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <a href="{{ route('login') }}" class="text-center">I already have a akun</a>
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
</div>
</div>
@endsection
