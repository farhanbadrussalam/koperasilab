<div class="modal fade" id="confirmModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Permohonan</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
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
                    <button class="btn btn-danger me-auto" id="btnNo" onclick="btnConfirm(9)">Tolak</button>
                    <button class="btn btn-primary" id="btnYes" onclick="btnConfirm(2)">Lengkap</button>
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
                        <textarea name="note" id="inputNote" cols="30" rows="3" class="form-control"
                            placeholder="Masukan note"></textarea>
                    </div>
                    {{-- Upload Surat --}}
                    <div class="mb-2">
                        <label for="uploadSurat" class="form-label"><span id="txtStatusSurat"></span><span
                                class="fw-bold fs-14 text-danger">*</span></label>
                        <div class="card mb-0" style="height: 100px;">
                            <input type="file" name="uploadSurat" id="uploadSurat" class="form-control dropify">
                        </div>
                        <span class="mb-3 text-muted" style="font-size: 12px;">Allowed file types: pdf,doc,docx.
                            Recommend size under 5MB.</span>
                    </div>
                    {{-- Status --}}
                    <input type="hidden" name="statusVerif" id="statusVerif">
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

<script>
    const role = @json(Auth::user()->getRoleNames());
    const permission = @json(Auth::user()->getDirectPermissions());

    function modalConfirm(id) {
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

                $('#txtNamaPelanggan').html(data.user.name);
                $('#txtNamaLayanan').html(data.layananjasa.nama_layanan);
                $('#txtJenisLayanan').html(data.jenis_layanan);
                $('#txtHarga').html(data.tarif);
                $('#txtStart').html(data.jadwal.date_mulai);
                $('#txtEnd').html(data.jadwal.date_end);
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
                if(role.includes('Pelanggan')){
                    $('#divConfirmBtn').hide();
                }else{
                    if(data.status == 2 && permission.find(d => d.name == 'Otorisasi-Front desk')){
                        $('#divConfirmBtn').hide();
                    }else if(data.status == 2 && data.flag == 2){
                        $('#btnNo').html('Tidak setuju');
                        $('#btnYes').html('Setuju');
                        idPermohonan = id;
                    }else{
                        idPermohonan = id;
                    }
                }
                maskReload();
                $('#confirmModal').modal('show');
            })
    }

    function printMedia(media, folder=false){
        return `
            <div
                class="mt-2 d-flex align-items-center justify-content-between px-3 mx-1 shadow-sm cursoron document border">
                    <div class="d-flex align-items-center w-100">
                        <div>
                            <img class="my-3" src="{{ asset('icons') }}/${iconDocument(media.file_type)}" alt=""
                                style="width: 24px; height: 24px;">
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="d-flex flex-column">
                                <a class="caption text-main" href="{{ asset('storage') }}/${folder ? folder : media.file_path}/${media.file_hash}" target="_blank">${media.file_ori}</a>
                                <span class="text-submain caption text-secondary">${dateFormat(media.created_at, 1)}</span>
                            </div>
                        </div>
                        <div class="p-1">
                            <small class="text-submain caption" style="margin-top: -3px;">${formatBytes(media.file_size)}</small>
                        </div>
                        <div class="p-1">
                            <button class="btn btn-sm btn-link" title="Download file"><i class="bi bi-download"></i></button>
                        </div>
                    </div>
                <div class="d-flex align-items-center"></div>
            </div>
            `;
    }
</script>
