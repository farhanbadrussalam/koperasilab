@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Jadwal</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Jadwal layanan
                    </h3>
                    @can('Penjadwalan.create')
                    <a href="{{ route('jadwal.create') }}" class="btn btn-primary btn-sm">Add jadwal</a>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-borderless w-100" id="jadwal-table">
                        <thead>
                            <th></th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.jadwal.confirm')
@include('pages.jadwal.info')
@endsection
@push('scripts')
    <script>
        let datatable_jadwal = false;
        $(function () {
            datatable_jadwal = $('#jadwal-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                lengthChange: false,
                ajax: "{{ route('jadwal.getData') }}",
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });
        });
            // { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false }

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/deleteJadwal') }}/"+id,
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
                        datatable_jadwal?.ajax.reload();
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

        function modalConfirm(id){
            $.ajax({
                url: "{{ url('api/jadwal') }}/"+id,
                method: 'GET',
                dataType: 'json',
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                let data = result.data;

                $('#txtNamaLayanan').html(data.jadwal.layananjasa.name);
                $('#txtJenisLayanan').html(data.jadwal.jenislayanan);
                $('#txtHarga').html(formatRupiah(data.jadwal.tarif));
                $('#txtStart').html(convertDate(data.jadwal.date_mulai));
                $('#txtEnd').html(convertDate(data.jadwal.date_selesai));
                let status = statusFormat('jadwal', data.petugas.status);
                $('#txtStatus').html(status);
                $('#txtSuratTugas').attr('href', `{{ asset('storage/dokumen/jadwal') }}/${data.jadwal.media.file_hash}`);
                $('#txtSuratTugas').html(data.jadwal.media.file_ori);
                $('#idJadwal').val(data.jadwal.jadwal_hash);
                if(data.petugas.status == 1){
                    $('#divConfirmBtn').show();
                }else{
                    $('#divConfirmBtn').hide();
                }
                $('#confirmModal').modal('show');
            })
        }

        function btnConfirm(answer){
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('idJadwal', $('#idJadwal').val());
            formData.append('answer', answer);
            $.ajax({
                url: "{{ route('jadwal.updatePetugas') }}",
                method: "POST",
                dataType: 'json',
                processData: false,
                contentType: false,
                data: formData
            }).done(result => {
                if(result.status == 2){
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message
                    });
                    datatable_jadwal?.ajax.reload();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'error',
                        text: result.message
                    });
                    datatable_jadwal?.ajax.reload();
                }
                $('#confirmModal').modal('hide');
            }).fail(err => {
                console.log(err);
            })
        }

        function showPetugas(id) {
            $.ajax({
                url: "{{ url('api/getJadwalPetugas') }}",
                method: "GET",
                dataType: 'json',
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                },
                data: {
                    idJadwal: id
                }
            }).done(result => {
                let content = '';
                for (const data of result.data.petugas) {
                    let contentOtorisasi = '';
                    for (const otorisasi of data.otorisasi) {
                        contentOtorisasi += `<button class="btn btn-outline-dark btn-sm m-1" role="button">${stringSplit(otorisasi.name, 'Otorisasi-')}</button>`;
                    }

                    let pj = data.petugas.user_hash == result.data.pj ? `<small class="text-danger">Penanggung jawab</small>` : '';
                    content += `
                        <div class="card m-0 mb-2">
                            <div class="card-body d-flex p-2">
                                <div class="flex-grow-1 d-flex my-auto">
                                    <div>
                                        <img src="${data.avatar}" alt="Avatar" onerror="this.src='{{ asset('assets/img/default-avatar.jpg') }}'" style="width: 3em;" class="img-circle border shadow-sm">
                                    </div>
                                    <div class="px-3 my-auto">
                                        <div class="lh-1">${data.petugas.name}</div>
                                        ${pj}
                                    </div>
                                </div>
                                <div class="p-2 m-auto">
                                    <div class="d-flex flex-wrap justify-content-end">
                                        ${contentOtorisasi}
                                    </div>
                                </div>
                                <div class="p-2 m-auto">${statusFormat('jadwal', data.status)}</div>
                            </div>
                        </div>
                    `;
                }

                $('#content-petugas').html(content);
                $('#infoModal').modal('show');
            })
        }
    </script>
@endpush
