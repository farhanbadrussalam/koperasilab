@extends('layouts.main')

@section('content')
    <div class="card shadow-sm m-4">
        <div class="card-body">
            <div class="d-flex">
                <div class="flex-grow-1">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()"><i
                            class="bi bi-arrow-clockwise"></i> Refresh data</button>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse"
                            data-bs-target="#collapseFilter">
                            <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none"
                                id="countFilter">4</span>
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
                    @for ($i = 0; $i < 3; $i++)
                        <div class="card mb-2">
                            <div class="card-body row align-items-center">
                                <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                    <div class="placeholder w-50 mb-1"></div>
                                    <div class="placeholder w-50 mb-1"></div>
                                    <div class="placeholder w-50 mb-1"></div>
                                    <div class="placeholder w-75 mb-1"></div>
                                </div>
                                <div class="placeholder-glow col-6 col-md-3">
                                    <div class="placeholder w-50 mb-1"></div>
                                </div>
                                <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                    <div class="placeholder w-50 mb-1"></div>
                                </div>
                                <div class="placeholder-glow col-6 col-md-2">
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
                <div aria-label="Page navigation example" id="list-pagination"></div>
            </div>
        </div>
    </div>

    <!-- Modal Pelanggan -->
    <div class="modal fade" id="modalVerifikasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVerifikasiTitle">Verifikasi Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_request">
                    <input type="hidden" id="jenis_pelanggan">
                    <p id="modalVerifikasiDesc"></p>
                    <div class="mb-3" id="formKodePerusahaan">
                        <label class="form-label" id="labelKodePerusahaan">Kode Perusahaan <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kode_perusahaan"
                            placeholder="Masukkan kode perusahaan secara manual...">
                        <div class="invalid-feedback" id="errorKodePerusahaan">
                            Please choose a username.
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <div>
                        <button type="button" class="btn btn-danger" onclick="tolakPelanggan(this)">Tolak</button>
                        <button type="button" class="btn btn-primary" onclick="verifikasiPelanggan(this)"
                            id="btnVerifikasi">Verifikasi Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset_versioned('js/staff/approval_pelanggan.js') }}"></script>
@endpush
