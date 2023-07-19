@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-8 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h2 class="card-title flex-grow-1">
                        Create Users
                    </h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="post">
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
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="inputFullname" class="form-label">Full name</label>
                                <input type="text" class="form-control" id="inputFullname">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputNik" class="form-label">NIK</label>
                                <input type="number" name="nik" id="inputNik" class="form-control">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputNoHp" class="form-label">Nomer Telepon</label>
                                <input type="number" name="no_telepon" id="inputNoHp" class="form-control">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputJenisKelamin" class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="inputJenisKelamin" class="form-control">
                                    <option value="">--- Select ---</option>
                                    <option value="laki-laki">Laki laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputRole" class="form-label">Role</label>
                                <select name="role" id="inputRole" class="form-control">
                                    <option value="">--- Select ---</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputSatuanKerja" class="form-label">Satuan Kerja</label>
                                <select name="satuanKerja" id="inputSatuanKerja" class="form-control">
                                    <option value="">--- Select ---</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="inputEmail" class="form-label">Email</label>
                                <input type="email" name="email" id="inputEmail" class="form-control">
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="inputAlamat" class="form-label">Alamat</label>
                                <textarea name="alamat" id="inputAlamat" cols="30" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputPassword" class="form-label">Password</label>
                                <input type="password" name="password" id="inputPassword" class="form-control">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="password-confirm" class="form-label">Retype password</label>
                                <input type="password" name="password_confirmation" id="password-confirm" class="form-control">
                            </div>
                            <div class="col-md-12 mt-3 text-center">
                                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection