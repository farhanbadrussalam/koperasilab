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
    <section class="content col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h2 class="card-title flex-grow-1">
                        Create Users
                    </h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        {{-- <div class="form-group mb-3">
                            <div class="text-center">
                                <div class="box-profile my-2">
                                    <a href="#" onclick="selectFileImage()">
                                        <img src="{{ asset('assets/img/default-avatar.jpg') }}" id="avatar" alt="Avatar" class="profile-user-img img-fluid img-circle" style="width: 100px;height: 100px;">
                                    </a>
                                    <input type="file" name="avatar" accept="image/png, image/gif, image/jpeg" id="uploadavatar" onchange="previewAvatar(this)" hidden>
                                </div>
                            </div>
                        </div> --}}
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="inputFullname" class="form-label">Nama lengkap <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="inputFullname" value="{{ old('name') }}">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputNik" class="form-label">NIK <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="nik" id="inputNik" class="form-control maskNIK @error('nik') is-invalid @enderror" value="{{ old('nik') }}">
                                @error('nik')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputNoHp" class="form-label">Nomer Telepon <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="no_telepon" id="inputNoHp" class="form-control maskTelepon @error('no_telepon') is-invalid @enderror" value="{{ old('no_telepon') }}">
                                @error('no_telepon')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputJenisKelamin" class="form-label">Jenis Kelamin <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="jenis_kelamin" id="inputJenisKelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" value="{{ old('jenis_kelamin') }}">
                                    <option value="">--- Select ---</option>
                                    <option value="laki-laki">Laki laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputSatuanKerja" class="form-label">Satuan Kerja <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="satuanKerja" id="inputSatuanKerja" class="form-control @error('satuanKerja') is-invalid @enderror" value="{{ old('satuanKerja') }}">
                                    <option value="">--- Select ---</option>
                                    @foreach ($satuankerja as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @error('satuanKerja')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputRole" class="form-label">Role <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="role[]" id="inputRole" class="form-control @error('role') is-invalid @enderror" value="{{ old('role') }}" multiple="multiple">
                                    <option value="">--- Select ---</option>
                                    @foreach ($role as $value)
                                        <option value="{{ $value->name }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2 d-none" id="tugas_lhu">
                                <label for="inputTugasLhu" class="form-label">Tugas LHU <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="tugas_lhu[]" id="inputTugasLhu" class="form-control @error('tugas_lhu') is-invalid @enderror" value="{{ old('tugas_lhu') }}" multiple="multiple">
                                    <option value="">--- Select ---</option>
                                    @foreach ($jobs as $value)
                                        <option value="{{ $value->jobs_hash }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @error('tugas_lhu')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="inputEmail" class="form-label">Email <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="email" name="email" id="inputEmail" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="inputAlamat" class="form-label">Alamat</label>
                                <textarea name="alamat" id="inputAlamat" cols="30" rows="3" class="form-control">{{ old('alamat') }}</textarea>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputPassword" class="form-label">Password <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="password" name="password" id="inputPassword" class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="password-confirm" class="form-label">Retype password <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password-confirm" class="form-control">
                            </div>
                            <div class="col-md-12 mt-3 text-center">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('scripts')
    <script>
        $(function(){
            $('#inputRole').on('change', function(evt){
                $('#tugas_lhu').removeClass('d-block').addClass('d-none');
                let role = $('#inputRole').val();
                if(role.includes('Staff LHU')) {
                    $('#tugas_lhu').removeClass('d-none').addClass('d-block');
                }
            });

            $('#inputTugasLhu').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Tugas",
            });

            $('#inputRole').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Role",
            })
        })
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
@endpush
