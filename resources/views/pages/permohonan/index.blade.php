@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Permohonan</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-8 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Permohonan layanan
                    </h3>
                    @can('Permohonan.create')
                    <a href="{{ route('permohonan.create') }}" class="btn btn-primary btn-sm">Add permohonan</a>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-hover w-100" id="permohonan-table">
                        <thead>
                            <th width="5%">No</th>
                            <th>Layanan</th>
                            <th>Jadwal</th>
                            <th>Progress</th>
                            <th>Antrian</th>
                            <th width="10%">Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.permohonan.confirm')
@endsection
@push('scripts')
    <script>
        let idPermohonan = false;
        let datatable_permohonan = false;
        $(function () {
            datatable_permohonan = $('#permohonan-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('permohonan.getData') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'nama_layanan', name: 'nama_layanan' },
                    { data: 'jadwal', name: 'jadwal' },
                    { data: 'status', name: 'status' },
                    { data: 'nomor_antrian', name: 'nomor_antrian' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });
            datatable_permohonan.on('init.dt', function() {
                maskReload();
            });
        });

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/permohonan') }}/"+id,
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
                        datatable_permohonan?.ajax.reload();
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
                url: '{{ url("api/permohonan") }}/'+id,
                method: 'GET',
                dataType: 'json',
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                $('#txtNamaLayanan').html(result.data.layananjasa.nama_layanan);
                $('#txtJenisLayanan').html(result.data.jenis_layanan);
                $('#txtHarga').html(result.data.tarif);
                $('#txtStart').html(result.data.jadwal.date_mulai);
                $('#txtEnd').html(result.data.jadwal.date_end);
                $('#txtStatus').html(statusFormat('permohonan', result.data.status));
                $('#txtNoBapeten').html(result.data.no_bapeten);
                $('#txtAntrian').html(result.data.nomor_antrian);
                $('#txtJeniLimbah').html(result.data.jenis_limbah);
                $('#txtRadioaktif').html(result.data.sumber_radioaktif);
                $('#txtJumlah').html(result.data.jumlah);

                // ambil dokumen
                let dokumen = `- <a href="{{ asset('storage/dokumen/permohonan') }}/${result.data.media.file_hash}" target="_blank">${result.data.media.file_ori}</a>`;
                $('#tmpDokumenPendukung').html(dokumen);
                maskReload();
                idPermohonan = id;
                $('#confirmModal').modal('show');
            })
        }

        function btnConfirm(status){
            $('#confirmModal').modal('hide');
            if(status == 2){
                $('#txtInfoConfirm').html('Setuju');
            }else{
                $('#txtInfoConfirm').html('Tolak');

            }
            $('#noteModal').modal('show');
        }

        function sendConfirm(key) {
            if(key == 1){
                let note = $('#inputNote').val();
                let documenSurat = $('#uploadSurat')[0].files[0];

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('note', note);
                formData.append('id', idPermohonan);
                formData.append('file', documenSurat);


                $.ajax({
                    url: '{{ url("api/updatePermohonan") }}',
                    method: "POST",
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`
                    },
                    data: formData
                }).done(result => {
                    console.log(result);
                })
            }else{
                $('#noteModal').modal('hide');
                $('#confirmModal').modal('show');
            }
        }

        setDropify('init', '#uploadSurat', {
            allowedFileExtentions:['pdf','doc','docx'],
            maxFileSize: '5M'
        });
    </script>
@endpush
