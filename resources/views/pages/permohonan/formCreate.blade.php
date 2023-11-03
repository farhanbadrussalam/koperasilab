@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Pemohonan</a></li>
                        <li class="breadcrumb-item">Create</li>
                        <li class="breadcrumb-item active">{{ $jadwal->jenislayanan }}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow bg-white">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Create Permohonan layanan
                    </h3>
                </div>
                <div class="card-body">
                    <div id="informasiLayanan" class="row border-bottom shadow-sm shadow-bottom rounded mb-3">
                        <div class="col-md-4 mb-3">
                            <label for="" class="lh-1">Nama Layanan</label>
                            <div>{{ $jadwal->layananjasa->nama_layanan }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="" class="lh-1">Jenis Layanan</label>
                            <div>{{ $jadwal->jenislayanan }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="" class="lh-1">Price</label>
                            <div>{{ formatCurrency($jadwal->tarif) }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="" class="lh-1">Start date</label>
                            <div>{{ convert_date($jadwal->date_mulai) }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="" class="lh-1">End date</label>
                            <div>{{ convert_date($jadwal->date_selesai) }}</div>
                        </div>
                    </div>
                    <form action="{{ route('permohonan.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="jadwal_id" value="{{ $jadwal->jadwal_hash }}">
                            <div class="col-md-6 mb-2">
                                <label for="inputNoBapeten" class="form-label">Nomor BAPETEN <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="number" name="noBapeten" id="inputNoBapeten" class="form-control @error('noBapeten')
                                    is-invalid
                                @enderror" value="{{ old('noBapeten') }}">
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
                                @enderror" value="{{ old('jenisLimbah') }}">
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
                                @enderror" value="{{ old('radioAktif') }}">
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
                                @enderror" value="{{ old('jumlah') }}">
                                @error('jumlah')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="uploadDokumen" class="form-label">Dokumen pendukung <i class="bi bi-plus-square-fill text-success" title="Tambah jenis" role="button" onclick="tambahDocument()"></i></label>
                                <div class="mb-3 text-muted" style="font-size: 12px;">Allowed file types: pdf,doc,docx. Recommend size under 5MB.</div>
                                <div class="d-flex flex-wrap" id="tmpDocument">
                                    <div class="card m-1" style="width: 150px;height: 150px;">
                                        <input type="file" name="dokumen[]" accept=".pdf,.doc,.docx" class="form-control dropify" id="uploadDokumen0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <a class="btn btn-danger" href="{{ route('permohonan.create') }}">Batal</a>
                            <button class="btn btn-primary">Buat permohonan</button>
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
        setDropify('init', '#uploadDokumen0', {
            allowedFileExtensions: ['pdf','doc', 'docx'],
            maxSizeFile: '5M',
        });
        let countDoc = 1;
        function tambahDocument() {
            let html = `
                <div class="card m-1" style="width: 150px;height: 180px;">
                    <input type="file" name="dokumen[]" accept=".pdf,.doc,.docx" class="form-control dropify" id="uploadDokumen${countDoc}">
                    <button class="btn btn-danger btn-sm" onclick="removeDocument(this)">Remove</button>
                </div>
            `;

            $('#tmpDocument').append(html);

            setDropify('init', `#uploadDokumen${countDoc}`, {
                allowedFileExtensions: ['pdf','doc', 'docx'],
                maxSizeFile: '5M',
            });
            countDoc++;
        }

        function removeDocument(obj){
            $(obj).parent().remove();
        }
    </script>
@endpush
