@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Pemohonan</a></li>
                        <li class="breadcrumb-item active">Create</li>
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
                      Create Permohonan layanan
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('permohonan.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label for="selectLayananjasa" class="col-md-3 form-label">Layanan <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="layanan_jasa" id="selectLayananjasa" class="form-control @error('layanan_jasa')
                                    is-invalid
                                @enderror" onchange="selectLayanan(this)">
                                    <option value="">--- Select ---</option>
                                    @foreach ($layanan as $value)
                                        <option value="{{ $value->layanan_hash }}">{{ $value->nama_layanan }}</option>
                                    @endforeach
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
                                    <option value="">--- Select ---</option>
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
                                                    aria-describedby="rupiah-text" placeholder="Tarif" readonly>
                                </div>
                            </div>
                            <div class="col-md-9 mb-2">
                                <label for="selectJadwal" class="form-label">Jadwal <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="jadwal" id="selectJadwal" class="form-control @error('jadwal')
                                    is-invalid
                                @enderror" onchange="selectJadwalLayanan(this)">
                                    <option value="">--- Select ---</option>
                                </select>
                                @error('jadwal')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="inputKuota" class="form-label">Kuota</label>
                                <input type="number" name="kuota" id="inputKuota" class="form-control" readonly>
                            </div>
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
                        <div class="mt-3 d-flex justify-content-end">
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
        const layanan = @json($layanan);

        setDropify('init', '#uploadDokumen0', {
            allowedFileExtensions: ['pdf','doc', 'docx'],
            maxSizeFile: '5M',
        });

        function selectLayanan(obj) {
            let idLayanan = obj.value;
            let cariLayanan = layanan.find(d => d.layanan_hash == idLayanan);

            if(cariLayanan){
                let jenis = JSON.parse(cariLayanan.jenis_layanan);
                let html = `<option>--- Select ---</option>`;

                for (const value of jenis) {
                    html += `<option value="${value.jenis}|${value.tarif}">${value.jenis}</option>`;
                }

                $('#selectJenisLayanan').html(html);
            }
        }

        function selectJenis(obj) {
            let jenis = obj.value.split('|');
            let idLayanan = $('#selectLayananjasa').val();
            $('#inputTarif').val(jenis[1]);

            let formData = new FormData();
            formData.append('idLayanan', idLayanan);
            formData.append('jenisLayanan', jenis[0]);

            $.ajax({
                method: 'GET',
                url : `{{ url('api/getJadwal') }}?idLayanan=${idLayanan}&jenisLayanan=${jenis[0]}`,
                dataType: 'json',
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                let html = `<option>--- Select ---</option>`;
                console.log(result);
                for (const jadwal of result.data) {
                    html += `
                        <option value="${jadwal.id}|${jadwal.kuota}">${jadwal.date_mulai} s/d ${jadwal.date_selesai}</option>
                    `;
                }
                if(result.data.length == 0){
                    html = `<option value="">--- Tidak ada jadwal ---</option>`;
                }
                $('#selectJadwal').html(html);
            }).fail(e => {
                console.log(e);
            });
        }

        function selectJadwalLayanan(obj){
            let jadwal = obj.value.split('|');

            $('#inputKuota').val(jadwal[1]);
        }

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
