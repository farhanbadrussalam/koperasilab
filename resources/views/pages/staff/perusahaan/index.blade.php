@extends('layouts.main')

@section('content')
<div class="card shadow-sm m-4">
    <div class="card-body">
        <div class="d-flex">
            <div class="flex-grow-1">
                <div class="flex-grow-1">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                                <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                        <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span>
                    </button>
                    <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="list-filter"></div>
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

@include('pages.profile.component.modal_pic')
@endsection
@push('scripts')
    <script src="{{ asset('js/staff/perusahaan.js') }}"></script>
@endpush
