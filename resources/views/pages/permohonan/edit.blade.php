@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Pemohonan</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-8 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow bg-white">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Edit Permohonan layanan
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('permohonan.update', $permohonan->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label for="inputLayananjasa" class="col-md-3 form-label">Layanan <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="layanan_jasa" id="inputLayananjasa" class="form-control" value="{{ $permohonan->layananjasa->nama_layanan }}" readonly>
                                @error('layanan_jasa')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputJenisLayanan" class="form-label">Jenis Layanan <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="jenis_layanan" id="inputJenisLayanan" class="form-control" value="{{ $permohonan->jenis_layanan }}" readonly>
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
                                                    aria-describedby="rupiah-text" placeholder="Tarif" value="{{ $permohonan->tarif }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="inputJadwal" class="form-label">Jadwal <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="jadwal" id="inputJadwal" class="form-control" value="{{ $permohonan->jadwal->date_mulai }} s/d {{ $permohonan->jadwal->date_selesai }}" readonly>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputNoBapeten" class="form-label">Nomor BAPETEN <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="number" name="noBapeten" id="inputNoBapeten" class="form-control @error('noBapeten')
                                    is-invalid
                                @enderror" value="{{ old('noBapeten') ? old('noBapeten') : $permohonan->no_bapeten }}">
                                @error('noBapeten')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputJenisLimbah" class="form-label">Jenis Limbah <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="jenisLimbah" id="inputJenisLimbah" class="form-control @error('jenisLimbah')
                                    is-invalid
                                @enderror" value="{{ old('jenisLimbah') ? old('jenisLimbah') : $permohonan->jenis_limbah }}">
                                @error('jenisLimbah')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputRadioaktif" class="form-label">Sumber Radioaktif <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="radioAktif" id="inputRadioaktif" class="form-control @error('radioAktif')
                                    is-invalid
                                @enderror" value="{{ old('radioAktif') ? old('radioAktif') : $permohonan->sumber_radioaktif }}">
                                @error('radioAktif')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputJumlah" class="form-label">Jumlah <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="number" name="jumlah" id="inputJumlah" class="form-control @error('jumlah')
                                    is-invalid
                                @enderror" value="{{ old('jumlah') ? old('jumlah') : $permohonan->jumlah }}">
                                @error('jumlah')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="uploadDokumen" class="form-label">Dokumen pendukung  <i class="bi bi-plus-square-fill text-success" title="Tambah jenis" role="button" onclick="tambahDocument()"></i></label>
                                <div class="mb-3 text-muted" style="font-size: 12px;">Allowed file types: pdf,doc,docx. Recommend size under 5MB.</div>
                                <div class="d-flex flex-wrap" id="tmpDocument">
                                    <div class="card m-1" style="width: 150px;height: 150px;">
                                        <input type="file" name="dokumen[]" id="uploadDokumen0" accept=".pdf,.doc,.docx" class="form-control dropify">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            <button class="btn btn-primary">Kirim ulang permohonan</button>
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
        let media = @json($permohonan->media);
        let countDoc = 0;

        for (const i in media) {
            if (Object.hasOwnProperty.call(media, i)) {
                const value = media[i];

                if(i != 0){
                    tambahDocument(i);
                }
                setDropify('init', `#uploadDokumen${i}`, {
                    allowedFileExtensions: ['pdf','doc', 'docx'],
                    maxSizeFile: '5M',
                    defaultFile: value ? "{{ asset('storage/dokumen/permohonan/') }}/"+value.file_hash : false,
                    fileNameOri: value ? value.file_ori : false
                });

            }
        }

        function tambahDocument(i) {
            if(!i){
                i = countDoc + 1;
            }
            let html = `
                <div class="card m-1" style="width: 150px;height: 180px;">
                    <input type="file" name="dokumen[]" accept=".pdf,.doc,.docx" class="form-control dropify" id="uploadDokumen${i}">
                    <button class="btn btn-danger btn-sm" role="button" onclick="removeDocument(this)">Remove</button>
                </div>
            `;

            $('#tmpDocument').append(html);

            setDropify('init', `#uploadDokumen${i}`, {
                allowedFileExtensions: ['pdf','doc', 'docx'],
                maxSizeFile: '5M'
            });
            countDoc = Number(i);
        }

        function removeDocument(obj){
            $(obj).parent().remove();
        }
    </script>
@endpush
