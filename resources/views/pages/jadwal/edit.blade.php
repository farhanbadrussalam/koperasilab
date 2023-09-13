@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('jadwal.index') }}">Jadwal</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-8 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box table-hover bg-white shadow">
                <div class="card-header d-flex ">
                    <h2 class="card-title flex-grow-1">
                        Edit Jadwal
                    </h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('jadwal.update', $jadwal->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="selectLayananjasa" class="form-label">Layanan <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="layanan_jasa" id="selectLayananjasa" class="form-control @error('layanan_jasa')
                                    is-invalid
                                @enderror" onchange="selectLayanan(this)">
                                    <option value="{{ $jadwal->layananjasa->id }}">{{ $jadwal->layananjasa->nama_layanan }}</option>
                                </select>
                                @error('layanan_jasa')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="selectJenisLayanan" class="form-label">Jenis Layanan <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="jenis_layanan" id="selectJenisLayanan" class="form-control @error('jenis_layanan')
                                    is-invalid
                                @enderror" onchange="selectJenis(this)">
                                    <option value="{{ $jadwal->jenislayanan }}">{{ $jadwal->jenislayanan }}</option>
                                </select>
                                @error('jenis_layanan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputTarif" class="form-label">Tarif</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="rupiah-text">Rp</span>
                                    <input type="text" name="tarif" id="inputTarif"
                                                    class="form-control rupiah"
                                                    aria-describedby="rupiah-text" placeholder="Tarif" value="{{ $jadwal->tarif }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputKuota" class="form-label">Kuota <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="number" name="kuota" id="inputKuota" class="form-control @error('kuota')
                                    is-invalid
                                @enderror" value="{{ old('kuota') ? old('kuota') : $jadwal->kuota }}">
                                @error('kuota')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputDateMulai" class="form-label">Tanggal mulai <span class="fw-bold fs-14 text-danger">*</span></label>
                                <x-flatpickr name="tanggal_mulai" show-time time-format="H:i"  value="{{ old('tanggal_mulai') ? old('tanggal_mulai') : $jadwal->date_mulai }}" />
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputDateSelesai" class="form-label">Tanggal selesai <span class="fw-bold fs-14 text-danger">*</span></label>
                                <x-flatpickr name="tanggal_selesai" show-time time-format="H:i"  value="{{ old('tanggal_selesai') ? old('tanggal_selesai') : $jadwal->date_selesai }}" />
                                @error('tanggal_selesai')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="inputPJ" class="form-label">Penanggung jawab</label>
                                <input type="text" id="inputPJ" class="form-control" value="{{ $jadwal->layananjasa->user->name ." (". stringSplit($jadwal->layananjasa->user->getDirectPermissions()[0]->name .")", 'Otorisasi-')}}" readonly>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="selectPetugas" class="form-label">Petugas <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="petugas[]" id="selectPetugas" class="form-control @error('petugas')
                                    is-invalid
                                @enderror" multiple>
                                    <option value="">--- Select ---</option>
                                    @foreach ($pegawai as $val)
                                        <option value="{{ $val->petugas->user_hash }}" title="{{ stringSplit($val->petugas->getDirectPermissions()[0]->name, 'Otorisasi-') }}">{{ $val->petugas->name }}</option>
                                    @endforeach
                                </select>
                                @error('petugas')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="uploadFile" class="form-label">Surat tugas</label>
                                <div class="card mb-0" style="height: 150px;">
                                    <input type="file" name="dokumen" id="uploadFile" accept=".pdf,.doc,.docx" class="form-control dropify @error('dokumen')
                                        is-invalid
                                    @enderror">
                                </div>
                                <span class="mb-3 text-muted" style="font-size: 12px;">Allowed file types: pdf, doc, docx. Recommend size under 5MB.</span>
                                @error('dokumen')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2 text-center">
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
        const mediaJadwal = @json($jadwal->media);
        const petugas = @json($petugas);

        $('#inputDateMulai').datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1
        });

        $('#selectPetugas').select2({
            theme: "bootstrap-5",
            placeholder: "Select petugas",
            templateResult: formatSelect2Staff
        });

        setDropify('init', '#uploadFile', {
            allowedFileExtensions: ['pdf', 'doc', 'docx'],
            maxFileSize: '5M',
            defaultFile: mediaJadwal ? "{{ asset('storage/dokumen/jadwal/') }}/"+mediaJadwal.file_hash : false,
            fileNameOri: mediaJadwal ? mediaJadwal.file_ori : false
        });

        let arrPetugas = [];
        for (const val of petugas) {
            arrPetugas.push(val.petugas.user_hash);
        }
        $('#selectPetugas').val(arrPetugas).trigger('change');
    </script>
@endpush
