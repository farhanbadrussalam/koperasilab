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
                <div class="ms-auto d-none align-items-center gap-3" id="container-collective-update">
                    <div class="form-check">
                        <input class="form-check-input cursor-pointer shadow-sm border-secondary-subtle" type="checkbox"
                            id="checkAllLhu">
                        <label class="form-check-label text-muted small cursor-pointer" for="checkAllLhu">
                            Pilih Semua (<span id="countSelected">0</span>)
                        </label>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill shadow-sm fw-semibold"
                        onclick="openProgressModalKolektif()"><i class="bi bi-ui-checks-grid me-1"></i> Update
                        Kolektif</button>
                </div>
            </div>
            <div id="list-filter"></div>
            <div class="my-3">
                <ul class="nav nav-tabs" id="myTab">
                    <li class="nav-item"><button class="nav-link active" role="tab" data-bs-toggle="tab"
                            onclick="switchLoadTab('progress')">Progress <span class="badge bg-secondary ms-1" id="count-progress"></span></button></li>
                    <li class="nav-item"><button class="nav-link" role="tab" data-bs-toggle="tab"
                            onclick="switchLoadTab('selesai')">Selesai <span class="badge bg-secondary ms-1" id="count-selesai"></span></button></li>
                </ul>
                <div class="body-placeholder my-3" id="list-placeholder-lhu">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="card mb-2">
                            <div class="card-body row align-items-center">
                                <div class="placeholder-glow col-12 col-md-4 d-flex flex-column">
                                    <div class="placeholder w-50 mb-1"></div>
                                    <div class="placeholder w-50 mb-1"></div>
                                    <div class="placeholder w-50 mb-1"></div>
                                    <div class="placeholder w-75 mb-1"></div>
                                </div>
                                <div class="placeholder-glow col-6 col-md-6">
                                    <div class="placeholder w-50 mb-1"></div>
                                </div>
                                <div class="placeholder-glow col-6 col-md-2 text-center">
                                    <div class="placeholder w-50 mb-1"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="body my-3" id="list-container-lhu">

                </div>
                <div aria-label="Page navigation example" id="list-pagination-lhu">

                </div>
            </div>
        </div>
    </div>
    <x-modal.progress-penyelia />
    <x-modal.progress-kolektif />
    @include('component.penyimpananModal')
@endsection

@push('scripts')
    <script>
        const listJobs = @json($listJobs);
    </script>
    <script src="{{ asset_versioned('js/staff/lhu.js') }}"></script>
@endpush
