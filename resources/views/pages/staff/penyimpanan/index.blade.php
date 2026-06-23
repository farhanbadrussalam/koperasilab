@extends('layouts.main')

@section('content')
    <div class="m-4">
        {{-- Summary Counter --}}
        {{-- <div class="row g-3 mb-4" id="summary-counter" style="display:none!important;">
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6!important;">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px;background:rgba(59,130,246,.12);">
                            <i class="bi bi-journal-bookmark-fill text-primary fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-3 lh-1 text-primary" id="count-kontrak">-</div>
                            <div class="text-muted small mt-1">Total Kontrak Aktif</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b!important;">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px;background:rgba(245,158,11,.12);">
                            <i class="bi bi-hourglass-split text-warning fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-3 lh-1 text-warning" id="count-belum-kembali">-</div>
                            <div class="text-muted small mt-1">Kontrak Belum Kembali</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981!important;">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px;background:rgba(16,185,129,.12);">
                            <i class="bi bi-check2-all text-success fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-3 lh-1 text-success" id="count-sudah-kembali">-</div>
                            <div class="text-muted small mt-1">Kontrak Sudah Kembali</div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- Tombol Refresh & Filter --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="flex-grow-1">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh data
                        </button>
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse"
                                data-bs-target="#collapseFilter">
                                <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none"
                                    id="countFilter">0</span>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                    {{-- <span class="text-muted small" id="total-label"></span> --}}
                </div>

                <div id="list-filter"></div>

                {{-- Placeholder Loading --}}
                <div class="body-placeholder my-3" id="placeholder-container">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="card mb-2 shadow-xs border-light-subtle">
                            <div class="card-body row align-items-center">
                                <div class="placeholder-glow col-12 col-md-5 d-flex flex-column gap-1">
                                    <div class="placeholder w-50" style="height: 18px;"></div>
                                    <div class="placeholder w-30" style="height: 12px;"></div>
                                    <div class="placeholder w-40" style="height: 14px;"></div>
                                </div>
                                <div class="placeholder-glow col-12 col-md-5 mt-2 mt-md-0">
                                    <div class="placeholder w-30" style="height: 14px;"></div>
                                </div>
                                <div
                                    class="placeholder-glow col-12 col-md-2 text-end mt-2 mt-md-0 d-flex justify-content-end gap-1">
                                    <div class="placeholder w-50 rounded-pill" style="height: 28px;"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Empty State Global --}}
                <div class="text-center py-5 d-none" id="empty-state">
                    <div class="mb-3">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="fw-bold text-secondary">Tidak Ada Data TLD</h5>
                    <p class="text-muted small mb-0">Saat ini tidak ada data TLD yang terpantau.</p>
                </div>
            </div>
        </div>

        {{-- ============ SECTION UTAMA: DAFTAR PER KONTRAK ============ --}}
        <div id="section-kontrak" class="d-none mb-4 mt-4">
            <div class="card shadow-sm">
                <div
                    class="card-header bg-transparent border-0 pt-4 pb-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width:36px;height:36px;background:rgba(59,130,246,.12);">
                            <i class="bi bi-archive-fill text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Daftar TLD per Kontrak</h5>
                            <p class="text-muted small mb-0">Dikelompokkan berdasarkan nomor kontrak</p>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold" id="badge-kontrak">0</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="container-kontrak" class="mt-3"></div>
                    <div id="pagination-kontrak" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ MODAL DETAIL TLD ============ --}}
    <div class="modal fade" id="modal-detail-tld" tabindex="-1" aria-labelledby="modal-detail-tld-label"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modal-detail-tld-label">Detail TLD</h5>
                        <p class="text-muted small mb-0" id="modal-detail-tld-subtitle"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-detail-tld-body">
                    {{-- Diisi oleh JS --}}
                </div>
            </div>
        </div>
    </div>
@endsection


@push('styles')
    <style>
        .kontrak-card {
            border-left: 4.5px solid #3b82f6;
            transition: box-shadow 0.2s ease;
        }

        .kontrak-card:hover {
            box-shadow: 0 4px 16px rgba(59, 130, 246, .13) !important;
        }

        .kontrak-card.tipe-evaluasi {
            border-left-color: #f59e0b;
        }

        .kontrak-card.tipe-sewa {
            border-left-color: #8b5cf6;
        }

        .kontrak-card.tipe-di_lab {
            border-left-color: #3b82f6;
        }

        .status-badge-kembali {
            background: rgba(16, 185, 129, .10);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, .25);
        }

        .status-badge-belum {
            background: rgba(245, 158, 11, .10);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, .25);
        }

        .tld-row-kembali td {
            opacity: 0.65;
        }

        .fs-8 {
            font-size: 0.85rem !important;
        }

        .fs-9 {
            font-size: 0.75rem !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset_versioned('js/staff/penyimpanan.js') }}"></script>
@endpush
