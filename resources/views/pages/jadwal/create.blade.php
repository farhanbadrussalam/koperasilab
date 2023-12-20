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
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container col-md-12 col-xl-8">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h2 class="card-title flex-grow-1">
                        Create Jadwal
                    </h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('jadwal.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="selectLayananjasa" class="form-label">Layanan <span class="fw-bold fs-14 text-danger">*</span></label>
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
                            <div class="col-md-6 mb-2">
                                <label for="inputKuota" class="form-label">Kuota <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="number" name="kuota" id="inputKuota" class="form-control @error('kuota')
                                    is-invalid
                                @enderror">
                                @error('kuota')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputDateMulai" class="form-label">Tanggal mulai <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="tanggal_mulai" id="inputDateMulai" class="form-control">
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputDateSelesai" class="form-label">Tanggal selesai <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="tanggal_selesai" id="inputDateSelesai" class="form-control">
                                @error('tanggal_selesai')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="inputPJ" class="form-label">Penanggung jawab</label>
                                <input type="text" id="inputPJ" class="form-control" readonly>
                            </div>
                            {{-- <div class="col-md-12 mb-2">
                                <label for="selectPetugas" class="form-label">Petugas <span class="fw-bold fs-14 text-danger">*</span></label>
                                <select name="petugas[]" id="selectPetugas" class="form-control @error('petugas')
                                    is-invalid
                                @enderror" multiple>
                                    <option value="">--- Select ---</option>
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
                            </div> --}}
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
        const layanan = @json($layanan);
        const petugas = @json($petugas);

        $('#selectPetugas').select2({
            theme: "bootstrap-5",
            placeholder: "Select petugas",
            templateResult: formatSelect2Staff
        });

        $('#inputDateMulai').flatpickr({
            enableTime: true,
            minDate: 'today',
            dateFormat: "Y-m-d H:i",
            time_24hr: true
        });

        $('#inputDateSelesai').flatpickr({
            enableTime: true,
            minDate: 'today',
            dateFormat: "Y-m-d H:i",
            time_24hr: true
        });

        function selectLayanan(obj) {
            let idLayanan = obj.value;
            let cariLayanan = layanan.find(d => d.layanan_hash == idLayanan);
            let contentPetugas = `<option value="">--- Select ---</option>`;
            let contentTarif = `<option value="">--- Select ---</option>`;

            if(cariLayanan){
                let jenis = JSON.parse(cariLayanan.jenis_layanan);

                for (const value of jenis) {
                    contentTarif += `<option value="${value.jenis}|${value.tarif}">${value.jenis}</option>`;
                }

                getPegawai(cariLayanan);
            }else{
                $('#inputPJ').val("");
                $('#selectPetugas').val(null).trigger('change');
                $('#selectPetugas').html(contentPetugas);
            }

            $('#selectJenisLayanan').html(contentTarif);
        }

        function selectJenis(obj) {
            let jenis = obj.value.split('|');
            $('#inputTarif').val(jenis[1]);
        }

        $('#inputDateMulai').datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1
        });

        setDropify('init', '#uploadFile', {
            allowedFileExtensions: ['pdf', 'doc', 'docx'],
            maxFileSize: '5M'
        });

        function getPegawai(layanan) {
            if (layanan) {
                $.ajax({
                    method: 'GET',
                    url: "{{ url('/api/petugas/getPetugas') }}",
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    },
                    data: {
                        idSatuan: layanan.satuan_kerja.satuan_hash
                    }
                }).done(function(result) {
                    if (result.data) {
                        let html = '<option>-- Select --</option>';
                        for (const data of result.data) {
                            if(data.petugas.user_hash == layanan.user.user_hash){
                                $('#inputPJ').val(`${data.petugas.name} (${stringSplit(data.otorisasi[0].name, 'Otorisasi-')})`);
                            }else{
                                html += `<option value="${data.petugas.user_hash}" title="${stringSplit(data.otorisasi[0].name, 'Otorisasi-')}" >
                                            ${data.petugas.name}
                                        </option>`;
                            }
                        }

                        $('#selectPetugas').html(html);
                    }
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                    console.error(message.responseJSON.message);
                });
            } else {
                $('#selectPetugas').html('<option>-- Select --</option>');
            }
        }
    </script>
@endpush
