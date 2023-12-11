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
        <section class="content col-md-12">
            <div class="container">
                <div class="card card-default color-palette-box shadow">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="layanan-tab" data-bs-toggle="tab"
                                    data-bs-target="#layanan-tab-pane" type="button" role="tab"
                                    aria-controls="layanan-tab-pane" aria-selected="true">Layanan</button>
                            </li>
                            {{-- <li class="nav-item" role="presentation">
                                <button class="nav-link" id="diteruskan-tab" data-bs-toggle="tab"
                                    data-bs-target="#diteruskan-tab-pane" type="button" role="tab"
                                    aria-controls="diteruskan-tab-pane" aria-selected="true">Diteruskan</button>
                            </li> --}}
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
                            {{-- <div class="tab-pane fade pt-3" id="diteruskan-tab-pane" role="tabpanel"
                                aria-labelledby="kiplhu-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="diteruskan-table"></table>
                            </div> --}}
                            <div class="tab-pane fade pt-3" id="kiplhu-tab-pane" role="tabpanel"
                                aria-labelledby="kiplhu-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="lhukip-table"></table>
                            </div>
                            <div class="tab-pane fade pt-3" id="dikembalikan-tab-pane" role="tabpanel"
                                aria-labelledby="dikembalikan-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="dikembalikan-table"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @include('pages.permohonan.confirm')
    @include('pages.jobs.modalDocument')
@endsection
@push('scripts')
    <script>
        let idPermohonan = false;
        let dt_frontdesk = false;
        let dt_diteruskan = false;
        let dt_lhukip = false;
        let dt_return = false;
        $(function() {
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
                    url: "{{ route('jobs.getData') }}",
                    data: function(d) {
                        d.jobs = 'frontdesk';
                        d.type = 'layanan';
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });

            // dt_diteruskan = $('#diteruskan-table').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     searching: false,
            //     ordering: false,
            //     lengthChange: false,
            //     infoCallback: function( settings, start, end, max, total, pre ) {
            //         var api = this.api();
            //         var pageInfo = api.page.info();

            //         return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
            //     },
            //     ajax: {
            //         url: "{{ route('jobs.getData') }}",
            //         data: function(d) {
            //             d.jobs = 'frontdesk';
            //             d.type = 'diteruskan';
            //         }
            //     },
            //     columns: [
            //         { data: 'content', name: 'content', orderable: false, searchable: false}
            //     ]
            // });

            dt_lhukip = $('#lhukip-table').DataTable({
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
                    url: "{{ route('jobs.getData') }}",
                    data: function(d) {
                        d.jobs = 'frontdesk';
                        d.type = 'lhukip';
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });

            dt_return = $('#dikembalikan-table').DataTable({
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
                    url: "{{ route('jobs.getData') }}",
                    data: function(d) {
                        d.jobs = 'frontdesk';
                        d.type = 'return';
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });
        })

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
                        cancelButton: 'btn btn-outline-danger mx-1'
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
                $('#statusVerif').val(9);

                $('#noteModal').modal('show');
            }
        }

        function reloadTable(index) {
            switch (index) {
                case 1:
                    dt_frontdesk?.ajax.reload();
                    break;
                case 2:
                    dt_lhukip?.ajax.reload();
                    break;
                case 4:
                    dt_return?.ajax.reload();
                    break;
                default:
                    break;
            }
        }

        function btnVerifikasi(idHash) {
            idPermohonan = idHash;

            $('#txtStatusSurat').html('Upload berkas permohonan');
            $('#txtInfoConfirm').html('Verifikasi');
            $('#statusVerif').val(2);

            $('#noteModal').modal('show');
        }

        function sendConfirm(key) {
            if (key == 1) {
                let note = $('#inputNote').val();
                let documenSurat = $('#uploadSurat')[0].files[0];
                let status = $('#statusVerif').val();

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('note', note);
                formData.append('id', idPermohonan);
                formData.append('file', documenSurat);
                formData.append('status', status);

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
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message
                    });
                    $('#noteModal').modal('hide');
                    reloadTable(1);
                }).fail(e => {
                    console.error(e);
                })
            } else {
                $('#noteModal').modal('hide');
            }
        }

        function confirmReturn(idHash){
            Swal.fire({
                title: 'Permohonan dikembalikan ?',
                icon: false,
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                customClass: {
                    confirmButton: 'btn btn-outline-success mx-1',
                    cancelButton: 'btn btn-outline-danger mx-1'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('note', '');
                    formData.append('id', idHash);
                    formData.append('status', 9);
                    formData.append('type', 'return');
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        reloadTable(4);
                    }).fail(e => {
                        console.error(e);
                    })
                }
            })
        }

        setDropify('init', '#uploadSurat', {
            allowedFileExtentions: ['pdf', 'doc', 'docx'],
            maxFileSize: '5M'
        });
    </script>
@endpush
