@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active">Front desk</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content col-xl-8 col-md-12">
            <div class="container">
                <div class="card card-default color-palette-box shadow">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="layanan-tab" data-bs-toggle="tab"
                                    data-bs-target="#layanan-tab-pane" type="button" role="tab"
                                    aria-controls="layanan-tab-pane" aria-selected="true">Layanan</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="kiplhu-tab" data-bs-toggle="tab"
                                    data-bs-target="#kiplhu-tab-pane" type="button" role="tab"
                                    aria-controls="kiplhu-tab-pane" aria-selected="true">KIP / LHU</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-danger" id="dikembalikan-tab" data-bs-toggle="tab"
                                    data-bs-target="#dikembalikan-tab-pane" type="button" role="tab"
                                    aria-controls="dikembalikan-tab-pane" aria-selected="true">Dikembalikan</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active pt-3" id="layanan-tab-pane" role="tabpanel"
                                aria-labelledby="layanan-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="layanan-table"></table>
                            </div>
                            <div class="tab-pane fade pt-3" id="kiplhu-tab-pane" role="tabpanel"
                                aria-labelledby="kiplhu-tab" tabindex="0">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem porro reiciendis
                                temporibus aperiam quia! Soluta harum mollitia dolorum blanditiis id. Odio, quos! Cum
                                explicabo maxime odio aliquid voluptates animi quidem voluptatum est distinctio dolorum
                                accusamus porro tempore magnam, dolor nobis.
                            </div>
                            <div class="tab-pane fade pt-3" id="dikembalikan-tab-pane" role="tabpanel"
                                aria-labelledby="dikembalikan-tab" tabindex="0">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Ex dolor suscipit magnam nesciunt
                                voluptate. Aperiam laudantium iure incidunt quo consequatur voluptatibus, reiciendis harum!
                                Velit ab aperiam molestias repellendus vero eaque iste porro unde ipsam ipsa laborum,
                                exercitationem, excepturi iure nihil officiis natus necessitatibus aspernatur modi quod
                                molestiae! Alias, rerum repellendus.
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
        let dt_frontdesk = false;
        $(function() {
            // $.ajax({
            //     url: "{{ route('frontdesk.getData') }}",
            //     method: "GET",
            // }).success(result => {
            //     console.log(result);
            // })
            dt_frontdesk = $('#layanan-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                lengthChange: false,
                infoCallback: function( settings, start, end, max, total, pre ) {
                    var api = this.api();
                    var pageInfo = api.page.info();

                    return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
                },
                ajax: {
                    url: "{{ route('frontdesk.getData') }}",
                    data: function(d) {
                        d.status = 1
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            })
        })

        function modalConfirm(id) {
            $.ajax({
                url: "{{ url('api/permohonan/show') }}/" + id,
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
                console.log(data);
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
                if(data.status == 2){
                    $('#divConfirmBtn').hide();
                }else{
                    idPermohonan = id;
                }
                maskReload();
                $('#confirmModal').modal('show');
            })
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

        function btnConfirm(status){
            $('#confirmModal').modal('hide');
            window.statusConfirm = status;

            if(status == 2){
                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('status', status);
                formData.append('tag', 'baru');

                Swal.fire({
                    title: 'Are you sure ?',
                    icon: true,
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    customClass: {
                        confirmButton: 'btn btn-outline-success mx-1',
                    },
                    buttonsStyling: false,
                    reverseButtons: true,
                    width: '20em'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('api/permohonan/update') }}/" + idPermohonan,
                            method: 'POST',
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            headers: {
                                'Authorization': `Bearer {{ $token }}`
                            },
                            data: formData
                        }).done(result => {
                            reloadTable(1);
                        })
                    }
                })
            }else{
                $('#txtStatusSurat').html('Surat jawaban permohonan');
                $('#txtInfoConfirm').html('Tolak');

                $('#noteModal').modal('show');
            }
        }

        function reloadTable(index) {
            switch (index) {
                case 1:
                    dt_frontdesk?.ajax.reload();
                    break;

                default:
                    break;
            }
        }

        function btnVerifikasi(idHash) {
            idPermohonan = idHash;

            $('#txtStatusSurat').html('Upload berkas permohonan');
            $('#txtInfoConfirm').html('Verifikasi');

            $('#noteModal').modal('show');
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

                $.ajax({
                    url: "{{ url('api/permohonan/verifikasi_fd') }}",
                    method: "POST",
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`
                    },
                    data: formData
                }).done(result => {
                    // Swal.fire({
                    //     icon: 'success',
                    //     title: 'Success',
                    //     text: result.message
                    // });
                    $('#noteModal').modal('hide');
                    reloadTable(1);
                }).fail(e => {
                    console.error(e);
                })
            } else {
                $('#noteModal').modal('hide');
            }
        }

        setDropify('init', '#uploadSurat', {
            allowedFileExtentions: ['pdf', 'doc', 'docx'],
            maxFileSize: '5M'
        });
    </script>
@endpush
