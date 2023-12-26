@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Jadwal Permohonan</li>
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
                        Jadwal
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless w-100" id="jadwal-table"></table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="confirmModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Jadwal</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-4 fw-bolder">Nama Pelanggan</div>
                    <div class="col-8">: <span id="txtNamaPelanggan"></span></div>
                </div>
                <div class="row">
                    <div class="col-4 fw-bolder">Nama Layanan</div>
                    <div class="col-8">: <span id="txtNamaLayanan">Uji Kebocoran Sumber Radioaktif</span></div>
                </div>
                <div class="row">
                    <div class="col-4 fw-bolder">Jenis Layanan</div>
                    <div class="col-8">: <span id="txtJenisLayanan">1-5 Sample</span></div>
                </div>
                <div class="row">
                    <div class="col-4 fw-bolder">Harga</div>
                    <div class="col-8">: <span id="txtHarga" class="rupiah">Rp 1.700.000</span></div>
                </div>
                <div class="row">
                    <div class="col-4 fw-bolder">Start</div>
                    <div class="col-8">: <span id="txtStart">2023-07-26 08:00:00</span></div>
                </div>
                <div class="row">
                    <div class="col-4 fw-bolder">End</div>
                    <div class="col-8">: <span id="txtEnd">2023-07-26 17:00:00</span></div>
                </div>
                <div class="row">
                    <div class="col-4 fw-bolder">Status pembayaran</div>
                    <div class="col-8">: <span id="txtStatusPembayaran">Lunas</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        let dt_jadwal = false;

        dt_jadwal = $('#jadwal-table').DataTable({
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
                url: "{{ route('penugasan.getWaktuJadwal') }}",
                data: function(d) {
                    d.flag = 1
                }
            },
            columns: [
                { data: 'content', name: 'content', orderable: false, searchable: false}
            ]
        });

        function createJadwal(id){

        }
    </script>
@endpush
