@extends('layouts.main')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Page -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-archive-fill text-primary me-2"></i> Pemantauan Penyimpanan TLD
            </h4>
            <p class="text-muted small mb-0">Daftar TLD yang saat ini tersimpan di laboratorium, dikelompokkan berdasarkan kontrak dan periode pemakaian.</p>
        </div>
        <div>
            <button class="btn btn-primary btn-sm rounded-pill shadow-sm" onclick="reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
            </button>
        </div>
    </div>

    <!-- Placeholder Loading -->
    <div class="row" id="placeholder-container">
        @for ($i = 0; $i < 3; $i++)
        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100 placeholder-glow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="w-75">
                            <span class="placeholder col-8 bg-secondary rounded mb-2" style="height: 20px;"></span>
                            <span class="placeholder col-10 bg-secondary rounded" style="height: 15px;"></span>
                        </div>
                        <span class="placeholder col-3 bg-secondary rounded" style="height: 30px;"></span>
                    </div>
                    <hr class="text-secondary opacity-25">
                    <div class="mb-2">
                        <span class="placeholder col-4 bg-secondary rounded" style="height: 15px;"></span>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <span class="placeholder col-12 bg-secondary rounded" style="height: 12px;"></span>
                        <span class="placeholder col-12 bg-secondary rounded" style="height: 12px;"></span>
                        <span class="placeholder col-9 bg-secondary rounded" style="height: 12px;"></span>
                    </div>
                </div>
            </div>
        </div>
        @endfor
    </div>

    <!-- Empty State -->
    <div class="text-center py-5 d-none" id="empty-state">
        <div class="mb-3">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
        </div>
        <h5 class="fw-bold text-secondary">Tidak Ada TLD di Penyimpanan</h5>
        <p class="text-muted small">Saat ini tidak ada data TLD dengan status penyimpanan aktif.</p>
    </div>

    <!-- Container Grid Data -->
    <div class="row d-none" id="data-container">
        <!-- Rendered via JavaScript -->
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-premium {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border-top: 4px solid var(--bs-primary) !important;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }
    .card-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.08) !important;
    }
    .transition-hover {
        transition: all 0.2s ease-in-out;
    }
    .transition-hover:hover {
        background-color: var(--bs-light-border-subtle) !important;
        transform: scale(1.02);
    }
    .hover-slide {
        transition: all 0.3s ease;
    }
    .hover-slide:hover {
        transform: translateX(2px) translateY(-1px);
        box-shadow: 0 4px 8px rgba(var(--bs-primary-rgb), 0.2);
    }
    .tld-scroll-container::-webkit-scrollbar {
        width: 5px;
    }
    .tld-scroll-container::-webkit-scrollbar-track {
        background: transparent;
    }
    .tld-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .tld-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .fs-9 {
        font-size: 0.75rem !important;
    }
    .fs-8 {
        font-size: 0.85rem !important;
    }
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/staff/penyimpanan.js') }}"></script>
@endpush
