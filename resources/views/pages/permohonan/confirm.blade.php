<div class="modal fade" id="confirmModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Permohonan</h4>
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
                <div class="row">
                    <div class="fw-bolder">Dokumen Pendukung :</div>
                    <div id="tmpDokumenPendukung">

                    </div>
                </div>
            </div>
            <div class="modal-footer" id="divConfirmBtn">
                <div class="d-flex w-100">
                    <button class="btn btn-danger me-auto" onclick="btnConfirm(9)">Tolak</button>
                    <button class="btn btn-primary" onclick="btnConfirm(2)">Setuju</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="noteModal" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-center w-100" id="txtInfoConfirm">Setuju</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- note --}}
                    <div class="mb-2">
                        <label for="inputNote" class="form-label">Note <span
                                class="fw-bold fs-14 text-danger">*</span></label>
                        <textarea name="note" id="inputNote" cols="30" rows="3" class="form-control" placeholder="Masukan note"></textarea>
                    </div>
                    {{-- Upload Surat --}}
                    <div class="mb-2">
                        <label for="uploadSurat" class="form-label">Surat <span id="txtStatusSurat"></span>
                            permohonan<span class="fw-bold fs-14 text-danger">*</span></label>
                        <div class="card mb-0" style="height: 100px;">
                            <input type="file" name="uploadSurat" id="uploadSurat" class="form-control dropify">
                        </div>
                        <span class="mb-3 text-muted" style="font-size: 12px;">Allowed file types: pdf,doc,docx.
                            Recommend size under 5MB.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" role="button" onclick="sendConfirm(2)">Batal</button>
                <button class="btn btn-primary" role="button" onclick="sendConfirm(1)">Kirim</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="previewNoteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Note</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- note --}}
                    <div class="mb-2">
                        <textarea name="note" id="txtNote" cols="30" rows="3" class="form-control"
                            placeholder="Masukan note" readonly></textarea>
                    </div>
                    {{-- Upload Surat --}}
                    <div class="mb-2">
                        <label for="uploadSurat" class="form-label">Surat <span id="txtStatusNote"></span>
                            permohonan</label>
                        <div id="tmpSurat"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
