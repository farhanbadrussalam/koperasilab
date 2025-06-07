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
                        <li class="breadcrumb-item active">Update</li>
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
                        Update Users
                    </h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', encryptor($d_user->id)) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        {{-- <div class="form-group mb-3">
                            <div class="text-center">
                                <div class="box-profile my-2">
                                    <a href="#" onclick="selectFileImage()">
                                        <img src="{{ $d_user->profile ? asset('storage/images/avatar/'. $d_user->profile->avatar) : asset('assets/img/default-avatar.jpg') }}" onerror="this.src='{{ asset('assets/img/default-avatar.jpg') }}';" id="avatar" alt="Avatar" class="profile-user-img img-fluid img-circle" style="width: 100px;height: 100px;">
                                    </a>
                                    <input type="file" name="avatar" accept="image/png, image/gif, image/jpeg" id="uploadavatar" onchange="previewAvatar(this)" hidden>
                                </div>
                            </div>
                        </div> --}}
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="inputFullname" class="form-label">Full name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="inputFullname" value="{{ old('name') ? old('name') : $d_user->name }}">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputNik" class="form-label">NIK <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="nik" id="inputNik" class="form-control maskNIK @error('nik') is-invalid @enderror" value="{{ old('nik') ? old('nik') : ($d_user->profile ? $d_user->profile->nik : '') }}">
                                @error('nik')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputNoHp" class="form-label">Nomer Telepon</label>
                                <input type="text" name="no_telepon" id="inputNoHp" class="form-control maskTelepon @error('no_telepon') is-invalid @enderror" value="{{ old('no_telepon') ? old('no_telepon') : ($d_user->profile ? $d_user->profile->no_hp : '') }}">
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
                                <label for="inputRole" class="form-label">Role <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="role[]" id="inputRole" class="form-control @error('role') is-invalid @enderror" multiple="multiple">
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
                            <div class="col-md-6 mb-2">
                                <label for="inputSatuanKerja" class="form-label">Satuan Kerja <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="satuanKerja[]" id="inputSatuanKerja" class="form-control @error('satuanKerja') is-invalid @enderror" multiple="multiple">
                                    <option value="">--- Select ---</option>
                                    @foreach ($satuankerja as $value)
                                        <option value="{{ $value->satuan_hash }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @error('satuanKerja')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2 d-none" id="tugas_lhu">
                                <label for="inputTugasLhu" class="form-label">Tugas LHU <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="tugas_lhu[]" id="inputTugasLhu" class="form-control @error('tugas_lhu') is-invalid @enderror" multiple="multiple">
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
                                <input type="email" name="email" id="inputEmail" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') ? old('email') : ($d_user->email) }}">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="inputAlamat" class="form-label">Alamat</label>
                                <textarea name="alamat" id="inputAlamat" cols="30" rows="3" class="form-control">{{ old('alamat') ? old('alamat') : ($d_user->profile ? $d_user->profile->alamat : '') }}</textarea>
                            </div>
                            <div class="col-md-12 mt-3 text-center d-flex justify-content-between">
                                <button type="button" class="btn btn-danger" onclick="deleteUser()">Delete User</button>
                                <button type="submit" class="btn btn-primary">Update</button>
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
        // Initialisasi
        const roleUser = @json(count($d_user->getRoleNames()) != 0 ? $d_user->getRoleNames() : '');
        const profile = @json($d_user->profile);
        const d_user = @json($d_user);

        $(function() {

            $('#inputRole').on('change', function(evt){
                $('#tugas_lhu').removeClass('d-block').addClass('d-none');
                let role = $('#inputRole').val();
                if(role.includes('Staff LHU')) {
                    $('#tugas_lhu').removeClass('d-none').addClass('d-block');
                }
            });

            if(profile?.jenis_kelamin){
                $('#inputJenisKelamin').val(profile.jenis_kelamin);
            }

            let arrSatuanId = [];
            if(d_user?.satuankerja) {
                arrSatuanId = d_user.satuankerja.map(function(item) {
                    return item.satuan_hash
                })
            }

            $('#inputSatuanKerja').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Satuan Kerja",
            }).val(arrSatuanId).trigger('change');

            $('#inputTugasLhu').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Tugas",
                defaultValue: roleUser
            });

            $('#inputRole').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Role",
            }).val(roleUser).trigger('change');

            if(roleUser.includes('Staff LHU')){
                $('#tugas_lhu').removeClass('d-none');
                $('#inputTugasLhu').val(d_user.jobs).trigger('change');
            }else{
                $('#tugas_lhu').addClass('d-none');
            }
        })

        // Function
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

        function deleteUser() {
            ajaxDelete(`management/users/${d_user.user_hash}`, (result) => {
                if (result.meta.code == 200) {
                    swal.fire({
                        icon: 'success',
                        title: 'Berhasil dihapus',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '/management/users';
                    })
                }
            })
        }
    </script>
@endpush
