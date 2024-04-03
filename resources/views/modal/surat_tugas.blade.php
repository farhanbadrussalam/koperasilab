<div class="modal fade" id="modal-surat-tugas">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create surat tugas</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body row">
                <form action="#" method="post" id="formSuratTugas" class="row">
                    @csrf
                    <div class="mb-3 col-md-6">
                        <label for="inputDateStart" class="form-label">Date Start</label>
                        <input type="text" class="form-control" name="date_mulai" id="inputDateStart">
                        <input type="hidden" name="idPermohonan" id="idPermohonanSuratTugas">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="inputDateEnd" class="form-label">Date End</label>
                        <input type="text" class="form-control" name="date_end" id="inputDateEnd">
                    </div>
                    <div class="mb-3">
                        <p>Daftar petugas</p>
                        <div id="listPetugas">
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary col-12" id="addPetugas">Tambah petugas</button>
                        </div>
                    </div>
                    <div class="wrapper text-center" id="content-ttd">
                        <button type="button" class="btn btn-danger btn-sm position-absolute ms-1 mt-1" id="signature-clear-surattugas"><i class="bi bi-trash"></i></button>
                        <canvas id="signature-canvas-surattugas" class="signature-pad border border-success-subtle rounded border-1" width=200 height=150></canvas>
                        <p class="text-center mb-0">{{ $title }}</p>
                        <span>(<span id="nameSignature">{{ Auth::user()->name }}</span>)</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="actionSignature">
                <button class="btn btn-outline-primary" role="button" id="btnSendTugas">Kirim tugas</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    @vite(['resources/js/component/suratTugas.js'])
@endpush
