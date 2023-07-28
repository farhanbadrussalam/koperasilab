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
                    <div class="card col-xl-6 col-md-12 bg-white">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 text-center fw-bolder border-bottom">
                                    <h2>Biodata perusahaan</h2>
                                </div>
                                <form action="{{ route('userPerusahaan.update', Auth::user()->perusahaan->id) }}"
                                    class="mt-3" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-3 row">
                                        <label for="inputNamePerusahaan" class="col-sm-3 col-md-4 col-form-label">Nama
                                            Perusahaan <span class="fw-bold fs-14 text-danger">*</span></label>
                                        <div class="col-sm-9 col-md-8">
                                            <input type="text" name="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                id="inputNamePerusahaan"
                                                value="{{ old('name') ? old('name') : Auth::user()->perusahaan->name }}"
                                                readonly>
                                            @error('name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmailPerusahaan" class="col-sm-3 col-md-4 col-form-label">Email
                                        <span class="fw-bold fs-14 text-danger">*</span></label>
                                        <div class="col-sm-9 col-md-8">
                                            <input type="email" name="email" class="form-control is-invalid"
                                                id="inputEmailPerusahaan"
                                                value="{{ old('email') ? old('email') : Auth::user()->perusahaan->email }}"
                                                readonly>
                                            <div class="invalid-feedback">
                                                Email belum terverifikasi
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputNpwpPerusahaan" class="col-sm-3 col-md-4 col-form-label">NPWP
                                        <span class="fw-bold fs-14 text-danger">*</span></label>
                                        <div class="col-sm-9 col-md-8">
                                            <input type="text" name="npwp"
                                                class="form-control maskNPWP @error('npwp') is-invalid @enderror"
                                                id="inputNpwpPerusahaan"
                                                value="{{ old('npwp') ? old('npwp') : Auth::user()->perusahaan->npwp }}"
                                                readonly>
                                            @error('npwp')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputSuratKuasaPerusahaan"
                                            class="col-sm-3 col-md-4 col-form-label">Surat kuasa <span class="fw-bold fs-14 text-danger">*</span></label>
                                        <div class="col-sm-9 col-md-8">
                                            <div id="previewDokumen">
                                                @if(Auth::user()->perusahaan->surat_kuasa)
                                                <a href="{{ asset('storage/dokumen/surat_kuasa/'.Auth::user()->perusahaan->surat_kuasa ) }}" target="_blank" id="previewDokumen">surat kuasa.pdf</a>
                                                @else
                                                <div class="text-danger">Belum upload</div>
                                                @endif
                                            </div>
                                            <div id="uploadDokumen" class="d-none">
                                                <input type="file" accept="application/pdf" name="dokumen" class="form-control @error('dokumen') is-invalid @enderror">
                                                @error('dokumen')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputAlamat" class="col-sm-3 col-md-4 col-form-label">Alamat</label>
                                        <div class="col-sm-9 col-md-8">
                                            <textarea name="alamat" id="inputAlamat" cols="30" rows="2" class="form-control" readonly>{{ Auth::user()->perusahaan->alamat }}</textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 d-flex justify-content-end">
                                        <button class="btn btn-warning" type="button" id="btnEditPerusahaan"
                                            onclick="editPerusahaan(this)">Edit perusahaan</button>
                                        <div id="actionBtnPerusahaan" class="d-none">
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
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        @if ($errors->any())
            editPerusahaan($('#btnEditPerusahaan'));
        @endif

        function editPerusahaan(obj) {
            const name = $('#inputNamePerusahaan');
            const email = $('#inputEmailPerusahaan');
            const npwp = $('#inputNpwpPerusahaan');
            const alamat = $('#inputAlamat');

            name.removeAttr('readonly');
            email.removeAttr('readonly');
            npwp.removeAttr('readonly');
            alamat.removeAttr('readonly');

            $('#uploadDokumen').removeClass('d-none');
            $('#previewDokumen').addClass('d-none');

            name.focus();

            $(obj).addClass('d-none');
            $('#actionBtnPerusahaan').removeClass('d-none');
        }
    </script>
@endpush
