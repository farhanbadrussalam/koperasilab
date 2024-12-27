@extends('layouts.main')

@section('content')
<div class="card shadow-sm m-4">
    <div class="card-body">
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
                @for ($i = 0; $i < 5; $i++)
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="placeholder-glow col-12 col-md-6 d-flex flex-column">
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-50 mb-1"></div>
                        </div>
                        <div class="placeholder-glow col-6 col-md-2 ms-auto text-center">
                            <div class="placeholder w-50 mb-1"></div>
                        </div>
                        <div class="placeholder-glow col-6 col-md-2 text-center">
                            <div class="placeholder w-50 mb-1"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
            <div class="body my-3" id="list-container">
                
            </div>
            <div aria-label="Page navigation example" id="list-pagination">
                    
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalEditPerusahaan" tabindex="-1" aria-labelledby="modalEditPerusahaanLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalEditPerusahaanLabel">Edit Perusahaan</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="idEditPerusahaan">
                <input type="hidden" name="typeModal" id="typeModal">
                <input type="hidden" name="kodePerusahaan" id="kodePerusahaan">
                <div class="mb-3">
                    <label for="kodeEditPerusahaan" class="form-label">Kode Perusahaan</label>
                    <input type="text" class="form-control" id="kodeEditPerusahaan" name="kode">
                    <div class="invalid-feedback" id="errorKodePerusahaan">
                        Please choose a username.
                    </div>
                </div>
                <div class="text-end">
                    <button class="btn btn-primary" onclick="simpanEditPerusahaan()" id="simpanEditPerusahaan">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/staff/perusahaan.js') }}"></script>
@endpush
