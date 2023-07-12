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
                            <label for="selectSatuankerja" class="col-sm-3 form-label">Satuan Kerja</label>
                            <div class="col-sm-9">
                                <select name="satuankerja" id="selectSatuankerja" class="form-control" onchange="getPegawai(this)">
                                    <option value="">-- Select --</option>
                                    @foreach($satuankerja as $key => $satuan)
                                    <option value="{{ $satuan->id }}">{{ $satuan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="selectPJ" class="col-sm-3 form-label">Penanggung Jawab</label>
                            <div class="col-sm-9">
                                <select name="pj" id="selectPJ" class="form-control">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJenisLayanan" class="col-sm-3 form-label">Jenis layanan</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="jenisLayanan" id="inputJenisLayanan">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputTarif" class="form-label col-sm-3">Tarif</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text" id="rupiah-text">Rp</span>
                                    <input type="number" name="tarif" id="inputTarif" class="form-control" aria-describedby="rupiah-text">
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
        function getPegawai(obj) {
            console.log(obj.value);
        }
        $(function () {
            // $('#user-table').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     ajax: "{{ route('users.getData') }}",
            //     columns: [
            //         { data: 'id', name: 'id' },
            //         { data: 'name', name: 'name' },
            //         { data: 'email', name: 'email' },
            //         { data: 'role', name: 'role' },
            //         { data: 'action', name: 'action', orderable: false, searchable: false },
            //     ]
            // });
        });
    </script>
@endpush
