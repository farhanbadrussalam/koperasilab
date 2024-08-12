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
            const contentPertanyaan = document.getElementById('content-pertanyaan');

            contentPertanyaan.innerHTML = '';
            ajaxGet(`api/lhu/getDokumenLHU/${id}`, false, result => {
                if(result.meta?.code == 200){
                    const data = result.data;

                    if(isShow){
                        $('#ttd_manager').attr('src', data.ttd_3);
                        $('#content-show').show();
                    }else{
                        $('#content-ttd').show();
                        $('#actionModalLhu').show();
                    }

                    for (const jawaban of data.jawaban) {
                        let contentJwb = createPertanyaan(jawaban);
                        contentPertanyaan.appendChild(contentJwb);
                    }

                    $('#idLhu').val(data.lhu_hash);
                    $('#ttd_penyelialab').attr('src', data.ttd_1);
                    $('#ttd_pelaksanalab').attr('src', data.ttd_2);
                    $('#modal-lhu').modal('show');
                }else{
                    console.error(result.meta?.message);
                }
            });
            return;
        }

        function ttdDocumentKIP(id, isShow=false) {
            $('#content-ttd-invoice').hide();
            $('#content-show-invoice').hide();
            $('#actionModalKip').hide();

            ajaxGet(`api/lhu/getDokumenKIP/${id}`, false, result => {
                const data = result.data;

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
            });
        }

        function createPertanyaan(list){
            const mainDiv = document.createElement('div');
            mainDiv.className = 'col-md-6 mb-2'

            const labelP = document.createElement('label')
            labelP.className = 'fw-bolder';
            labelP.innerHTML = list.pertanyaan.title

            const inputP = document.createElement('input')
            inputP.className = 'form-control'
            inputP.disabled = true
            inputP.value = list.jawaban

            mainDiv.appendChild(labelP)
            mainDiv.appendChild(inputP)

            return mainDiv;
        }
    </script>
@endpush
