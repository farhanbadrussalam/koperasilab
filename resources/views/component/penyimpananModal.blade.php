<div class="modal fade" id="penyimpananModal" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="penyimpananModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="penyimpananModalLabel">Penyimpanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="col-md-12">
                    <label for="" class="col-form-label">List TLD</label>
                    <div class="text-center" id="content-penyimpanan-tld"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset_versioned('js/component/penyimpananModal.js') }}"></script>
@endpush
