@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/auth/registrasi.css') }}">

    <div class="container-fluid">
        <h1 class="fw-bold m-4 text-center">Registration</h1>
        <div class="registration-form" id="cek-akun-form">
            <div class="row mt-3">
                <div class="col-md-12 mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control maskNIK" placeholder="Masukan NIK Anda" autocomplete="false" id="input-cek-akun">
                        <button type="button" class="btn btn-primary" onclick="searchAkun(this)" id="btn-cek-akun">Cari</button>
                    </div>
                </div>
                <div class="col-md-12">
                    <div><a class="btn btn-danger" href="{{ route('login') }}">Kembali</a></div>
                    <div class="alert alert-info mt-2 d-none" role="alert" id="alert-cek-akun"></div>
                </div>
            </div>
        </div>
        <div class="registration-form d-none" id="registration-form">
            <form action="{{ route('register') }}" method="post" enctype="multipart/form-data" id="form-registration">
                @csrf
                <div class="border position-relative rounded my-4 p-3 mx-xl-5">
                    <span class="position-absolute top-0 start-50 translate-middle bg-white px-2 fs-5">
                        Data PIC
                    </span>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-2">
                            <label for="nik" class="form-label">NIK <span class="fw-bold fs-14 text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="input-nik" name="nik" placeholder="" autocomplete="true" readonly data-parsley-minlength-message="NIK minimal 16 karakter" minlength="16">
                                <button type="button" class="btn btn-outline-secondary" onclick="changeNik()">Ganti</button>
                            </div>
                            <div id="message-input-nik" class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="email" class="form-label">Email <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="email" class="form-control maskEmail" id="input-email" name="email" autocomplete="true" required
                                data-parsley-required-message="Email harus diisi"
                                data-parsley-errors-container="#email_error">
                            <div id="email_error" class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="nama_pic" class="form-label">Nama <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_pic" name="nama_pic" required data-parsley-required-message="Nama harus diisi">
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
                            <input type="text" class="form-control maskTelepon" id="telepon" name="telepon" required data-parsley-required-message="Telepon harus diisi">
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="fw-bold fs-14 text-danger">*</span></label>
                            <textarea name="alamat" id="alamat" cols="30" rows="5" class="form-control" required data-parsley-required-message="Alamat harus diisi"></textarea>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="password" class="form-label">Password <span class="fw-bold fs-14 text-danger">*</span></label>
                            <div class="input-group mb-2 mt-1">
                                <input type="password" class="form-control" id="input-password" name="password" required
                                    data-parsley-required-message="Password harus diisi"
                                    data-parsley-trigger="input"
                                    data-parsley-minlength="8"
                                    data-parsley-lowercase="true"
                                    data-parsley-uppercase="true"
                                    data-parsley-errors-container="#password_error">
                                <div class="input-group-text border-0 bg-body-secondary" onclick="showPassword(this)">
                                    <i class="bi bi-eye"></i>
                                </div>
                            </div>
                            <div id="password_error" class="invalid-feedback d-none"></div>
                            <ul id="password-rules" class="mt-2 small ps-0" style="list-style: none;"></ul>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="fw-bold fs-14 text-danger">*</span></label>
                            <div class="input-group mb-2 mt-1">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required
                                    data-parsley-trigger="input"
                                    data-parsley-equalto="#input-password"
                                    data-parsley-equalto-message="Konfirmasi password tidak sama."
                                    data-parsley-required-message="Konfirmasi password wajib diisi."
                                    data-parsley-errors-container="#konfirm_password_error">
                                <div class="input-group-text border-0 rounded-end bg-body-secondary" onclick="showPassword(this)">
                                    <i class="bi bi-eye"></i>
                                </div>
                            </div>
                            <div id="konfirm_password_error" class="invalid-feedback d-block"></div>
                        </div>
                    </div>
                </div>
                <div class="border rounded my-4 p-3 mx-xl-5 position-relative">
                    <span class="position-absolute top-0 start-50 translate-middle bg-white px-2 fs-5">
                        Data Instansi
                    </span>
                    <div class="row mt-3">
                        <div class="col-md-12 mb-2">
                            <label for="nama_instansi" class="form-label">Nama Instansi <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="hidden" name="type_instansi" id="type_instansi">
                            <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" required data-parsley-required-message="Nama instansi harus diisi">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="email_instansi" class="form-label">Email instansi <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="email" class="form-control maskEmail" id="email_instansi" name="email_instansi" required
                                data-parsley-required-message="Email instansi harus diisi"
                                data-parsley-errors-container="#email_instansi_error">
                            <div id="email_instansi_error" class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="npwp" class="form-label">NPWP <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control maskNPWP" id="npwp" name="npwp" required data-parsley-required-message="NPWP harus diisi">
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="kode_pos" class="form-label">Kode Pos <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control maskNumber" id="kode_pos" name="kode_pos" placeholder="" autocomplete="true" required data-parsley-required-message="Kode Pos harus diisi">
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="fw-bold fs-14 text-danger">*</span></label>
                            <textarea name="alamat_instansi" id="alamat_instansi" cols="30" rows="5" class="form-control" required data-parsley-required-message="Alamat instansi harus diisi"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="uploadSuratKuasa" class="form-label">Surat Kuasa <span class="fw-bold fs-14 text-danger">*</span></label>
                            <div id="uploadSuratKuasa"></div>
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
                        <button class="btn btn-primary mx-3" onclick="simpan()" type="button">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth/register.js') }}"></script>
@endpush
