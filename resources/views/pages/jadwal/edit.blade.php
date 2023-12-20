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
                                <input type="text" name="tanggal_mulai" id="inputDateMulai" class="form-control" value="{{ old('tanggal_mulai') ? old('tanggal_mulai') : $jadwal->date_mulai }}">
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="inputDateSelesai" class="form-label">Tanggal selesai <span class="fw-bold fs-14 text-danger">*</span></label>
                                <input type="text" name="tanggal_selesai" id="inputDateSelesai" class="form-control" value="{{ old('tanggal_selesai') ? old('tanggal_selesai') : $jadwal->date_selesai }}">
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
                            {{-- <div class="col-md-12 mb-2">
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
                            <div class="col-md-12 mb-2">
                                <div class="d-flex justify-content-between">
                                    <label for="selectPetugas" class="form-label">Petugas <span class="fw-bold fs-14 text-danger">*</span></label>
                                    <button class="btn btn-sm btn-outline-success" type="button" onclick="addPetugas()">Tambah Petugas</button>
                                </div>

                                <table class="table table-borderless w-100" id="content-petugas"></table>
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
@include('pages.jadwal.addPetugas')
@include('pages.jadwal.changePetugas')
@endsection
@push('scripts')
    <script>
        // Initialisasi
        // mengambil data yang dikirim oleh controller
        const mediaJadwal = @json($jadwal->media);
        const jadwal = @json($jadwal);
        const pegawai = @json($pegawai);
        let datatable_petugas = false;

        // setting
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


        // ketika load data selesai
        $(function(){
            // menambahkan list petugas layanan
            datatable_petugas = $('#content-petugas').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                lengthChange: false,
                pageLength: 5,
                infoCallback: function( settings, start, end, max, total, pre ) {
                    var api = this.api();
                    var pageInfo = api.page.info();

                    return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
                },
                ajax: {
                    url: "{{ route('jadwal.getPetugasDT') }}",
                    data: function(d){
                        d.idJadwal = jadwal.jadwal_hash
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            })
        });

        // METHOD
        function addPetugas() {
            $.ajax({
                method: 'GET',
                url: "{{ url('api/petugas/getJadwalPetugas/'.$jadwal->jadwal_hash) }}",
                dataType: "JSON",
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(function(result){
                let arrResult = [];
                for (const data of pegawai) {
                    let find = result.data.find(f => f.petugas.user_hash == data.petugas.user_hash);

                    if(!find){
                        arrResult.push({
                            id: data.petugas.user_hash,
                            text: data.petugas.name,
                            title: stringSplit(data.otorisasi[0].name, 'Otorisasi-')
                        });
                    }
                }
                $('#selectPetugas').select2({
                    theme: "bootstrap-5",
                    placeholder: "Select petugas",
                    templateResult: formatSelect2Staff,
                    dropdownParent: $('#addPetugas'),
                    data: arrResult
                });

                $('#addPetugas').modal('show');
            })
        }
        function changePetugas(idHash){
            $.ajax({
                method: 'GET',
                url: "{{ url('api/petugas/getJadwalPetugas/'.$jadwal->jadwal_hash) }}",
                dataType: "JSON",
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(function(result){
                let arrResult = [];
                for (const data of pegawai) {
                    let find = result.data.find(f => f.petugas.user_hash == data.petugas.user_hash);

                    if(!find){
                        arrResult.push({
                            id: data.petugas.user_hash,
                            text: data.petugas.name,
                            title: stringSplit(data.otorisasi[0].name, 'Otorisasi-')
                        });
                    }
                }
                $('#idJadwalPetugas').val(idHash);
                $('#selectChangePetugas').select2({
                    theme: "bootstrap-5",
                    placeholder: "Select petugas",
                    templateResult: formatSelect2Staff,
                    dropdownParent: $('#changePetugas'),
                    data: arrResult
                });

                $('#changePetugas').modal('show');
            })
        }
        function storePetugas(){
            let select = $('#selectPetugas').val();
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('idPetugas', select);
            formData.append('idJadwal', jadwal.jadwal_hash);

            $.ajax({
                method: "POST",
                url: "{{ url('api/petugas/storeJadwalPetugas') }}",
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'Authorization': `Bearer {{ $token }}`
                },
                data: formData
            }).done(result => {
                toastr.success(result.message);
                datatable_petugas?.ajax.reload();
                $('#addPetugas').modal('hide');
            });
        }

        function updatePetugas() {
            let select = $('#selectChangePetugas').val();
            let idJadwalPetugas = $('#idJadwalPetugas').val();
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('idPetugas', select);
            formData.append('id', idJadwalPetugas);

            $.ajax({
                method: "POST",
                url: "{{ url('api/petugas/updateJadwalPetugas') }}",
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'Authorization': `Bearer {{ $token }}`
                },
                data: formData
            }).done(result => {
                toastr.success(result.message);
                datatable_petugas?.ajax.reload();
                $('#changePetugas').modal('hide');
            });
        }

        function deletePetugas(id){
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/petugas/destroyJadwalPetugas') }}/"+id,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    }
                }).done((result) => {
                    if(result.message){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        datatable_petugas?.ajax.reload();
                    }
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                });
            });
        }

        // END METHOD
    </script>
@endpush
