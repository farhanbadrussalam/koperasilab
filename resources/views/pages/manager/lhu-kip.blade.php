@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">LHU - KIP</li>
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
                            <button class="nav-link active" id="permohonan-tab" data-bs-toggle="tab"
                                data-bs-target="#permohonan-tab-pane" type="button" role="tab"
                                aria-controls="permohonan-tab-pane" aria-selected="true">Permohonan</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active pt-3" id="permohonan-tab-pane" role="tabpanel"
                            aria-labelledby="permohonan-tab" tabindex="0">
                            <table class="table table-borderless w-100" id="permohonan-table"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@include('pages.manager.modalParaf')
@endsection
@push('scripts')
@vite(['resources/js/pages/manager.js'])
    <script>
        let dt_permohonan = false;

        $(function() {
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
                    url: "{{ route('manager.getData') }}",
                    data: function(d) {
                        d.status = 1
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });

        })
        function ttdDocumentLHU(id, isShow=false) {
            $('#content-ttd').hide();
            $('#content-show').hide();
            $('#actionModalLhu').hide();

            $.ajax({
                url: "{{ url('api/lhu/getDokumenLHU') }}/" + id,
                method: "GET",
                dataType: "json",
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ generateToken() }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                if(result.meta?.code == 200){
                    const data = result.data;

                    let html = `
                        <div class="mt-2 d-flex align-items-center justify-content-between px-3 mx-1 shadow-sm cursoron document border bg-white">
                            <div class="d-flex align-items-center w-100">
                                <div>
                                    <img class="my-3" src="{{ asset('icons') }}/${iconDocument(data.media.file_type)}" alt=""
                                        style="width: 24px; height: 24px;">
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div class="d-flex flex-column">
                                        <a class="caption text-main" href="{{ asset('storage') }}/${data.media.file_path}/${data.media.file_hash}" target="_blank">${data.media.file_ori}</a>
                                        <span class="text-submain caption text-secondary">${dateFormat(data.media.created_at, 1)}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-submain caption" style="margin-top: -3px;">${formatBytes(data.media.file_size)}</small>
                                </div>
                            </div>
                        </div>
                    `;

                    if(isShow){
                        $('#ttd_manager').attr('src', data.ttd_2);
                        $('#content-show').show();
                    }else{
                        $('#content-ttd').show();
                        $('#actionModalLhu').show();
                    }
                    $('#idLhu').val(data.lhu_hash);
                    $('#doc-lampiran').html(html);
                    $('#ttd_penyelialab').attr('src', data.ttd_1);
                    $('#modal-lhu').modal('show');
                }else{
                    console.error(result.meta?.message);
                }
            })
            return;
        }

        function ttdDocumentKIP(id, isShow=false) {
            $('#content-ttd-invoice').hide();
            $('#content-show-invoice').hide();
            $('#actionModalKip').hide();

            $.ajax({
                url: "{{ url('api/lhu/getDokumenKIP') }}/" + id,
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
                $('#txtNoKontrakModal').html(data.no_kontrak);
                $('#txtNamaLayananModal').html(data.permohonan.layananjasa.nama_layanan);
                $('#txtNamaPelangganModal').html(data.permohonan.user.name);
                $('#txtAlamatModal').html(data.permohonan.user.email);
                $('#idKip').val(data.kip_hash);

                let contentRincian = '';
                // items
                contentRincian += `
                    <tr>
                        <td>${data.permohonan.jenis_layanan} x ${data.permohonan.jumlah}</td>
                        <td>${formatRupiah(data.harga)}</td>
                    </tr>
                `;

                // Pajak
                contentRincian += `
                    <tr>
                        <td>PPN 11%</td>
                        <td>${formatRupiah(data.pajak)}</td>
                    </tr>
                `;
                // Total
                total = data.harga + data.pajak;
                contentRincian += `
                    <tr>
                        <th class="w-100">Jumlah</th>
                        <th>${formatRupiah(total)}</th>
                    </tr>
                `;

                if(isShow){
                    $('#ttd_manager_invoice').attr('src', data.ttd_1);
                    $('#content-show-invoice').show();
                }else{
                    $('#content-ttd-invoice').show();
                    $('#actionModalKip').show();
                }

                $('#rincian-table-kip').html(contentRincian);
                $('#modal-kip').modal('show');
            })
        }
    </script>
@endpush
