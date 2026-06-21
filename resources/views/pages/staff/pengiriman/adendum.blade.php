@extends('layouts.main')

@section('content')
    <div class="card shadow-sm m-4 mt-2 border-0 rounded-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <div>
                    <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Antrean
                        Pengiriman Adendum</h4>
                    <p class="text-muted small mb-0">Kelola dan kirim dokumen Invoice/LHU khusus untuk permohonan adendum
                        kontrak yang aktif.</p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="reload()"><i
                            class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
                </div>
            </div>

            <div class="d-flex">
                <div class="flex-grow-1">
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
                <!-- Loading Skeleton Placeholder -->
                <div class="body-placeholder d-flex flex-column gap-3 my-3" id="list-placeholder-adendum">
                    <!-- Skeleton loader akan dimuat di sini -->
                </div>

                <!-- Container Data -->
                <div class="body d-flex flex-column gap-3 my-3" id="list-container-adendum">
                    <!-- Daftar adendum akan dirender di sini -->
                </div>

                <!-- Pagination -->
                <div aria-label="Page navigation" id="list-pagination-adendum" class="d-flex justify-content-end mt-4">
                    <!-- Pagination links -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/staff/pengiriman_adendum.js') }}"></script>
@endpush
