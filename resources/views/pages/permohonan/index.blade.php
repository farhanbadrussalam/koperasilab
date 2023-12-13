@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active">Permohonan</li>
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
                        @can('Permohonan.create')
                            <a href="{{ route('permohonan.create') }}" class="btn btn-primary btn-sm">Add permohonan</a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                              <button class="nav-link text-primary active" id="pengajuan-tab" onclick="reloadTable(1)" data-bs-toggle="tab" data-bs-target="#pengajuan-tab-pane" type="button" role="tab" aria-controls="pengajuan-tab-pane" aria-selected="true">Pengajuan</button>
                            </li>
                            <li class="nav-item" role="presentation">
                              <button class="nav-link text-success" id="disetujui-tab" onclick="reloadTable(2)" data-bs-toggle="tab" data-bs-target="#disetujui-tab-pane" type="button" role="tab" aria-controls="disetujui-tab-pane" aria-selected="true">Disetujui</button>
                            </li>
                            <li class="nav-item" role="presentation">
                              <button class="nav-link text-warning" id="pembayaran-tab" onclick="reloadTable(3)" data-bs-toggle="tab" data-bs-target="#pembayaran-tab-pane" type="button" role="tab" aria-controls="pembayaran-tab-pane" aria-selected="false">Pembayaran</button>
                            </li>
                            <li class="nav-item" role="presentation">
                              <button class="nav-link text-danger" id="dikembalikan-tab" onclick="reloadTable(4)" data-bs-toggle="tab" data-bs-target="#dikembalikan-tab-pane" type="button" role="tab" aria-controls="dikembalikan-tab-pane" aria-selected="false">Dikembalikan</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active pt-3" id="pengajuan-tab-pane" role="tabpanel" aria-labelledby="pengajuan-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="pengajuan-table"></table>
                            </div>
                            <div class="tab-pane fade p-3" id="disetujui-tab-pane" role="tabpanel" aria-labelledby="disetujui-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="disetujui-table"></table>
                            </div>
                            <div class="tab-pane fade p-3" id="pembayaran-tab-pane" role="tabpanel" aria-labelledby="pembayaran-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="pembayaran-table"></table>
                            </div>
                            <div class="tab-pane fade p-3" id="dikembalikan-tab-pane" role="tabpanel" aria-labelledby="dikembalikan-tab" tabindex="0">
                                <table class="table table-borderless w-100" id="dikembalikan-table"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @include('pages.permohonan.confirm')
    @include('pages.keuangan.modalinvoice')
