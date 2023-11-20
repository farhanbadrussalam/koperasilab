@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Penyelia LAB</li>
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
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="lhu-tab" data-bs-toggle="tab"
                                data-bs-target="#lhu-tab-pane" type="button" role="tab"
                                aria-controls="lhu-tab-pane" aria-selected="true">Data LHU</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active pt-3" id="layanan-tab-pane" role="tabpanel"
                            aria-labelledby="layanan-tab" tabindex="0">
                            <table class="table table-borderless w-100" id="layanan-table"></table>
                        </div>
                        <div class="tab-pane fade pt-3" id="lhu-tab-pane" role="tabpanel"
                            aria-labelledby="lhu-tab" tabindex="0">
                            <table class="table table-borderless w-100" id="lhu-table"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Modal ttd confirm --}}
<div class="modal fade" id="modal-confirm-ttd">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <input type="hidden" name="idLhu" id="idLhu">
                <h3>Apakah data ini valid ?</h3>
                <div class="wrapper">
                    <button class="btn btn-danger btn-sm position-absolute ms-2 mt-2" id="signature-clear">Clear</button>
                    <canvas id="signature-pad" class="signature-pad border border-success-subtle rounded border-3" width=400 height=200></canvas>
                    <h4>Signature</h4>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex w-100">
                    <button class="btn btn-danger me-auto" id="btn-invalid">Tidak valid</button>
                    <button class="btn btn-primary" id="btn-valid">Valid</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <input type="hidden" name="bearer" id="bearer-token" value="{{ $token }}">
    <input type="hidden" name="csrf" id="csrf-token" value="{{ csrf_token() }}">
    <input type="hidden" id="base_url" value="{{ url('') }}">
</div>
@include('pages.permohonan.confirm')
@include('pages.jobs.createSurat')
@endsection
@push('scripts')
@vite(['resources/js/pages/penyelia.js'])
<script>
    let idPermohonan = false;
    let dt_layanan = false;
    let dt_lhu = false;

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
                    d.jobs = 'penyelia';
                    d.type = 'layanan';
                }
            },
            columns: [
                { data: 'content', name: 'content', orderable: false, searchable: false}
            ]
        });

        dt_lhu = $('#lhu-table').DataTable({
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
                url: "{{ route('jobs.getDataLhu') }}",
                data: function(d) {
                    d.jobs = 'penyelia';
                    d.type = 'lhu';
                }
            },
            columns: [
                { data: 'content', name: 'content', orderable: false, searchable: false}
            ]
        });

        // initialisasi signature

    })

    function createSurat(idPermohonan){
        $.ajax({
            url: "{{ url('api/permohonan/show') }}/" + idPermohonan,
            method: 'GET',
            dataType: 'json',
            processing: true,
            serverSide: true,
            headers: {
                'Authorization': `Bearer {{ $token }}`,
                'Content-Type': 'application/json'
            }
        }).done(result => {
            result = result.data;
            $('#txtTugas').val(result.layananjasa.nama_layanan);
            $('#txtCustomer').val(result.user.name);
            $('#txtJumlah').val(result.jumlah);
            $('#noKontrak').val(result.no_kontrak);
            $('#txtTanggal').val(`${dateFormat(result.jadwal.date_mulai, 2)} - ${dateFormat(result.jadwal.date_selesai, 2)}`);
            $('#create-surat').modal('show');
        })
    }

    function btnConfirm(idLhu){
        $('#idLhu').val(idLhu);
        $('#modal-confirm-ttd').modal('show');
    }
</script>
@endpush
