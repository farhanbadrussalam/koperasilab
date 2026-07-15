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
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-sm btn-outline-success rounded-pill" onclick="exportData()">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </button>
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

        {{-- ===== TABEL DETAIL (Dihide, untuk background process) ===== --}}
        <div class="card border-0 shadow-sm rounded-4 d-none">
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

        {{-- ===== MODAL DETAIL JOB ===== --}}
        <div class="modal fade" id="modalDetailJob" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-sm rounded-4">
                    <div class="modal-header pb-0 border-bottom-0">
                        <h6 class="modal-title fw-bold text-dark mb-0" id="modalDetailJobTitle">
                            <i class="bi bi-table text-info me-2"></i>
                            Detail Produktivitas: <span id="modalJobName"></span>
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4">
                        <div class="table-responsive">
                            <table id="tbl-produktivitas-job"
                                class="table table-hover table-bordered align-middle small w-100"
                                style="min-width:600px;">
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Data untuk JS (master_jobs ID list) --}}
    <script>
        const masterJobIds = @json($masterJobs->pluck('id_jobs')->toArray());
        const masterJobNames = @json($masterJobs->pluck('name')->toArray());
        const dataUrl = "{{ route('manager.produktivitas.getData') }}";

        function exportData() {
            let url = "{{ route('manager.produktivitas.petugas.export') }}?";
            let filterValue = filterComp && filterComp.getAllValue();

            let params = new URLSearchParams();

            if (filterValue && filterValue.date_range && filterValue.date_range.length === 2) {
                params.append("start_date", filterValue.date_range[0]);
                params.append("end_date", filterValue.date_range[1]);
            }
            if (filterValue && filterValue.satuan_kerja) {
                params.append("satuan_kerja", filterValue.satuan_kerja);
            }
            if (filterValue && filterValue.search) {
                params.append("pencarian", filterValue.search);
            }

            window.location.href = url + params.toString();
        }
    </script>
@endsection

@push('scripts')
    <script src="{{ asset_versioned('js/manager/produktivitas.js') }}"></script>
@endpush

@push('styles')
    <style>
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

        .breakdown-clickable:hover {
            background-color: #f8f9fa !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-color: #dee2e6 !important;
        }

        /* Fix: loading indicator harus di depan tabel */
        .dataTables_processing {
            z-index: 100 !important;
            background: rgba(255, 255, 255, 0.9) !important;
        }
    </style>
@endpush
