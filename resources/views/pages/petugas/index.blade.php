@extends('layouts.main')

@section('content')

<?php
$idSatuan = Auth::user()->satuankerja_id ? Auth::user()->satuankerja_id : null;
?>
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
        <div class="container col-md-10 col-xl-9">
            <div class="card card-default shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                        List Petugas Layanan
                    </h3>
                    @can('Petugas.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPetugasModal">Add petugas</button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        <div class="mx-2">
                            <label for="filterStatusVerif" class="form-label">Status</label>
                            <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                <option value="" selected>All</option>
                                <option value="{{ encryptor(1) }}">Not verif</option>
                                <option value="{{ encryptor(2) }}">Verifikasi</option>
                            </select>
                        </div>
                        <div class="mx-2">
                            <label for="filterLab" class="form-label">Lab</label>
                            <select name="filterLab" id="filterLab" class="form-select">
                                <option value="" selected>All</option>
                                @foreach ($lab as $val)
                                    <option value="{{ $val->lab_hash }}">{{ $val->name_lab }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mx-2">
                            <label for="inputSearch" class="form-label">Searching</label>
                            <input type="text" class="form-control" placeholder="Name petugas" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                        </div>
                        <div class="mx-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button class="btn btn-outline-secondary" type="button" id="btnSearch">Filter</button>
                            </div>
                        </div>
                    </div>
                    <table class="table w-100 table-borderless" id="petugas-layanan-table">
                        <thead>
                            <th></th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.petugas.create')
@include('pages.petugas.edit')
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
                        d.filterStatus = $('#filterStatusVerif').val(),
                        d.filterLab = $('#filterLab').val()
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false },
                ]
            });

            @if($errors->any())
                $('#txtMessage').html('@error("pegawai") {{ $message }}  @enderror');
                $('#divMessage').show();
                $('#createPetugasModal').modal('show');
            @endif

            @if($idSatuan)
                getPegawai(document.getElementById('inputSatuanKerja'));
            @endif

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
                placeholder: "Select Otorisasi"
            });

            $('#inputOtorisasiEdit').select2({
                theme: "bootstrap-5",
                dropdownParent: $('#editPetugasModal'),
                placeholder: "Select Otorisasi"
            });

            $('#btnSearch').on('click', obj => {
                datatable_petugas?.ajax.reload();
            });

            $('#inputSatuanKerja').on('change', getPegawai);
        })

        function getPegawai(obj) {
            let input = obj.target ? obj.target : obj;
            if($(input).val()){
                $.ajax({
                    method: 'GET',
                    url: '{{ url("/api/getPegawai") }}',
                    dataType: 'json',
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    },
                    data: {
                        satuankerja: $(input).val(),
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
            }else{
                $('#inputPegawai').html(`<option value="">-- Select Pegawai --</option>`);
            }
        }
        function btnEdit(obj){
            let petugas_id = $(obj).data('petugasid');

            $('#editPetugasModal').modal('show');
            $.ajax({
                url: '{{ url("api/petugas/getPetugas") }}',
                dataType: 'JSON',
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                },
                data: {
                    idPetugas: petugas_id
                }
            }).done(result => {
                let data = result.data;
                $('#inputSatuanKerjaEdit').val(data.satuankerja.name);
                $('#inputPegawaiEdit').val(data.petugas.name);
                $('#inputSatuanLabEdit').val(data.lab.lab_hash);
                $('#inputPetugasIdEdit').val(petugas_id);

                // Set otorisasi in select2
                const inputOtorisasi = document.getElementById('inputOtorisasiEdit');

                // Menghapus semua opsi yang sudah terpilih
                while (inputOtorisasi.selectedOptions.length > 0) {
                    inputOtorisasi.selectedOptions[0].selected = false;
                }

                // Memilih opsi berdasarkan array nilai yang baru
                for (const value of data.otorisasi) {
                    const option = inputOtorisasi.querySelector(`option[value="${value.name}"]`);
                    if (option) {
                        option.selected = true;
                    }
                }

                // Memperbarui tampilan Select2
                $(inputOtorisasi).trigger('change.select2');
            });
        }

        $('#form-edit').on("submit", (evt) => {
            evt.preventDefault();
            const formData = new FormData(evt.target);
            let url = `{{ url('petugasLayanan/update') }}`;

            $.ajax({
                method: "POST",
                url: url,
                processData: false,
                contentType: false,
                data: formData
            }).done((result) => {
                toastr.success(result.message);
                $('#editPetugasModal').modal('hide');
                datatable_petugas?.ajax.reload();
            });
        })

        function btnDelete(obj) {
            const id = $(obj).data('id');
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/petugasLayanan') }}/"+id,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    data: {
                        _token: "{{ csrf_token() }}"
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
    </script>
@endpush
