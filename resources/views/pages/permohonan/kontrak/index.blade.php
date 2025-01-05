@extends('layouts.main')

@section('content')
<div class="card shadow-sm m-4 mt-2">
    <div class="card-body">
        <div class="d-flex">
            <div class="flex-grow-1"><button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button></div>
        </div>
        <div class="d-flex pb-4">
            <div class="w-100 d-flex">
                {{-- <div class="mx-2">
                    <label for="filterStatusVerif" class="form-label">Status</label>
                    <select name="statusVerif" id="filterStatusVerif" class="form-select">
                        <option value="" selected>All</option>
                        <option value="1">Not verif</option>
                        <option value="2">Verifikasi</option>
                    </select>
                </div>
                <div class="mx-2">
                    <label for="filterLab" class="form-label">Lab</label>
                    <select name="filterLab" id="filterLab" class="form-select">
                        <option value="" selected>All</option>
                    </select>
                </div> --}}
            </div>
            
            <div class="flex-shrink-1">
                <div class="col-12">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                        <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="my-3">
            <div class="body-placeholder my-3" id="list-placeholder">
                
            </div>
            <div class="body my-3" id="list-container">
            </div>
            <div aria-label="Page navigation example" id="list-pagination">
                    
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="permohonanEvaluasiModal" tabindex="-1" aria-labelledby="permohonanEvaluasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permohonanEvaluasiModalLabel">Evaluasi - TLD Perorangan (BARC)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Buat permohonan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/permohonan/kontrak.js') }}"></script>
@endpush