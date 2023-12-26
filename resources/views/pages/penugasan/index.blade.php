@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
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
                        Permohonan layanan
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless w-100" id="permohonan-table"></table>
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
@include('pages.jadwal.info')
@endsection
@push('scripts')
    <script>
        let dt_permohonan = false;

        dt_permohonan = $('#permohonan-table').DataTable({
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

        function showPetugas(id) {
            $.ajax({
                url: "{{ url('api/getJadwalPetugas') }}",
                method: "GET",
                dataType: 'json',
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                },
                data: {
                    idJadwal: id
                }
            }).done(result => {
                let content = '';
                for (const data of result.data.petugas) {
                    let contentOtorisasi = '';
                    for (const otorisasi of data.otorisasi) {
                        contentOtorisasi += `<button class="btn btn-outline-dark btn-sm m-1" role="button">${stringSplit(otorisasi.name, 'Otorisasi-')}</button>`;
                    }

                    let pj = data.petugas.user_hash == result.data.pj ? `<small class="text-danger">Penanggung jawab</small>` : '';
                    content += `
                        <div class="card m-0 mb-2">
                            <div class="card-body d-flex p-2">
                                <div class="flex-grow-1 d-flex my-auto">
                                    <div>
                                        <img src="${data.avatar}" alt="Avatar" onerror="this.src='{{ asset('assets/img/default-avatar.jpg') }}'" style="width: 3em;" class="img-circle border shadow-sm">
                                    </div>
                                    <div class="px-3 my-auto">
                                        <div class="lh-1">${data.petugas.name}</div>
                                        ${pj}
                                        <div class="lh-1">${data.petugas.email}</div>
                                    </div>
                                </div>
                                <div class="p-2 m-auto">
                                    <div class="d-flex flex-wrap justify-content-end">
                                        ${contentOtorisasi}
                                    </div>
                                </div>
                                <div class="p-2 m-auto">${statusFormat('jadwal', data.status)}</div>
                            </div>
                        </div>
                    `;
                }

                $('#content-petugas').html(content);
                $('#infoModal').modal('show');
            })
        }
    </script>
@endpush
