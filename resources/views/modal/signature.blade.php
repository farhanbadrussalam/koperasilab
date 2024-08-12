<div class="modal fade" id="modal-signature">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Signature</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between m-2 mt-4">
                    <div class="row w-100">
                        <input type="hidden" id="tmp_id_hash">
                        <div class="wrapper text-center" id="content-ttd">
                            <button class="btn btn-danger btn-sm position-absolute ms-1 mt-1" id="signature-clear"><i class="bi bi-trash"></i></button>
                            <canvas id="signature-canvas" class="signature-pad border border-success-subtle rounded border-2" width=300 height=200></canvas>
                            <p class="text-center mb-0">{{ $title }}</p>
                            <span>(<span id="nameSignature"></span>)</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="actionSignature">
                <button class="btn btn-outline-primary" role="button" id="createSignature">Save</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    @vite(['resources/js/component/signature.js'])

@endpush
