@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Keuangan</li>
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
@include('pages.keuangan.modalinvoice')
@endsection
@push('scripts')
    <script>
        let dt_keuangan = false;

        $(function () {
            dt_keuangan = $('#layanan-table').DataTable({
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
                        d.jobs = 'keuangan';
                        d.type = 'layanan';
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });
        })

        function createInvoice(id, show = false){
            ajaxGet(`api/permohonan/show/${id}`, false, result => {
                const data = result.data;
                // $('#txtNoKontrakModal').html(data.no_kontrak);
                $('#txtNamaLayananModal').html(data.layananjasa.nama_layanan);
                $('#txtNamaPelangganModal').html(data.user.name);
                $('#txtAlamatModal').html(data.user.email);

                // Rincian
                let contentRincian = '';
                let tarif = data.tarif;
                let total = 0;

                // items
                let totalItems = tarif * data.jumlah;
                contentRincian += `
                    <tr>
                        <td>${data.jenis_layanan} x ${data.jumlah}</td>
                        <td>${formatRupiah(totalItems)}</td>
                    </tr>
                `;

                // Pajak
                let totalPajak = totalItems * (11/100);
                contentRincian += `
                    <tr>
                        <td>PPN 11%</td>
                        <td>${formatRupiah(totalPajak)}</td>
                    </tr>
                `;
                // Total
                total = totalItems + totalPajak;
                contentRincian += `
                    <tr>
                        <th class="w-100">Jumlah</th>
                        <th>${formatRupiah(total)}</th>
                    </tr>
                `;

                $('#inputIdPermohonan').val(id);
                $('#inputPajak').val(totalPajak);
                $('#inputHarga').val(totalItems);

                $('#rincian-table').html(contentRincian);
                $('#actionModalInvoice').show();

                if(show){
                    $('#actionModalInvoice').hide();
                }
                $('#moda-invoice').modal('show');
            });
        }

        function showBukti(id){
            $.ajax({
                url: "{{ url('api/permohonan/show') }}/" + id,
                method: 'GET',
                dataType: 'json',
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ generateToken() }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                const data = result.data;

                $('#imgBukti').attr('src', `{{ asset('storage') }}/${data.tbl_kip.bukti.file_path}/${data.tbl_kip.bukti.file_hash}`);
                $('#btn-cetak').attr('href', `{{ url('laporan/kwitansi') }}/${data.tbl_kip.kip_hash}`)
                $('#modal-bukti').modal('show');
            })
        }
    </script>
@endpush
