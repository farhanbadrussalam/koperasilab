@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active">Pelaksana kontrak</li>
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
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active pt-3" id="layanan-tab-pane" role="tabpanel"
                                aria-labelledby="layanan-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="layanan-table"></table>
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
        let dt_layanan = false;

        $(function () {
            dt_layanan = $('#layanan-table').DataTable({
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
                        d.jobs = 'pelaksana';
                        d.type = 'layanan';
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });
        })

        function btnConfirm(status){
            $('#confirmModal').modal('hide');

            if(status == 2){
                $('#txtStatusSurat').html('Upload tanda terima');
                $('#txtInfoConfirm').html('Setuju');
                $('#statusVerif').val(3);

                $('#noteModal').modal('show');
            }else{
                $('#txtStatusSurat').html('Surat jawaban permohonan');
                $('#txtInfoConfirm').html('Tidak setuju');
                $('#statusVerif').val(9);

                $('#noteModal').modal('show');
            }
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
                    url: "{{ url('api/permohonan/verifikasi_kontrak') }}",
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
                    dt_layanan?.ajax.reload();
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
