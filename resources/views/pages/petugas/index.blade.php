@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Petugas Layanan</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-md-12">
        <div class="container col-md-10 col-xl-7">
            <div class="card card-default shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                        List Petugas Layanan
                    </h3>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPetugasModal">Add petugas</button>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <select name="statusVerif" id="selectStatusVerif" class="form-select">
                                <option value="" selected>All</option>
                                <option value="{{ encryptor(1) }}">Not verif</option>
                                <option value="{{ encryptor(2) }}">Verifikasi</option>
                            </select>
                        </div>
                        <div>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Searching" aria-label="Searching" id="inputSearch" aria-describedby="btnSearch">
                                <button class="btn btn-outline-secondary" type="button" id="btnSearch">Filter</button>
                            </div>
                        </div>
                    </div>
                    <div id="table-container">
                        <table class="table w-100 table-borderless" id="petugas-layanan-table">
                            <thead>
                                <th></th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.petugas.create')
@endsection
@push('scripts')
    <script>
        let datatable_petugas = false;
        $(function() {
            datatable_petugas = $('#petugas-layanan-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                lengthChange: false,
                ajax: {
                    url: "{{ route('petugasLayanan.getData') }}",
                    data: function (d) {
                        d.search = $('#inputSearch').val(),
                        d.filterStatus = $('#selectStatusVerif').val()
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false },
                ]
            });

            $('#inputPegawai').select2({
                theme: "bootstrap-5",
                dropdownParent: $('#createPetugasModal'),
                placeholder: "Select Pegawai",
                allowClear: true
            });

            $('#inputSatuanLab').select2({
                theme: "bootstrap-5",
                dropdownParent: $('#createPetugasModal'),
                placeholder: "Select LAB",
                allowClear: true
            });

            $('#inputOtorisasi').select2({
                theme: "bootstrap-5",
                dropdownParent: $('#createPetugasModal'),
                placeholder: "Select Otorisasi",
                allowClear: true
            });

            $('#btnSearch').on('click', obj => {
                datatable_petugas?.ajax.reload();
            });

            $('#inputSatuanKerja').on('change', (obj) => {
                $.ajax({
                    method: 'GET',
                    url: '{{ url("/api/getPegawai") }}',
                    dataType: 'json',
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    },
                    data: {
                        satuankerja: obj.target.value,
                        role: 'staff'
                    }
                }).done(result => {
                    let content = `<option value="">-- Select Pegawai --</option>`;
                    for (const staff of result.data) {
                        content += `<option value="${staff.id}">${staff.name}</option>`;
                    }
                    $('#inputPegawai').html(content);
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                    console.error(message.responseJSON.message);
                });
            });
        })
    </script>
@endpush
