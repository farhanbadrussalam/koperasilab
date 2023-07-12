@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('userProfile.index') }}">Profile</a></li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container">
                <div class="row">
                    <div class="card col-xl-6 col-md-12">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 text-center fw-bolder border-bottom">
                                    <h2>Biodata pribadi</h2>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="box-profile my-2">
                                            <img class="profile-user-img img-fluid img-circle"
                                                src="{{ asset('storage/images/avatar/' . Auth::user()->profile->avatar) }}"
                                                alt="User profile picture" style="width: 15em;">
                                        </div>
                                        <h4>{{ Auth::user()->getRoleNames()[0] }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-8 mt-sm-3">
                                    <form action="{{ route('userProfile.update', Auth::user()->profile->id) }}" method="post">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-md-4 col-form-label">Nama
                                                lengkap </label>
                                            <div class="col-sm-9 col-md-8">
                                                <input type="text" name="name" class="form-control" id="inputName"
                                                    value="{{ old('name') ? old('name') : Auth::user()->name }}" readonly>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputNik" class="col-sm-3 col-md-4 col-form-label">NIK</label>
                                            <div class="col-sm-9 col-md-8">
                                                <input type="number" name="nik"
                                                    class="form-control @error('nik') is-invalid @enderror" id="inputNik"
                                                    value="{{ old('nik') ? old('nik') : Auth::user()->profile->nik }}" readonly>
                                                @error('nik')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputEmail" class="col-sm-3 col-md-4 col-form-label">Email</label>
                                            <div class="col-sm-9 col-md-8">
                                                <input type="email" name="email"
                                                    class="form-control @error('email') is-invalid @enderror" id="inputEmail"
                                                    value="{{ old('email') ? old('email') : Auth::user()->email }}" readonly>
                                                @error('email')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputNomer" class="col-sm-3 col-md-4 col-form-label">No
                                                Telepon</label>
                                            <div class="col-sm-9 col-md-8">
                                                <input type="number" name="telepon"
                                                    class="form-control @error('telepon') is-invalid @enderror" id="inputNomer"
                                                    value="{{ old('telepon') ? old('telepon') : Auth::user()->profile->no_hp }}"
                                                    readonly>
                                                @error('telepon')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputJeniskelamin" class="col-sm-3 col-md-4 col-form-label">Jenis
                                                Kelamin</label>
                                            <div class="col-sm-9 col-md-8">
                                                <select name="jenis_kelamin" id="inputJenisKelamin"
                                                    class="form-control @error('jenis_kelamin') is-invalid @enderror" disabled>
                                                    <option value="">Pilih Jenis Kelamin</option>
                                                    <option value="laki-laki"
                                                        {{ old('jenis_kelamin') ? (old('jenis_kelamin') === 'laki-laki' ? 'selected' : '') : (Auth::user()->profile->jenis_kelamin === 'laki-laki' ? 'selected' : '') }}>
                                                        Laki-laki</option>
                                                    <option value="perempuan"
                                                        {{ old('jenis_kelamin') ? (old('jenis_kelamin') === 'perempuan' ? 'selected' : '') : (Auth::user()->profile->jenis_kelamin === 'perempuan' ? 'selected' : '') }}>
                                                        Perempuan</option>
                                                </select>
                                                @error('jenis_kelamin')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputAlamat" class="col-sm-3 col-md-4 col-form-label">Alamat</label>
                                            <div class="col-sm-9 col-md-8">
                                                <textarea name="alamat" id="inputAlamat" rows="5" class="form-control" readonly>{{ old('alamat') ? old('alamat') : Auth::user()->profile->alamat }}</textarea>
                                            </div>
                                        </div>
                                        <div class="mb-3 d-flex justify-content-end">
                                            <button class="btn btn-warning" type="button" id="btnEditProfile"
                                                onclick="editProfile(this)">Edit biodata</button>
                                            <div id="actionBtnProfile" class="d-none">
                                                <button class="btn btn-primary">Simpan</button>
                                                <button class="btn btn-danger" type="reset"
                                                    onclick="window.location.reload();">Batal</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        @if ($errors->any())
            editProfile($('#btnEditProfile'));
        @endif


        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @elseif (session('error'))
            toastr.error('{{ session('error') }}');
        @endif

        function editProfile(obj) {
            const name = $('#inputName');
            const nik = $('#inputNik');
            const email = $('#inputEmail');
            const nomer = $('#inputNomer');
            const jenisKelamin = $('#inputJenisKelamin');
            const alamat = $('#inputAlamat');

            name.removeAttr('readonly');
            nik.removeAttr('readonly');
            email.removeAttr('readonly');
            nomer.removeAttr('readonly');
            jenisKelamin.removeAttr('disabled');
            alamat.removeAttr('readonly');

            name.focus();

            $(obj).addClass('d-none');
            $('#actionBtnProfile').removeClass('d-none');
        }
    </script>
@endpush
