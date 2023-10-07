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
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                              <button class="nav-link text-primary active" id="pengajuan-tab" data-bs-toggle="tab" data-bs-target="#pengajuan-tab-pane" type="button" role="tab" aria-controls="pengajuan-tab-pane" aria-selected="true">Pengajuan</button>
                            </li>
                            <li class="nav-item" role="presentation">
                              <button class="nav-link text-success" id="disetujui-tab" data-bs-toggle="tab" data-bs-target="#disetujui-tab-pane" type="button" role="tab" aria-controls="disetujui-tab-pane" aria-selected="true">Disetujui</button>
                            </li>
                            <li class="nav-item" role="presentation">
                              <button class="nav-link text-warning" id="pembayaran-tab" data-bs-toggle="tab" data-bs-target="#pembayaran-tab-pane" type="button" role="tab" aria-controls="pembayaran-tab-pane" aria-selected="false">Pembayaran</button>
                            </li>
                            <li class="nav-item" role="presentation">
                              <button class="nav-link text-danger" id="dikembalikan-tab" data-bs-toggle="tab" data-bs-target="#dikembalikan-tab-pane" type="button" role="tab" aria-controls="dikembalikan-tab-pane" aria-selected="false">Dikembalikan</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active pt-3" id="pengajuan-tab-pane" role="tabpanel" aria-labelledby="pengajuan-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="pengajuan-table">
                                    <tr>
                                        <td>
                                            <div class="card m-0 border-0">
                                                <div class="ribbon-wrapper">
                                                    <div class="ribbon bg-primary" title="Kuota">
                                                        Baru
                                                    </div>
                                                </div>
                                                <div class="card-body d-flex flex-wrap p-3 align-items-center">

                                                    <div class="col-md-6 col-sm-12 mb-sm-2">
                                                        <span class="fw-bold">Uji kebocoran sumber radioaktif</span>
                                                        <div class="text-body-secondary text-start">
                                                            <div>
                                                                <small><b>Start date</b> : 12-03-2023</small>
                                                                <small><b>End date</b> : 12-03-2023</small>
                                                            </div>
                                                            <small><b>Created</b> : 12-02-2023</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 col-sm-5 h5">
                                                        <span class="badge text-bg-secondary">1-5 Sample</span>
                                                    </div>
                                                    <div class="col-md-2 col-sm-5 h5">
                                                        <span class="badge text-bg-info">Antrian 1</span>
                                                    </div>
                                                    <div class="col-md-2 col-sm-2">
                                                        <div class="dropdown">
                                                            <div class="more-option d-flex align-items-center justify-content-center mx-0 mx-md-4" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </div>
                                                            <ul class="dropdown-menu shadow-sm px-2">
                                                                <li class="my-1 cursoron">
                                                                    <a class="dropdown-item dropdown-item-lab subbody text-success">
                                                                        <i class="bi bi-info-circle"></i>&nbsp;Rincian
                                                                    </a>
                                                                </li>
                                                                <li class="my-1 cursoron">
                                                                    <a class="dropdown-item dropdown-item-lab subbody text-danger">
                                                                        <i class="bi bi-trash"></i>&nbsp;Delete
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-none">
                                                        <small><b>Reason:</b> ini alasan kenapa dokumen di balikkan</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="tab-pane fade p-3" id="disetujui-tab-pane" role="tabpanel" aria-labelledby="disetujui-tab" tabindex="0">
                                Lorem ipsum, dolor sit amet consectetur adipisicing elit. Commodi provident illo voluptatibus dolorum maxime expedita maiores omnis sed temporibus voluptate? Sunt facere dolores ipsa porro quaerat expedita praesentium dicta qui!
                            </div>
                            <div class="tab-pane fade p-3" id="pembayaran-tab-pane" role="tabpanel" aria-labelledby="pembayaran-tab" tabindex="0">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Itaque quidem vel minus numquam dignissimos magnam! Rerum nesciunt placeat sit, dolore maxime qui sapiente pariatur. Fuga cupiditate nam asperiores, perspiciatis molestiae blanditiis repudiandae cumque atque neque nisi earum debitis porro! Quidem?
                            </div>
                            <div class="tab-pane fade p-3" id="dikembalikan-tab-pane" role="tabpanel" aria-labelledby="dikembalikan-tab" tabindex="0">
                                Lorem, ipsum dolor sit amet consectetur adipisicing elit. Ad, nostrum dicta aut aspernatur dolorem reprehenderit voluptate totam non deleniti. Ullam incidunt maiores fugiat doloremque. Ipsam odio cupiditate amet eaque labore.
                            </div>
                        </div>
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
            // datatable_permohonan = $('#permohonan-table').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     ajax: "{{ route('permohonan.getData') }}",
            //     columns: [{
            //             data: 'DT_RowIndex',
            //             name: 'DT_RowIndex',
            //             orderable: false
            //         },
            //         {
            //             data: 'nama_layanan',
            //             name: 'nama_layanan'
            //         },
            //         {
            //             data: 'jadwal',
            //             name: 'jadwal'
            //         },
            //         {
            //             data: 'status',
            //             name: 'status'
            //         },
            //         {
            //             data: 'nomor_antrian',
            //             name: 'nomor_antrian'
            //         },
            //         {
            //             data: 'action',
            //             name: 'action',
            //             orderable: false,
            //             searchable: false
            //         },
            //     ]
            // });
            // datatable_permohonan.on('init.dt', function() {
            //     maskReload();
            // });
        });

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/permohonan_api') }}/" + id,
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
                url: "{{ url('api/permohonan_api') }}/" + id,
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
                url: '{{ url('api/permohonan_api') }}/' + id,
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
