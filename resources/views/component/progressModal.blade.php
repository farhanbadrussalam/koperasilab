<div class="modal fade" id="updateProgressModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="updateProgressModalLabel">Update progress</h1>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" onclick=""></button>
            </div>
            <div class="modal-body px-1">
                <div class="row mx-2">
                    <div class="col-md-7">
                        <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                            <label for="" class="fw-bold">Tanggal</label>
                            <div>
                                <input type="text" class="form-control" id="dateProgress">
                            </div>
                        </div>
                        <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                            <label for="" class="fw-bold">Status</label>
                            <div>
                                <div class="form-check form-check-inline" id="divReturnProgress">
                                    <input class="form-check-input" type="radio" name="statusProgress" id="statusReturn" value="return">
                                    <label class="form-check-label text-danger" for="statusReturn">Return</label>
                                </div>
                                <div class="form-check form-check-inline" id="divDoneProgress">
                                    <input class="form-check-input" type="radio" name="statusProgress" id="statusDone" value="done" checked>
                                    <label class="form-check-label text-success" for="statusDone">Done</label>
                                </div>
                            </div>
                        </div>
                        <span class="fw-bold">Progress</span>
                        <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                            <select name="prosesNow" id="prosesNow" class="form-select">
                                <option value="">Pilih proses</option>
                            </select>
                            <span class="mx-2">To</span>
                            <input type="text" class="form-control bg-secondary-subtle" name="prosesNext" id="prosesNext" readonly>
                        </div>
                        <div class="col-sm-12 mb-3">
                            <label for="inputNote" class="fw-bold">Note<span class="text-danger ms-1">*</span></label>
                            <textarea name="inputNote" id="inputNote" cols="30" rows="5" class="form-control"></textarea>
                        </div>
                        <div id="divUploadDocLhu">
                            <label for="upload_document" class="col-form-label">Upload Document LHU<span class="text-danger ms-1">*</span></label>
                            <div id="upload_document"></div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label for="" class="fw-bold">Rincian TLD</label>
                        <div id="detailTld" class="overflow-auto" style="max-height: 20rem;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex" id="modalFooter">
                <!-- Footer buttons will be dynamically inserted here -->
                <button class="btn btn-primary" onclick="simpanProgress(this)">Update</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/component/progressModal.js') }}"></script>
@endpush
