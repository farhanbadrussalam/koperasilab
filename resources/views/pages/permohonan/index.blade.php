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
        $(function() {
            datatable_permohonan = $('#permohonan-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('permohonan.getData') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'nama_layanan',
                        name: 'nama_layanan'
                    },
                    {
                        data: 'jadwal',
                        name: 'jadwal'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'nomor_antrian',
                        name: 'nomor_antrian'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            datatable_permohonan.on('init.dt', function() {
                maskReload();
            });
        });

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/permohonan') }}/" + id,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    }
                }).done((result) => {
                    if (result.message) {
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

        function modalConfirm(id) {
            $.ajax({
                url: '{{ url('api/permohonan') }}/' + id,
                method: 'GET',
                dataType: 'json',
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                const data = result.data;
                $('#txtNamaPelanggan').html(data.user.name);
                $('#txtNamaLayanan').html(data.layananjasa.nama_layanan);
                $('#txtJenisLayanan').html(data.jenis_layanan);
                $('#txtHarga').html(data.tarif);
                $('#txtStart').html(data.jadwal.date_mulai);
                $('#txtEnd').html(data.jadwal.date_end);
                $('#txtStatus').html(statusFormat('permohonan', data.status));
                $('#txtNoBapeten').html(data.no_bapeten);
                $('#txtAntrian').html(data.nomor_antrian);
                $('#txtJeniLimbah').html(data.jenis_limbah);
                $('#txtRadioaktif').html(data.sumber_radioaktif);
                $('#txtJumlah').html(data.jumlah);

                // ambil dokumen
                let dokumen = ``;
                for (const media of data.media) {
                    dokumen += printMedia(media, "permohonan");
                }
                $('#tmpDokumenPendukung').html(dokumen);
                if (data.status == 1 && data.jadwal.petugas_id == "{{ Auth::user()->id }}") {
                    $('#divConfirmBtn').show();
                } else {
                    $('#divConfirmBtn').hide();
                }
                maskReload();
                idPermohonan = id;
                $('#confirmModal').modal('show');
            })
        }

        function btnConfirm(status) {
            $('#confirmModal').modal('hide');
            window.statusConfirm = status;

            if (status == 2) {
                $('#txtStatusSurat').html('rekomendasi');
                $('#txtInfoConfirm').html('Setuju');
            } else {
                $('#txtStatusSurat').html('jawaban');
                $('#txtInfoConfirm').html('Tolak');
            }
            $('#noteModal').modal('show');
        }

        function modalNote(id) {
            $.ajax({
                url: '{{ url('api/permohonan') }}/' + id,
                method: 'GET',
                dataType: 'json',
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                $('#txtNote').html(result.data.note);
                $('#tmpSurat').html(printMedia(result.data.surat_terbit, "permohonan"));
                if (result.data.status == 2) {
                    $('#txtStatusNote').html('rekomendasi');
                } else if (result.data.status == 9) {
                    $('#txtStatusNote').html('jawaban');
                }
                $('#previewNoteModal').modal('show');
            })
        }

        function sendConfirm(key) {
            if (key == 1) {
                let note = $('#inputNote').val();
                let documenSurat = $('#uploadSurat')[0].files[0];

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('note', note);
                formData.append('id', idPermohonan);
                formData.append('file', documenSurat);
                formData.append('status', window.statusConfirm);


                $.ajax({
                    url: '{{ url('api/updatePermohonan') }}',
                    method: "POST",
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`
                    },
                    data: formData
                }).done(result => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message
                    });
                    datatable_permohonan?.ajax.reload();
                    $('#noteModal').modal('hide');
                }).fail(e => {
                    console.error(e);
                })
            } else {
                $('#noteModal').modal('hide');
                $('#confirmModal').modal('show');
            }
        }

        function printMedia(media, folder){
            return `
            <a
                class="mt-2 d-flex align-items-center justify-content-between px-3 mx-1 shadow-sm cursoron document border"
                href="{{ asset('storage/dokumen') }}/${folder}/${media.file_hash}"
                target="_blank">
                    <div class="d-flex align-items-center">
                        <img class="my-3" src="{{ asset('icons') }}/${iconDocument(media.file_type)}" alt=""
                            style="width: 24px; height: 24px;">
                        <div class="d-flex flex-column ms-2">
                            <span class="caption text-main">${media.file_ori}</span>
                            <span class="text-submain caption" style="margin-top: -3px;">${formatBytes(media.file_size)}</span>
                        </div>
                    </div>
                <div class="d-flex align-items-center"></div>
            </a>
            `;
        }

        setDropify('init', '#uploadSurat', {
            allowedFileExtentions: ['pdf', 'doc', 'docx'],
            maxFileSize: '5M'
        });
    </script>
@endpush
