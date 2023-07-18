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

      <form action="{{ route('register') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group mb-3">
          <div class="text-center">
            <div class="box-profile my-2">
              <a href="#" onclick="selectFileImage()">
                <img src="{{ asset('assets/img/default-avatar.jpg') }}" id="avatar" alt="Avatar" class="profile-user-img img-fluid img-circle" style="width: 100px;height: 100px;">
              </a>
              <input type="file" name="avatar" accept="image/png, image/gif, image/jpeg" id="uploadavatar" onchange="previewAvatar(this)" hidden>
            </div>
          </div>
        </div>
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
          <input type="number" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}" placeholder="NIK">

          @error('nik')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
          @enderror
        </div>
        <div class="form-group mb-3">
          <input type="number" name="no_telepon" id="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror" value="{{ old('no_telepon') }}" placeholder="Nomer Telepon">

          @error('no_telepon')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
          @enderror
        </div>
        <div class="form-group mb-3">
          <select name="jenis_kelamin" id="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror">
            <option value="">Jenis kelamin</option>
            <option value="laki-laki">Laki-laki</option>
            <option value="perempuan">Perempuan</option>
          </select>

          @error('jenis_kelamin')
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
<script>
  function selectFileImage() {
    let _uploadfile = document.getElementById('uploadavatar');
    _uploadfile.click();
  }

  function previewAvatar(obj){
    const file = obj.files[0];
    if(obj.files && file){
      const reader = new FileReader();
      const preview = document.getElementById('avatar');

      reader.onload = function(e){
        preview.src = e.target.result;
      }

      reader.readAsDataURL(file);
    }
  }
</script>
@endsection
