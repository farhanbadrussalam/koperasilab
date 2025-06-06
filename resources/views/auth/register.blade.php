@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/auth/registrasi.css') }}">

    <div class="container-fluid">
        <div class="registration-form">
            <h1 class="fw-bold mb-4 mx-xl-5">Registration</h1>

            <form action="{{ route('register') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="border rounded my-4 p-3 mx-xl-5 position-relative">
                    <span class="position-absolute top-0 start-50 translate-middle bg-white px-2 fs-5">
                        Data Instansi
                    </span>
                    <div class="row mt-3">
                        <div class="col-md-12 mb-2">
                            <label for="nama_instansi" class="form-label">Nama Instansi <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="hidden" name="type_instansi" id="type_instansi">
                            <select name="nama_instansi" class="form-select @error('nama_instansi') is-invalid @enderror" id="nama_instansi"></select>
                            @error('nama_instansi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="email_instansi" class="form-label">Email instansi <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="email" class="form-control maskEmail @error('email_instansi') is-invalid @enderror" id="email_instansi" name="email_instansi" placeholder="" autocomplete="true" value="{{ old('email_instansi') }}">
                            @error('email_instansi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control maskNPWP" id="npwp" name="npwp" placeholder="">
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="kode_pos" class="form-label">Kode Pos <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control maskNumber {{ $errors->has('kode_pos') ? 'is-invalid' : '' }}" id="kode_pos" name="kode_pos" placeholder="" autocomplete="true" value="{{ old('kode_pos') }}">
                            @error('kode_pos')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="fw-bold fs-14 text-danger">*</span></label>
                            <textarea name="alamat_instansi" id="alamat_instansi" cols="30" rows="5" class="form-control {{ $errors->has('alamat_instansi') ? 'is-invalid' : '' }}">{{ old('alamat_instansi') }}</textarea>
                            @error('alamat_instansi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="border position-relative rounded my-4 p-3 mx-xl-5">
                    <span class="position-absolute top-0 start-50 translate-middle bg-white px-2 fs-5">
                        Data PIC
                    </span>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-2">
                            <label for="nik" class="form-label">NIK <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control maskNIK {{ $errors->has('nik') ? 'is-invalid' : '' }}" id="nik" name="nik" placeholder="" autocomplete="true" value="{{ old('nik') }}">
                            @error('nik')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="email" class="form-label">Email <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="email" class="form-control maskEmail {{ $errors->has('email') ? 'is-invalid' : '' }}" id="email" name="email" placeholder="" autocomplete="true" value="{{ old('email') }}">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="nama_pic" class="form-label">Nama <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('nama_pic') ? 'is-invalid' : '' }}" id="nama_pic" name="nama_pic" placeholder="" value="{{ old('nama_pic') }}">
                            @error('nama_pic')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="jabatan_pic" class="form-label">Jabatan</label>
                            <input type="text" class="form-control {{ $errors->has('jabatan_pic') ? 'is-invalid' : '' }}" id="jabatan_pic" name="jabatan_pic" placeholder="" value="{{ old('jabatan_pic') }}">
                            @error('jabatan_pic')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="fw-bold fs-14 text-danger">*</span></label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
                                <option value="laki-laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="telepon" class="form-label">Telepon <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control maskTelepon {{ $errors->has('telepon') ? 'is-invalid' : '' }}" id="telepon" name="telepon" placeholder="" value="{{ old('telepon') }}">
                            @error('telepon')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="fw-bold fs-14 text-danger">*</span></label>
                            <textarea name="alamat" id="alamat" cols="30" rows="5" class="form-control {{ $errors->has('alamat') ? 'is-invalid' : '' }}">{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="password" class="form-label">Password <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" id="password" name="password" placeholder="" value="{{ old('password') }}">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="">
                        </div>
                    </div>
                </div>
                <div class="g-3 mx-lg-5">
                    <div class="form-group mb-2">
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
