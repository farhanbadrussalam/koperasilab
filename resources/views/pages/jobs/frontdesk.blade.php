@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
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

    @include('modal.detail_permohonan')
    @include('pages.jobs.modalDocument')
@endsection
@push('scripts')
    @vite(['resources/js/component/signature.js'])
    <script>
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
            $('#detail_permohonan').modal('hide');

            if(status){
                $('#modal-signature').modal('show');
                let idPermohonan = $('#idPermohonan').val();
                let tmpArr = {
                    'id_hash': idPermohonan,
                    'url': '',
                    'jenis': 'frontdesk'
                };
                $('#nameSignature').html(userActive.name)
                $('#createSignature').attr('data-item', JSON.stringify(tmpArr));
            }else{
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
            if (key) {
                let note = $('#inputNote').val();
                let idPermohonan = $('#idPermohonan').val();

                const formData = new FormData();
                formData.append('id', idPermohonan);
                formData.append('note', note);
                formData.append('status', 9); // di tolak

                ajaxPost('api/permohonan/verifikasi_fd', formData, result => {
                    Swal.fire({
                        icon: 'success',
                        text: 'success',
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        $('#noteModal').modal('hide');
                        reloadTable(1);
                    });
                }, err => {})
            } else {
                $('#noteModal').modal('hide');
            }
        }

        function confirmReturn(idHash){
            Swal.fire({
                title: 'Permohonan dikembalikan ?',
                icon: 'warning',
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
                    ajaxGet(`api/permohonan/show/${idHash}`, false, result => {
                        const data = result.data;

                        const formData = new FormData();
                        formData.append('id', idHash);
                        formData.append('note', data.progress?.note);
                        formData.append('flag', 1);
                        formData.append('ttd_1', false);
                        formData.append('ttd_1_by', false);

                        ajaxPost(`api/permohonan/update/${idHash}`, formData, result2 => {
                            Swal.fire({
                                icon: 'success',
                                text: 'success',
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                $('#detail_permohonan').modal("hide");

                                reloadTable(1);
                                reloadTable(4);
                            });
                        })

                    })


                }
            })
        }

    </script>
@endpush
