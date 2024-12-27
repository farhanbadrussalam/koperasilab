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
    @include('modal.detail_permohonan')
    @include('modal.signature')
@endsection
@push('scripts')
    <script>
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
            $('#detail_permohonan').modal('hide');

            if(status == 2){
                $('#modal-signature').modal('show');

                let idPermohonan = $('#idPermohonan').val();
                let tmpArr = {
                    'id_hash': idPermohonan,
                    'url': '',
                    'jenis': 'pelaksana'
                };
                $('#nameSignature').html(userActive.name)
                $('#createSignature').attr('data-item', JSON.stringify(tmpArr));
            }else{
                $('#noteModal').modal('show');
            }
        }
        function sendConfirm(key) {
            if (key) {
                let note = $('#inputNote').val();

                const idPermohonan = $('#idPermohonan').val();

                const formData = new FormData();
                formData.append('note', note);
                formData.append('status', 9);

                ajaxPost(`api/permohonan/update/${idPermohonan}`, formData, result => {
                    Swal.fire({
                        icon: 'success',
                        text: 'success',
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        $('#noteModal').modal('hide');
                        dt_layanan?.ajax.reload();
                    });
                }, err => {})
            } else {
                $('#noteModal').modal('hide');
            }
        }
    </script>
@endpush
