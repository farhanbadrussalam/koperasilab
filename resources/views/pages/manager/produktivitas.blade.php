@extends('layouts.main')

@section('content')
    <div class="produktivitas-wrapper px-2 px-md-0">

        {{-- ===== PAGE HEADER ===== --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="prod-icon-circle">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </span>
                    Produktivitas Petugas
                </h5>
                <p class="text-muted small mb-0 mt-1">Pantau jumlah pekerjaan yang diselesaikan oleh setiap petugas</p>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="reloadData()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
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
                                    id="countFilter">4</span>
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
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 prod-card prod-card-blue">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="prod-stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people-fill"></i>
                            </span>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"
                                style="font-size:.7rem">Petugas</span>
                        </div>
                        <div class="prod-stat-value" id="stat_total_petugas">
                            <div class="placeholder-glow"><span class="placeholder col-6 rounded"></span></div>
                        </div>
                        <div class="prod-stat-label">Total Petugas Aktif</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 prod-card prod-card-green">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="prod-stat-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check2-circle"></i>
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill"
                                style="font-size:.7rem">Selesai</span>
                        </div>
                        <div class="prod-stat-value" id="stat_total_selesai">
                            <div class="placeholder-glow"><span class="placeholder col-6 rounded"></span></div>
                        </div>
                        <div class="prod-stat-label" id="stat_label_selesai">Total Job Selesai</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 prod-card prod-card-orange">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="prod-stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-graph-up-arrow"></i>
                            </span>
                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill"
                                style="font-size:.7rem">Rata-rata</span>
                        </div>
                        <div class="prod-stat-value" id="stat_avg">
                            <div class="placeholder-glow"><span class="placeholder col-6 rounded"></span></div>
                        </div>
                        <div class="prod-stat-label">Rata-rata / Petugas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 prod-card prod-card-purple">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="prod-stat-icon bg-primary bg-opacity-10" style="color:#8b5cf6">
                                <i class="bi bi-trophy-fill"></i>
                            </span>
                            <span class="badge rounded-pill"
                                style="background:rgba(139,92,246,.12);color:#8b5cf6;font-size:.7rem">Top</span>
                        </div>
                        <div class="prod-stat-value text-truncate" id="stat_top_performer" style="font-size:1rem">
                            <div class="placeholder-glow"><span class="placeholder col-8 rounded"></span></div>
                        </div>
                        <div class="prod-stat-label" id="stat_top_total">Top Performer</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- ===== CHART ===== --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                            Produktivitas per Petugas
                        </h6>
                        <p class="text-muted small mb-3">Top 10 petugas berdasarkan total job selesai</p>
                        <div id="chart-wrapper" style="position:relative; height:260px; overflow:hidden;">
                            <div id="chart-placeholder" class="d-flex align-items-center justify-content-center"
                                style="height:100%; width:100%; position:absolute; top:0; left:0;">
                                <div class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                                    <div class="small">Memuat grafik...</div>
                                </div>
                            </div>
                            <div id="prodChart" style="display:none; width:100%; height:100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== JOB BREAKDOWN MINI ===== --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-pie-chart-fill text-success me-2"></i>
                            Breakdown per Jenis Pekerjaan
                        </h6>
                        <p class="text-muted small mb-3">Distribusi jumlah pekerjaan selesai berdasarkan jenis</p>
                        <div id="breakdown-wrapper" style="min-height:100px;">
                            <div id="breakdown-placeholder" class="d-flex align-items-center justify-content-center"
                                style="height:100px">
                                <div class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-success mb-2"></div>
                                    <div class="small">Memuat data...</div>
                                </div>
                            </div>
                            <div id="breakdown-list" class="row g-3 d-none"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TABEL DETAIL ===== --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-table text-info me-2"></i>
                    Detail Produktivitas Petugas
                </h6>
                <div class="table-responsive">
                    <table id="tbl-produktivitas" class="table table-hover table-bordered align-middle small w-100"
                        style="min-width:600px;">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-nowrap">Nama Petugas</th>
                                <th class="text-center text-nowrap text-success">✔ Selesai</th>
                                <th class="text-center text-nowrap text-warning">⟳ Proses</th>
                                <th class="text-center fw-bold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Diisi oleh DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Data untuk JS (master_jobs ID list) --}}
    <script>
        const masterJobIds = @json($masterJobs->pluck('id_jobs')->toArray());
        const masterJobNames = @json($masterJobs->pluck('name')->toArray());
        const dataUrl = "{{ route('manager.produktivitas.getData') }}";
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/manager/produktivitas.js') }}"></script>
@endpush

@push('styles')
    <style>
        .produktivitas-wrapper {
            max-width: 100%;
        }

        /* Icon circle header */
        .prod-icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Summary stat cards */
        .prod-card {
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            border-left: 4px solid transparent !important;
        }

        .prod-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .1) !important;
        }

        .prod-card-blue {
            border-left-color: #4e73df !important;
        }

        .prod-card-green {
            border-left-color: #1cc88a !important;
        }

        .prod-card-orange {
            border-left-color: #f6c23e !important;
        }

        .prod-card-purple {
            border-left-color: #8b5cf6 !important;
        }

        .prod-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            font-size: 1.05rem;
        }

        .prod-stat-value {
            font-size: 1.7rem;
            font-weight: 700;
            line-height: 1.1;
            color: #1a1a2e;
        }

        .prod-stat-label {
            font-size: .75rem;
            color: #6c757d;
            margin-top: 2px;
        }

        /* DataTable header */
        #tbl-produktivitas thead th {
            white-space: nowrap;
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .03em;
        }

        #tbl-produktivitas tbody td {
            font-size: .82rem;
            vertical-align: middle;
        }

        #tbl-produktivitas tbody td.text-center {
            font-variant-numeric: tabular-nums;
        }

        /* Job value coloring */
        .job-val-zero {
            color: #adb5bd;
        }

        .job-val-low {
            color: #1cc88a;
        }

        .job-val-mid {
            color: #f6c23e;
        }

        .job-val-high {
            color: #e74a3b;
        }

        /* Breakdown list item */
        .breakdown-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-bar {
            height: 6px;
            border-radius: 99px;
            background: linear-gradient(90deg, #4e73df, #1cc88a);
            transition: width .5s ease;
        }
    </style>
@endpush
