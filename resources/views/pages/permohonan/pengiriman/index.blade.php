@extends('layouts.main')

@section('content')
    <div class="card shadow-sm m-4 mt-2">
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
            
            <ul class="nav nav-tabs mt-3" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="progress-tab" onclick="switchLoadTab('progress')" data-bs-toggle="tab"
                        data-bs-target="#progress-tab-pane" type="button" role="tab"
                        aria-controls="progress-tab-pane" aria-selected="true">Progres <span class="badge bg-secondary ms-1" id="count-progress"></span></button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="selesai-tab" onclick="switchLoadTab('selesai')" data-bs-toggle="tab"
                        data-bs-target="#selesai-tab-pane" type="button" role="tab"
                        aria-controls="selesai-tab-pane" aria-selected="false">Selesai <span class="badge bg-secondary ms-1" id="count-selesai"></span></button>
                </li>
            </ul>
            <div class="my-3">
                <div class="body-placeholder my-3" id="list-placeholder-pengiriman"></div>
                <div class="body my-3" id="list-container-pengiriman"></div>
                <div aria-label="Page navigation example" id="list-pagination-pengiriman"></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal-diterima" tabindex="-1" aria-labelledby="modal-diterimaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light border-bottom py-3">
                    <h1 class="modal-title fs-5 fw-bold text-dark d-flex align-items-center gap-2" id="modal-diterimaLabel">
                        <i class="bi bi-shield-check text-primary fs-4"></i> Konfirmasi Penerimaan Dokumen
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <input type="hidden" name="idPengiriman" id="idPengiriman">
                        <input type="hidden" name="isLhuSend" id="isLhuSend">

                        <!-- Section 1: Tanggal Penerimaan -->
                        <div class="col-12">
                            <label for="txt_date_diterima"
                                class="form-label small fw-bold text-muted text-uppercase mb-2">Tanggal Diterima</label>
                            <div class="input-group shadow-sm rounded-3">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i
                                        class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" name="txt_date_diterima"
                                    id="txt_date_diterima">
                            </div>
                        </div>

                        <!-- Section 2: Surat Pengantar -->
                        <div class="col-12" id="surpengDivContainer">
                            <div id="surpengDiv"></div>
                        </div>

                        <!-- Section 3: Bukti Pengiriman (Collapsible) -->
                        <div class="col-12">
                            <div
                                class="card border border-info-subtle bg-info-subtle bg-opacity-10 rounded-3 mb-2 shadow-xs">
                                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2 text-info-emphasis small">
                                        <i class="bi bi-info-circle-fill fs-5"></i>
                                        <span>Bukti pengiriman dari kurir telah diunggah.</span>
                                    </div>
                                    <button class="btn btn-link btn-sm text-decoration-none fw-bold text-info p-0"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#collapseBuktiPengiriman"
                                        aria-expanded="false" aria-controls="collapseBuktiPengiriman">
                                        Lihat Bukti <i class="bi bi-chevron-down ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="collapse" id="collapseBuktiPengiriman">
                                <div class="card border rounded-3 mb-3 shadow-sm bg-light">
                                    <div class="card-body p-3">
                                        <div id="showBuktiPengiriman"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Kelengkapan Dokumen Checkbox -->
                        <div class="col-12 mt-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Kelengkapan Dokumen <span
                                    class="text-danger">*</span></label>
                            <ul class="list-group w-100 border-0 bg-transparent" id="list-kelengkapan">
                                <!-- Populated dynamically -->
                            </ul>
                        </div>

                        <!-- Section 5: Upload Bukti Penerima -->
                        <div class="col-12 mt-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Upload Bukti Penerima
                                <span class="text-danger">*</span></label>
                            <div class="card border border-dashed rounded-3 p-3 bg-light text-center shadow-xs">
                                <div id="uploadBuktiPenerima"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-2 d-flex justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-3 px-4 flex-grow-1 fw-bold" id="btnSendDocument">
                        <i class="bi bi-check-circle-fill me-2"></i> Konfirmasi Penerimaan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const idPelanggan = `{{ Auth::user()->user_hash }}`;
    </script>
    <script src="{{ asset_versioned('js/permohonan/pengiriman.js') }}"></script>
@endpush