@endsection
@push('scripts')
    <script>
        let idPermohonan = false;
        let datatable_permohonan = false;
        let dt_pengajuan = false;
        let dt_disetujui = false;
        let dt_pembayaran = false;
        let dt_return = false;
        $(function() {
            dt_pengajuan = $('#pengajuan-table').DataTable({
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
                    url: "{{ route('permohonan.getData') }}",
                    data: function(d) {
                        d.status = 1
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });

            dt_disetujui = $('#disetujui-table').DataTable({
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
                    url: "{{ route('permohonan.getData') }}",
                    data: function(d) {
                        d.status = 2
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });

            dt_pembayaran = $('#pembayaran-table').DataTable({
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
                    url: "{{ route('permohonan.getData') }}",
                    data: function(d) {
                        d.status = 3
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
                    url: "{{ route('permohonan.getData') }}",
                    data: function(d) {
                        d.status = 9
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });
            // datatable_permohonan.on('init.dt', function() {
            //     maskReload();
            // });
        });

        function reloadTable(index) {
            switch (index) {
                case 1:
                    dt_pengajuan?.ajax.reload();
                    break;
                case 2:
                    dt_disetujui?.ajax.reload();
                    break;
                case 3:
                    dt_pembayaran?.ajax.reload();
                    break;
                case 4:
                    dt_return?.ajax.reload();
                    break;
            }
        }

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/permohonan/destroy') }}/" + id,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    }
                }).done((result) => {
                    if (result.message) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        reloadTable(1);
                    }
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                });
            });
        }

        // function modalConfirm(id) {
        //     $.ajax({
        //         url: "{{ url('api/permohonan/show') }}/" + id,
        //         method: 'GET',
        //         dataType: 'json',
        //         processing: true,
        //         serverSide: true,
        //         headers: {
        //             'Authorization': `Bearer {{ $token }}`,
        //             'Content-Type': 'application/json'
        //         }
        //     }).done(result => {
        //         const data = result.data;
        //         $('#txtNamaPelanggan').html(data.user.name);
        //         $('#txtNamaLayanan').html(data.layananjasa.nama_layanan);
        //         $('#txtJenisLayanan').html(data.jenis_layanan);
        //         $('#txtHarga').html(data.tarif);
        //         $('#txtStart').html(data.jadwal.date_mulai);
        //         $('#txtEnd').html(data.jadwal.date_end);
        //         $('#txtStatus').html(statusFormat('permohonan', data.status));
        //         $('#txtNoBapeten').html(data.no_bapeten);
        //         $('#txtAntrian').html(data.nomor_antrian);
        //         $('#txtJeniLimbah').html(data.jenis_limbah);
        //         $('#txtRadioaktif').html(data.sumber_radioaktif);
        //         $('#txtJumlah').html(data.jumlah);

        //         // ambil dokumen
        //         let dokumen = ``;
        //         for (const media of data.media) {
        //             dokumen += printMedia(media, "permohonan");
        //         }
        //         $('#tmpDokumenPendukung').html(dokumen);
        //         $('#divConfirmBtn').hide();
        //         maskReload();
        //         idPermohonan = id;
        //         $('#confirmModal').modal('show');
        //     })
        // }

        function modalNote(id) {
            $.ajax({
                url: "{{ url('api/permohonan') }}/" + id,
                method: 'GET',
                dataType: 'json',
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                $('#txtNote').html(result.data.note);
                $('#tmpSurat').html(printMedia(result.data.surat_terbit, "permohonan"));
                if (result.data.status == 2) {
                    $('#txtStatusNote').html('rekomendasi');
                } else if (result.data.status == 9) {
                    $('#txtStatusNote').html('jawaban');
                }
                $('#previewNoteModal').modal('show');
            })
        }

        function btnDetailPayment(id){
            $.ajax({
                url: "{{ url('api/permohonan/show') }}/" + id,
                method: 'GET',
                dataType: 'json',
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                const data = result.data;
                console.log(data);
                $('#txtNoKontrakModal').html(data.no_kontrak);
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

                $('#inputNoKontrak').val(data.no_kontrak);
                $('#inputPajak').val(totalPajak);
                $('#inputHarga').val(totalItems);

                $('#rincian-table').html(contentRincian);

                $('#actionModalInvoice').hide();

                $('#contentBuktiPembayaran').show();
                $('#imgBuktiPembayaran').attr('src', `{{ asset('storage') }}/${data.tbl_kip.bukti.file_path}/${data.tbl_kip.bukti.file_hash}`);

                $('#moda-invoice').modal('show');
            })
        }

        // function printMedia(media, folder){
        //     return `
        //     <a
        //         class="mt-2 d-flex align-items-center justify-content-between px-3 mx-1 shadow-sm cursoron document border"
        //         href="{{ asset('storage/dokumen') }}/${folder}/${media.file_hash}"
        //         target="_blank">
        //             <div class="d-flex align-items-center">
        //                 <img class="my-3" src="{{ asset('icons') }}/${iconDocument(media.file_type)}" alt=""
        //                     style="width: 24px; height: 24px;">
        //                 <div class="d-flex flex-column ms-2">
        //                     <span class="caption text-main">${media.file_ori}</span>
        //                     <span class="text-submain caption" style="margin-top: -3px;">${formatBytes(media.file_size)}</span>
        //                 </div>
        //             </div>
        //         <div class="d-flex align-items-center"></div>
        //     </a>
        //     `;
        // }

        setDropify('init', '#uploadSurat', {
            allowedFileExtentions: ['pdf', 'doc', 'docx'],
            maxFileSize: '5M'
        });
    </script>
@endpush
