@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="card p-0 m-0 shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="flex-grow-1">
                            <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()"><i
                                    class="bi bi-arrow-clockwise"></i> Refresh data</button>
                        </div>
                        <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="tambahPengguna()"><i
                                class="bi bi-plus"></i> Create Pengguna</button>
                    </div>
                    <div class="mb-3" id="list-filter"></div>
                    <div class="row mt-2" style="min-height: 50vh">
                        <table class="table table-hover w-100 align-middle table-borderless border-bottom"
                            id="pengguna-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%" class="text-center py-3">No</th>
                                    <th class="py-3">Informasi Pengguna</th>
                                    <th class="py-3">Divisi</th>
                                    <th class="py-3">Radiasi</th>
                                    <th class="py-3 text-center" width="18%">Status Keterikatan</th>
                                    <th class="py-3 text-center" width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @include('pages.permohonan.pengajuan.tld_pengguna')

    <!-- Modal Detail History Keterikatan Kontrak -->
    <div class="modal fade" id="modal-detail-keterikatan-pengguna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-text me-2 text-primary"></i>Detail Kontrak Aktif
                        Terikat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">Berikut adalah daftar kontrak aktif yang sedang menggunakan Kode
                        Lencana & Divisi ini:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Kontrak</th>
                                    <th>Jenis Layanan / TLD</th>
                                    <th>Divisi & Kode</th>
                                </tr>
                            </thead>
                            <tbody id="body-detail-keterikatan-pengguna">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4"
                        data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset_versioned('js/management/pengguna.js') }}"></script>
@endpush
