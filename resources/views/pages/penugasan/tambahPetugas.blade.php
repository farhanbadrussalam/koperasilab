@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('penugasan.index') }}">Jadwal Permohonan</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('penugasan.show', $jadwal->jadwal_hash) }}">List Permohonan</a></li>
                        <li class="breadcrumb-item active">Tambah Petugas</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-8 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box table-hover bg-white shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="d-flex justify-content-between">
                                <label for="selectPetugas" class="form-label"></label>
                                <button class="btn btn-sm btn-outline-success" type="button" onclick="addPetugas()">Tambah Petugas</button>
                            </div>

                            <table class="table table-borderless w-100" id="content-petugas"></table>
                        </div>
                    </div>
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

        $('#selectPetugas').select2({
            theme: "bootstrap-5",
            placeholder: "Select petugas",
            templateResult: formatSelect2Staff
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



        // END METHOD
    </script>
@endpush
