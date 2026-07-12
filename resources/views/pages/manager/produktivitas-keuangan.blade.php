@extends('layouts.main')

@section('content')
    <div class="prodkeu-wrapper px-2 px-md-0">

        {{-- ===== PAGE HEADER ===== --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="prodkeu-icon-circle">
                        <i class="bi bi-receipt-cutoff"></i>
                    </span>
                    Produktivitas Keuangan
                </h5>
                <p class="text-muted small mb-0 mt-1">Pantau jumlah invoice dan pengiriman secara real-time</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="reloadData()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
            </div>
        </div>

        {{-- ===== FILTER BAR ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-secondary btn-sm rounded-start-pill" data-bs-toggle="collapse"
                                data-bs-target="#collapseFilter">
                                <i class="bi bi-funnel"></i> Filter <span class="badge text-bg-secondary d-none"
                                    id="countFilter">3</span>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-end-pill" onclick="clearFilter()">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="list-filter"></div>
            </div>
        </div>

        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="row g-3 mb-4" id="summary-cards">
            {{-- Card: Total Invoice --}}
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 prodkeu-card prodkeu-card-blue">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="prodkeu-stat-icon bg-primary bg-opacity-10 text-primary p-1 rounded">
                                <i class="bi bi-receipt"></i>
                            </span>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"
                                style="font-size:.7rem">Invoice</span>
                        </div>
                        <div class="prodkeu-stat-value" id="stat_total_invoice">
                            <div class="placeholder-glow"><span class="placeholder col-6 rounded"></span></div>
                        </div>
                        <div class="prodkeu-stat-label">Total Invoice Dibuat</div>
                    </div>
                </div>
            </div>
            {{-- Card: Invoice Lunas --}}
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 prodkeu-card prodkeu-card-green">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="prodkeu-stat-icon bg-success bg-opacity-10 text-success p-1 rounded">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill"
                                style="font-size:.7rem">Lunas</span>
                        </div>
                        <div class="prodkeu-stat-value" id="stat_invoice_lunas">
                            <div class="placeholder-glow"><span class="placeholder col-6 rounded"></span></div>
                        </div>
                        <div class="prodkeu-stat-label" id="stat_label_lunas">Invoice Lunas</div>
                    </div>
                </div>
            </div>
            {{-- Card: Total Pengiriman --}}
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 prodkeu-card prodkeu-card-orange">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="prodkeu-stat-icon bg-warning bg-opacity-10 text-warning p-1 rounded">
                                <i class="bi bi-truck"></i>
                            </span>
                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill"
                                style="font-size:.7rem">Pengiriman</span>
                        </div>
                        <div class="prodkeu-stat-value" id="stat_total_pengiriman">
                            <div class="placeholder-glow"><span class="placeholder col-6 rounded"></span></div>
                        </div>
                        <div class="prodkeu-stat-label">Total Pengiriman</div>
                    </div>
                </div>
            </div>
            {{-- Card: Nilai Invoice Lunas --}}
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 prodkeu-card prodkeu-card-purple">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="prodkeu-stat-icon bg-primary bg-opacity-10 p-1 rounded" style="color:#8b5cf6">
                                <i class="bi bi-cash-coin"></i>
                            </span>
                            <span class="badge rounded-pill"
                                style="background:rgba(139,92,246,.12);color:#8b5cf6;font-size:.7rem">Nilai</span>
                        </div>
                        <div class="prodkeu-stat-value text-truncate" id="stat_nilai_lunas" style="font-size:.95rem">
                            <div class="placeholder-glow"><span class="placeholder col-8 rounded"></span></div>
                        </div>
                        <div class="prodkeu-stat-label">Nilai Invoice Lunas</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== CHARTS ROW ===== --}}
        <div class="row g-3 mb-4">
            {{-- Line Chart: Tren 12 Bulan --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                            Tren Invoice & Pengiriman
                        </h6>
                        <p class="text-muted small mb-3">12 bulan terakhir</p>
                        <div id="tren-chart-wrapper" style="position:relative; height:240px; overflow:hidden;">
                            <div id="tren-chart-placeholder" class="d-flex align-items-center justify-content-center"
                                style="height:100%; width:100%; position:absolute; top:0; left:0;">
                                <div class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                                    <div class="small">Memuat grafik...</div>
                                </div>
                            </div>
                            <div id="trenChart" style="display:none; width:100%; height:100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Donut Chart: Status Invoice --}}
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-pie-chart-fill text-success me-2"></i>
                            Status Invoice
                        </h6>
                        <p class="text-muted small mb-3">Distribusi berdasarkan status</p>
                        <div id="donut-chart-wrapper" style="position:relative; height:200px; overflow:hidden;">
                            <div id="donut-chart-placeholder" class="d-flex align-items-center justify-content-center"
                                style="height:100%; width:100%; position:absolute; top:0; left:0;">
                                <div class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-success mb-2"></div>
                                    <div class="small">Memuat grafik...</div>
                                </div>
                            </div>
                            <div id="donutChart" style="display:none; width:100%; height:100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== BREAKDOWN ROW ===== --}}
        <div class="row g-3 mb-4">
            {{-- Invoice Breakdown per Status --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-list-check text-info me-2"></i>
                            Breakdown Status Invoice
                        </h6>
                        <p class="text-muted small mb-3">Rincian jumlah invoice per status</p>
                        <div id="invoice-breakdown-wrapper" style="min-height:120px;">
                            <div id="invoice-breakdown-placeholder"
                                class="d-flex align-items-center justify-content-center" style="height:120px;">
                                <div class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-info mb-2"></div>
                                    <div class="small">Memuat data...</div>
                                </div>
                            </div>
                            <div id="invoice-breakdown-list" class="d-none"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pengiriman per Ekspedisi --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-truck text-warning me-2"></i>
                            Pengiriman per Ekspedisi
                        </h6>
                        <p class="text-muted small mb-3">Distribusi pengiriman berdasarkan ekspedisi</p>
                        <div id="ekspedisi-breakdown-wrapper" style="min-height:120px;">
                            <div id="ekspedisi-breakdown-placeholder"
                                class="d-flex align-items-center justify-content-center" style="height:120px;">
                                <div class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-warning mb-2"></div>
                                    <div class="small">Memuat data...</div>
                                </div>
                            </div>
                            <div id="ekspedisi-breakdown-list" class="d-none"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TABEL DETAIL INVOICE ===== --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-table text-secondary me-2"></i>
                    Detail Invoice
                </h6>
                <div class="table-responsive">
                    <table id="tbl-invoice-keuangan" class="table table-hover table-bordered align-middle small w-100"
                        style="min-width:700px;">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-nowrap">No. Invoice</th>
                                <th class="text-nowrap">Pelanggan</th>
                                <th class="text-nowrap">Layanan</th>
                                <th class="text-nowrap text-end">Total Harga</th>
                                <th class="text-center text-nowrap">Status</th>
                                <th class="text-nowrap">Metode</th>
                                <th class="text-nowrap">Tgl. Dibuat</th>
                                <th class="text-nowrap">Tgl. Lunas</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Data untuk JS --}}
    <script>
        const dataUrl = "{{ route('manager.produktivitas.keuangan.getData') }}";
    </script>
@endsection

@push('scripts')
    <script src="{{ asset_versioned('js/filter.js') }}"></script>
    <script src="{{ asset_versioned('js/manager/produktivitas-keuangan.js') }}"></script>
@endpush

@push('styles')
    <style>
        /* ── Icon circles ── */
        .prodkeu-icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #4e73df22, #1cc88a22);
            color: #4e73df;
            font-size: 1.1rem;
        }

        /* ── Stat cards ── */
        .prodkeu-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            font-size: 1rem;
        }

        .prodkeu-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .prodkeu-stat-label {
            font-size: 0.72rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── Card accent borders ── */
        .prodkeu-card {
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .prodkeu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, .10) !important;
        }

        .prodkeu-card-blue {
            border-left: 4px solid #4e73df !important;
        }

        .prodkeu-card-green {
            border-left: 4px solid #1cc88a !important;
        }

        .prodkeu-card-orange {
            border-left: 4px solid #f6c23e !important;
        }

        .prodkeu-card-purple {
            border-left: 4px solid #8b5cf6 !important;
        }

        /* ── Breakdown bars ── */
        .keu-breakdown-bar {
            height: 6px;
            border-radius: 99px;
            transition: width .5s ease;
        }

        .keu-ekspedisi-bar {
            height: 5px;
            border-radius: 99px;
            background: linear-gradient(90deg, #f6c23e, #e74a3b);
            transition: width .5s ease;
        }
    </style>
@endpush
