@extends('layouts.main')

@section('content')
    <div class="m-4">
        {{-- Summary Counter --}}
        <div class="row g-3 mb-4" id="summary-counter" style="display:none!important;">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6!important;">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px;background:rgba(59,130,246,.12);">
                            <i class="bi bi-archive-fill text-primary fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-3 lh-1 text-primary" id="count-di-lab">-</div>
                            <div class="text-muted small mt-1">TLD di Lab</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b!important;">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px;background:rgba(245,158,11,.12);">
                            <i class="bi bi-graph-up-arrow text-warning fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-3 lh-1 text-warning" id="count-evaluasi">-</div>
                            <div class="text-muted small mt-1">Evaluasi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #8b5cf6!important;">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px;background:rgba(139,92,246,.12);">
                            <i class="bi bi-box-seam-fill text-purple fs-5" style="color:#8b5cf6;"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-3 lh-1" style="color:#8b5cf6;" id="count-sewa">-</div>
                            <div class="text-muted small mt-1">Sewa</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981!important;">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px;background:rgba(16,185,129,.12);">
                            <i class="bi bi-check2-circle text-success fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-3 lh-1 text-success" id="count-idle">-</div>
                            <div class="text-muted small mt-1">Idle / Siap Pakai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Refresh & Filter --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="flex-grow-1">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="reload()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh data
                        </button>
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                                <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none" id="countFilter">0</span>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                    <span class="text-muted small" id="total-label"></span>
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

        {{-- ============ CARD 1: TLD DI LAB ============ --}}
        <div id="section-di-lab" class="d-none mb-4">
            <div class="card shadow-sm">
                <div
                    class="card-header bg-transparent border-0 pt-4 pb-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width:36px;height:36px;background:rgba(59,130,246,.12);">
                            <i class="bi bi-archive-fill text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Daftar TLD di LAB</h5>
                            <p class="text-muted small mb-0">Daftar TLD aktif yang terpantau (Terikat Kontrak,
                                Evaluasi, & Sewa)</p>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold" id="badge-di-lab">0</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="container-di-lab" class="mt-3"></div>
                    <div id="pagination-di-lab" class="mt-3"></div>
                </div>
            </div>
        </div>

        {{-- ============ CARD 2: TLD IDLE ============ --}}
        <div id="section-idle" class="d-none mb-4">
            <div class="card border-0 shadow-sm">
                <div
                    class="card-header bg-transparent border-0 pt-4 pb-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width:36px;height:36px;background:rgba(16,185,129,.12);">
                            <i class="bi bi-check2-circle text-success fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Daftar TLD Idle</h5>
                            <p class="text-muted small mb-0">Daftar TLD siap pakai yang sedang berada di
                                penyimpanan</p>
                        </div>
                    </div>
                    <span class="badge bg-success rounded-pill px-3 py-2 fw-semibold" id="badge-idle">0</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="container-idle" class="mt-3"></div>
                    <div id="pagination-idle" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('styles')
    <style>
        .border-left-lab {
            border-left: 4.5px solid #3b82f6 !important;
        }

        .border-left-evaluasi {
            border-left: 4.5px solid #f59e0b !important;
        }

        .border-left-sewa {
            border-left: 4.5px solid #8b5cf6 !important;
        }

        .border-left-idle {
            border-left: 4.5px solid #10b981 !important;
        }

        .tld-badge {
            font-size: 0.78rem;
            letter-spacing: 0.02em;
        }

        .history-badge {
            font-size: 0.75rem;
            background: rgba(100, 116, 139, .07);
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 0.3rem 0.6rem;
        }

        .fs-9 {
            font-size: 0.75rem !important;
        }

        .fs-8 {
            font-size: 0.85rem !important;
        }

        .btn-xs {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            border-radius: 20px;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/staff/penyimpanan.js') }}"></script>
@endpush
