@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/auth/registrasi.css') }}">

    <div class="container-fluid">
        <div class="registration-form">
            <h1 class="fw-bold text-center mb-4">Registration</h1>

            <form action="{{ route('register') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 mx-lg-5">
                    <div class="col-md-6">
                        <label for="nama_instansi" class="form-label">Nama Instansi</label>
                        <input type="hidden" name="type_instansi" id="type_instansi">
                        <select name="nama_instansi" class="form-select @error('nama_instansi') is-invalid @enderror" id="nama_instansi"></select>
                        @error('nama_instansi')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email_instansi" class="form-label">Email instansi</label>
                        <input type="email" class="form-control" id="email_instansi" name="email_instansi" placeholder="" autocomplete="true">
                    </div>
                    <div class="col-md-6">
                        <label for="nik" class="form-label">NIK</label>
                        <input type="text" class="form-control maskNIK" id="nik" name="nik" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="npwp" class="form-label">NPWP</label>
                        <input type="text" class="form-control maskNPWP" id="npwp" name="npwp" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="" autocomplete="true">
                    </div>
                    <div class="col-md-6">
                        <label for="kode_pos" class="form-label">Kode Pos</label>
                        <input type="text" class="form-control" id="kode_pos" name="kode_pos" placeholder="" autocomplete="true">
                    </div>
                    <div class="col-md-6">
                        <label for="nama_pic" class="form-label">Nama PIC</label>
                        <input type="text" class="form-control" id="nama_pic" name="nama_pic" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="jabatan_pic" class="form-label">Jabatan PIC</label>
                        <input type="text" class="form-control" id="jabatan_pic" name="jabatan_pic" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Retype password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control maskTelepon" id="telepon" name="telepon" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat Instansi</label>
                        <textarea name="alamat" id="alamat" cols="30" rows="5" class="form-control"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        {!! NoCaptcha::renderJs() !!}
                        {!! NoCaptcha::display() !!}
                        @if ($errors->has('g-recaptcha-response'))
                            <span class="help-block text-danger">
                                <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="mb-3 d-flex justify-content-center">
                        <a class="btn btn-danger mx-3" href="{{ route('login') }}">Kembali</a>
                        <button class="btn btn-primary mx-3">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth/register.js') }}"></script>
@endpush