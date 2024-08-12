<div class="modal fade" id="detail_permohonan">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Permohonan</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-primary active" id="informasi-tab" data-bs-toggle="tab"
                            data-bs-target="#informasi-tab-pane" type="button" role="tab"
                            aria-controls="informasi-tab-pane" aria-selected="true">Informasi</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-primary" id="dokumen-tab" data-bs-toggle="tab"
                            data-bs-target="#dokumen-tab-pane" type="button" role="tab"
                            aria-controls="dokumen-tab-pane" aria-selected="true">Dokumen pendukung</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active pt-3" id="informasi-tab-pane" role="tabpanel"
                        aria-labelledby="informasi-tab" tabindex="0">
                        <input type="hidden" name="idPermohonan" id="idPermohonan">
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
                            <div class="col-4 fw-bolder">Status</div>
                            <div class="col-8  d-flex">:&nbsp;<span id="txtStatus">Diajukan</span></div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bolder">BAPETEN</div>
                            <div class="col-8">: <span id="txtNoBapeten"></span></div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bolder">Antrian</div>
                            <div class="col-8">: <span id="txtAntrian"></span></div>
                        </div>
                        <div class="row px-2 my-2">
                            <table class="table table-bordered shadow">
                                <thead>
                                    <tr>
                                        <th width="50%">Jenis Limbah</th>
                                        <th width="50%">Sumber Radioaktif</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="txtJeniLimbah"></td>
                                        <td id="txtRadioaktif"></td>
                                        <td id="txtJumlah"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="signature-content" style="display: none">
                            <div class="d-flex justify-content-between m-2 mt-4">
                                <div class="row w-50" id="content-ttd">
                                    <div class="wrapper text-center">
                                        <button class="btn btn-danger btn-sm position-absolute ms-1 mt-1" id="signature-clear"><i class="bi bi-trash"></i></button>
                                        <canvas id="signature-canvas" class="signature-pad border border-success-subtle rounded border-2" width=200 height=114></canvas>
                                        <p class="text-center mb-0">{{ $title }}</p>
                                        <span>(<span id="nameSignature"></span>)</span>
                                    </div>
                                </div>
                                <div class="row w-50" id="content-image-1">
                                    <div class="wrapper text-center">
                                        <img src="{{ asset('icons/default/white.png') }}" width="200" height="114" class="rounded border p-0" alt="ttd penyelia lab" id="ttd_1">
                                        <p class="mt-2 mb-0">Front desk</p>
                                        <span>(<span id="ttd_1_by">______________</span>)</span>
                                    </div>
                                </div>
                                <div class="row w-50" id="content-image-2">
                                    <div class="wrapper text-center">
                                        <img src="{{ asset('icons/default/white.png') }}" width="200" height="114" class="rounded border p-0" alt="ttd penyelia lab" id="ttd_2">
                                        <p class="mt-2 mb-0">Pelaksana Kontrak</p>
                                        <span>(<span id="ttd_2_by">______________</span>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade p-3" id="dokumen-tab-pane" role="tabpanel" aria-labelledby="dokumen-tab"
                        tabindex="0">
                        <div id="tmpDokumenPendukung">

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="divConfirmBtn">
                <div class="d-flex w-100">
                    <button class="btn btn-danger me-auto" id="btnNo" onclick="btnConfirm(false)">Tidak lengkap</button>
                    <button class="btn btn-primary" id="createSignature">Lengkap</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

{{-- Modal note --}}
<div class="modal fade" id="noteModal" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-center w-100" id="txtInfoConfirm">Tolak</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- note --}}
                    <div class="mb-2">
                        <label for="inputNote" class="form-label">Note <span
                                class="fw-bold fs-14 text-danger">*</span></label>
                        <textarea name="note" id="inputNote" cols="30" rows="3" class="form-control"
                            placeholder="Masukan note"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" role="button" onclick="sendConfirm(false)">Batal</button>
                <button class="btn btn-primary" role="button" onclick="sendConfirm(true)">Kirim</button>
            </div>
        </div>
    </div>
</div>

<script>
    function show_detail_permohonan(id) {
        ajaxGet(`api/permohonan/show/${id}`, false, result => {
            const data = result.data;

            $('#idPermohonan').val(id);
            $('#txtNamaPelanggan').html(data.user.name);
            $('#txtNamaLayanan').html(data.layananjasa.nama_layanan);
            $('#txtJenisLayanan').html(data.jenis_layanan);
            $('#txtHarga').html(data.tarif);
            $('#txtStatus').html(statusFormat('permohonan', data.status));
            $('#txtNoBapeten').html(data.no_bapeten);
            $('#txtAntrian').html(data.nomor_antrian);
            $('#txtJeniLimbah').html(data.jenis_limbah);
            $('#txtRadioaktif').html(data.sumber_radioaktif);
            $('#txtJumlah').html(data.jumlah);

            let allDocument = '';
            // ambil dokumen petugas
            if(data.detailPermohonan?.media){
                allDocument += `<label>Petugas</label>`;
                allDocument += printMedia(data.detailPermohonan.media);
            }

            // ambil dokumen pelanggan
            allDocument += `<label class="mt-3">Pelanggan</label>`;
            for (const media of data.media) {
                allDocument += printMedia(media, "dokumen/permohonan");
            }

            $('#tmpDokumenPendukung').html(allDocument);

            $('#divConfirmBtn').show();
            $('#signature-content').show();
            if(role.includes('Pelanggan')){
                $('#divConfirmBtn').hide();
                $('#signature-content').hide();
            }else{
                if(data.status == 2 && permission.find(d => d.name == 'Otorisasi-Front desk')){
                    $('#divConfirmBtn').hide();
                }else if(data.status == 2 && data.flag == 2){
                    $('#btnNo').html('Tidak setuju');
                    $('#btnYes').html('Setuju');
                    idPermohonan = id;
                }else if(data.status == 3 && permission.find(d => d.name == 'Otorisasi-Penyelia LAB')){
                    $('#divConfirmBtn').hide();
                }else if(data.status == 3 && permission.find(d => d.name == 'Otorisasi-Pelaksana LAB')){
                    $('#divConfirmBtn').hide();
                }else if(data.status == 3 && permissionInRole.find(d => d.name == 'Keuangan')){
                    $('#divConfirmBtn').hide();
                }else{
                    idPermohonan = id;
                }

                if(permission.find(d => d.name == 'Otorisasi-Front desk')){
                    // signature
                    let tmpArr = {
                        'id_hash': idPermohonan,
                        'url': '',
                        'jenis': 'frontdesk'
                    };
                    $('#nameSignature').html(userActive.name)
                    $('#signature-canvas').attr('data-item', JSON.stringify(tmpArr));
                }else if(permission.find(d => d.name == 'Otorisasi-Pelaksana kontrak')){
                    // signature
                    let tmpArr = {
                        'id_hash': idPermohonan,
                        'url': '',
                        'jenis': 'pelaksana'
                    };
                    $('#nameSignature').html(userActive.name)
                    $('#signature-canvas').attr('data-item', JSON.stringify(tmpArr));
                }
            }
            maskReload();

            // Signature
            let title = "{{ $title }}";

            $('#content-ttd').show();
            $('#content-image-1').show();
            $('#content-image-2').show();

            if(data.ttd_1 && data.ttd_2){
                $('#content-ttd').hide();
            }

            if(title == 'frontdesk'){
                $('#content-image-1').hide();
            }else if(title == 'Pelaksana kontrak'){
                $('#content-image-2').hide();
            }

            data.ttd_1 && $('#ttd_1').attr('src', data.ttd_1);
            data.ttd_2 && $('#ttd_2').attr('src', data.ttd_2);
            data.signature_1?.name && $('#ttd_1_by').html(data.signature_1.name);
            data.signature_2?.name && $('#ttd_2_by').html(data.signature_2.name);

            $('#detail_permohonan').modal('show');
        })
    }

</script>
