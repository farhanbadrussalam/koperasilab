@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content col-md-12">
            <div class="container">
                <div class="card card-default color-palette-box shadow">
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
    {{-- Modal buat permohonan --}}
    <div class="modal fade" id="modal_edit_permohonan" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title">Edit Permohonan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row">
                    <form action="#" method="post" id="editPermohonan" class="row">
                        @csrf
                        <div class="mb-3">
                            <label for="permohonan_namalayanan" class="form-label">Nama Layanan</label>
                            <input type="text" class="form-control" id="permohonan_namalayanan" name="namalayanan" readonly disabled>
                            <input type="hidden" name="permohonan_hash" id="permohonan_id" readonly>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="permohonanJenis" class="form-label">Jenis <span class="fw-bold fs-14 text-danger">*</span></label>
                            <select name="desc_biaya" id="permohonanJenis" class="form-control" required>
                                <option value="">-- Select --</option>
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="permohonan_biaya" class="form-label">Biaya</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control rupiah" id="permohonan_biaya" name="biaya" readonly>
                            </div>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="permohonan_noBapeten" class="form-label">Nomor BAPETEN</label>
                            <input type="number" class="form-control" id="permohonan_noBapeten" name="no_bapeten" autocomplete="false" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="permohonan_jenisLimbah" class="form-label">Jenis limbah <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control" id="permohonan_jenisLimbah" name="jenis_limbah" autocomplete="false" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="permohonan_radioaktif" class="form-label">Sumber Radioaktif <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="text" class="form-control" id="permohonan_radioaktif" name="sumber_radioaktif" autocomplete="false" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="permohonan_jumlah" class="form-label">Jumlah <span class="fw-bold fs-14 text-danger">*</span></label>
                            <input type="number" class="form-control" id="permohonan_jumlah" name="jumlah" autocomplete="false" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File pendukung</label>
                            <div id="contentFilePermohonan"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-grey" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" id="save-permohonan"><i class="bi bi-floppy2-fill"></i> Save update</button>
                </div>
            </div>
        </div>
    </div>
    @include('modal.detail_permohonan')
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
        let countFile = 1;
        let arrBiayaLayanan = [];

        const contentFilePermohonan = document.getElementById("contentFilePermohonan");
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
            ajaxDelete(`api/permohonan/destroy/${id}`, result => {
                if (result.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message
                    });
                    reloadTable(1);
                }
            }, error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: message.responseJSON.message
                });
            })
        }

        function modalNote(id) {
            $.ajax({
                url: "{{ url('api/permohonan') }}/" + id,
                method: 'GET',
                dataType: 'json',
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ generateToken() }}`,
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
                    'Authorization': `Bearer {{ generateToken() }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                const data = result.data;
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

        function edit_permohonan(id = false) {
            ajaxGet(`api/permohonan/show/${id}`, false, result => {
                const data = result.data;

                // mengambil layanan
                ajaxGet(`api/layananjasa/getLayanan/${data.layananjasa.layanan_hash}`, false, datalayanan => {

                    arrBiayaLayanan = JSON.parse(datalayanan.data.biaya_layanan)
                    let contentBiaya = ''

                    for (const biaya of arrBiayaLayanan) {
                        let selected = data.jenis_layanan === biaya.desc && "selected"
                        contentBiaya += `<option value="${biaya.desc}" ${selected}>${biaya.desc}</option>`;
                    }
                    $('#permohonanJenis').html(contentBiaya)

                    $('#permohonan_id').val(id);
                    $('#permohonan_namalayanan').val(data.layananjasa.nama_layanan);
                    $('#permohonan_biaya').val(data.tarif);
                    $('#permohonan_noBapeten').val(data.no_bapeten);
                    $('#permohonan_jenisLimbah').val(data.jenis_limbah);
                    $('#permohonan_radioaktif').val(data.sumber_radioaktif);
                    $('#permohonan_jumlah').val(data.jumlah);
                    maskReload();

                    contentFilePermohonan.innerHTML = '';
                    countFile = 1;
                    for (const media of data.media) {
                        tambahFile(media);
                    }

                    $('#modal_edit_permohonan').modal('show');
                })
            })
        }

        function tambahFile(media = false){
            const mainDiv = document.createElement('div')
            mainDiv.className = 'input-group mb-2'

            if(media){
                const mediaDiv = document.createElement('div');
                mediaDiv.innerHTML = printMedia(media, false, { download: false, date: false });
                mediaDiv.className = "form-control p-0";
                mainDiv.appendChild(mediaDiv);
            }else{
                const input1 = document.createElement('input')
                input1.type = 'file'
                input1.className = 'form-control'
                input1.ariaLabel = 'Upload file'
                input1.accept = '.pdf, .docx, .doc, .xls'
                input1.name = 'documents[]'
                mainDiv.appendChild(input1)
            }

            const btnTambah = document.createElement('button')
            btnTambah.className = 'btn btn-primary bi bi-plus-lg'
            btnTambah.type = 'button'
            btnTambah.onclick = () => { tambahFile(false) }

            const btnRemove = document.createElement('button')
            btnRemove.className = 'btn btn-danger bi bi-dash-lg'
            btnRemove.type = 'button'
            btnRemove.onclick = removeFile

            countFile == 1 ? mainDiv.appendChild(btnTambah) : mainDiv.appendChild(btnRemove)

            countFile++;
            contentFilePermohonan.appendChild(mainDiv);
        }

        function removeFile(obj) {
            $(obj.target).parent().remove();
            countFile--;
        }

        $('#permohonanJenis').on('change', obj => {
            const biaya = arrBiayaLayanan.find(d => d.desc == obj.target.value)
            $('#permohonan_biaya').val(biaya.tarif);
        })

        $('#save-permohonan').on('click', obj => {
            const formData = new FormData(document.getElementById('editPermohonan'));
            const id = formData.get('permohonan_hash');
            formData.append('status', 1);

            $(obj.target).attr('disabled', true).html(`
                <span class="spinner-border spinner-border-sm" role="status"></span> Save update
            `)
            ajaxPost(`api/permohonan/update/${id}`, formData, result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        text: result.message,
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modal_edit_permohonan').modal('hide')
                        $(obj.target).attr('disabled', false).html(`
                            <i class="bi bi-floppy2-fill"></i> Save update
                        `)

                        reloadTable(1)
                        reloadTable(4)
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        text: 'Terjadi masalah saat update data',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        $(obj.target).attr('disabled', false).html(`
                            <i class="bi bi-floppy2-fill"></i> Save update
                        `)
                    })
                }
            })

        })
    </script>
@endpush
