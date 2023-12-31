@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('layananJasa.index') }}">Layanan Jasa</a></li>
                            <li class="breadcrumb-item active">Edit</li>
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
                            Create Layanan
                        </h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('layananJasa.update', $layananjasa->layanan_hash) }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="mb-3 row">
                                <label for="selectSatuankerja" class="col-sm-4 form-label">Satuan Kerja <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="satuankerja" id="selectSatuankerja"
                                        class="form-control @error('satuankerja')
                                    is-invalid
                                @enderror"
                                        disabled>
                                        <option value="">-- Select --</option>
                                        @foreach ($satuankerja as $key => $satuan)
                                            <option value="{{ $satuan->satuan_hash }}" @if($layananjasa->satuanKerja->satuan_hash == $satuan->satuan_hash) selected @endif>{{ $satuan->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('satuankerja')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="selectPJ" class="col-sm-4 form-label">Penanggung Jawab <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="pj" id="selectPJ"
                                        class="form-control @error('pj')
                                    is-invalid
                                @enderror">
                                        <option value="">-- Select --</option>
                                    </select>
                                    @error('pj')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="inputnamaLayanan" class="col-sm-4 form-label">Nama layanan <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text"
                                        class="form-control @error('nama_layanan')
                                    is-invalid
                                @enderror"
                                        name="nama_layanan" id="inputnamaLayanan" value="{{ $layananjasa->nama_layanan }}">
                                    @error('nama_layanan')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-4 form-label">Jenis layanan <i class="bi bi-plus-square-fill text-success" title="Tambah" role="button" onclick="tambahFormJenis()"></i></label>
                                <div class="col-md-8" id="formJenisLayanan">
                                    <div class="mb-3 row">
                                        <div class="col-7">
                                            <input type="text"
                                                class="form-control"
                                                name="jenisLayanan[]" id="inputJenisLayanan" value="{{ $jenisLayanan[0]->jenis }}" required>
                                        </div>
                                        <div class="col-5">
                                            <div class="input-group">
                                                <span class="input-group-text" id="rupiah-text">Rp</span>
                                                <input type="text" name="tarif[]" id="inputTarif"
                                                    class="form-control rupiah"
                                                    aria-describedby="rupiah-text" placeholder="Tarif" value="{{ $jenisLayanan[0]->tarif }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 d-flex justify-content-end">
                                <button class="btn btn-primary mx-2">Simpan</button>
                                <button class="btn btn-danger" type="reset" onclick="window.location.reload();">Reset</button>
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
        let pj = '{{ $layananjasa->user ? $layananjasa->user->user_hash : false }}';

        $('#selectPJ').select2({
            theme: "bootstrap-5",
            placeholder: "Select Penanggung jawab",
            templateResult: formatSelect2Staff
        });
        function getPegawai(obj) {
            if (obj.value) {
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
                        idSatuan: obj.value
                    }
                }).done(function(result) {
                    if (result.data) {
                        let html = '<option>-- Select --</option>';
                        for (const data of result.data) {
                            html += `<option value="${data.petugas.user_hash}" title="${stringSplit(data.otorisasi[0].name, 'Otorisasi-')}" ${pj == data.petugas.user_hash ? 'selected' : ''}>${data.petugas.name}</option>`;
                        }

                        $('#selectPJ').html(html);
                    }
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                });
            } else {
                $('#selectPJ').html('<option>-- Select --</option>');
            }
        }

        function tambahFormJenis(jenis = "", tarif = ""){

            let html = `
                <div class="mb-3 row">
                    <div class="col-7">
                        <div class="input-group">
                            <button class="btn btn-danger" id="rupiah-text" type="button" onclick="removeFormJenis(this)"><i class="bi bi-trash3-fill"></i></button>
                            <input type="text"
                                class="form-control"
                                name="jenisLayanan[]" value="${jenis ? jenis : ''}" required>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="input-group">
                            <span class="input-group-text" id="rupiah-text">Rp</span>
                            <input type="text" name="tarif[]"
                                class="form-control rupiah"
                                aria-describedby="rupiah-text" value="${tarif ? tarif : ''}" placeholder="Tarif" required>
                        </div>
                    </div>
                </div>
            `;

            $('#formJenisLayanan').append(html);
            maskReload();
        }

        function removeFormJenis(obj){
            $(obj).parent().parent().parent().remove();
        }

        $(function () {
            getPegawai(document.getElementById('selectSatuankerja'));

            const jenisLayanan = @json($jenisLayanan);

            for (const index in jenisLayanan) {
                if (Object.hasOwnProperty.call(jenisLayanan, index)) {
                    const data = jenisLayanan[index];
                    if(index != 0){
                        tambahFormJenis(data.jenis, data.tarif);
                    }
                }
            }
        })
    </script>
@endpush
