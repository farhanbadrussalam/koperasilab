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
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content col-xl-8 col-md-12">
            <div class="container">
                <div class="card card-default color-palette-box shadow">
                    <div class="card-header d-flex ">
                        <h2 class="card-title flex-grow-1">
                            Create Layanan
                        </h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('layananJasa.store') }}" method="post">
                            @csrf
                            <div class="mb-3 row">
                                <label for="selectSatuankerja" class="col-sm-3 form-label">Satuan Kerja <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select name="satuankerja" id="selectSatuankerja"
                                        class="form-control @error('satuankerja')
                                    is-invalid
                                @enderror"
                                        onchange="getPegawai(this)">
                                        <option value="">-- Select --</option>
                                        @foreach ($satuankerja as $key => $satuan)
                                            <option value="{{ $satuan->id }}">{{ $satuan->name }}</option>
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
                                <label for="selectPJ" class="col-sm-3 form-label">Penanggung Jawab <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div class="col-sm-9">
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
                                <label for="inputNamaLayanan" class="col-sm-3 form-label">Nama Layanan <span class="fw-bold fs-14 text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="name_layanan" id="inputNamaLayanan"
                                        class="form-control @error('name_layanan')
                                    is-invalid
                                @enderror">
                                    @error('name_layanan')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 form-label">Jenis layanan <i class="bi bi-plus-square-fill text-success" title="Tambah jenis" role="button" onclick="tambahFormJenis()"></i></label>
                                <div class="col-md-9" id="formJenisLayanan">
                                    <div class="mb-3 row">
                                        <div class="col-7">
                                            <input type="text"
                                                class="form-control"
                                                name="jenisLayanan[]" id="inputJenisLayanan" required>
                                        </div>
                                        <div class="col-5">
                                            <div class="input-group">
                                                <span class="input-group-text" id="rupiah-text">Rp</span>
                                                <input type="text" name="tarif[]" id="inputTarif"
                                                    class="form-control rupiah"
                                                    aria-describedby="rupiah-text" placeholder="Tarif" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 d-flex justify-content-end">
                                <button class="btn btn-primary">Simpan</button>
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
        function tambahFormJenis(){
            let html = `
                <div class="mb-3 row">
                    <div class="col-7">
                        <div class="input-group">
                            <button class="btn btn-danger" id="rupiah-text" type="button" onclick="removeFormJenis(this)"><i class="bi bi-trash3-fill"></i></button>
                            <input type="text"
                                class="form-control"
                                name="jenisLayanan[]" required>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="input-group">
                            <span class="input-group-text" id="rupiah-text">Rp</span>
                            <input type="text" name="tarif[]"
                                class="form-control rupiah"
                                aria-describedby="rupiah-text" placeholder="Tarif" required>
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
        function getPegawai(obj) {
            if (obj.value) {
                $.ajax({
                    method: 'GET',
                    url: "{{ url('/api/getPegawai') }}",
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    },
                    data: {
                        satuankerja: obj.value,
                        role: "staff"
                    }
                }).done(function(result) {
                    if (result.data) {
                        let html = '<option>-- Select --</option>';
                        for (const pegawai of result.data) {
                            html += `<option value="${pegawai.id}">${pegawai.name}</option>`;
                        }

                        $('#selectPJ').html(html);
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
                $('#selectPJ').html('<option>-- Select --</option>');
            }
        }
    </script>
@endpush
