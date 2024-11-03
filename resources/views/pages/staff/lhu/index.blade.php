@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content col-md-12">
        <div class="container">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="start-tab" onclick="switchLoadTab(1)" data-bs-toggle="tab" data-bs-target="#start-tab-pane" type="button" role="tab" aria-controls="start-tab-pane" aria-selected="true">Mulai</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="anealing-tab" onclick="switchLoadTab(2)" data-bs-toggle="tab" data-bs-target="#anealing-tab-pane" type="button" role="tab" aria-controls="anealing-tab-pane" aria-selected="true">Proses Anealing</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="pembacaan-tab" onclick="switchLoadTab(3)" data-bs-toggle="tab" data-bs-target="#pembacaan-tab-pane" type="button" role="tab" aria-controls="pembacaan-tab-pane" aria-selected="true">Proses Pembacaan</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="selesai-tab" onclick="switchLoadTab(4)" data-bs-toggle="tab" data-bs-target="#selesai-tab-pane" type="button" role="tab" aria-controls="selesai-tab-pane" aria-selected="true">Selesai</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="start-tab-pane" role="tabpanel" aria-labelledby="start-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Petugas</div>
                                    <div class="col-md-2">Tipe</div>
                                    <div class="col-md-3 text-center">Tanggal</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-start">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-start">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-start">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="anealing-tab-pane" role="tabpanel" aria-labelledby="anealing-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Petugas</div>
                                    <div class="col-md-2">Tipe</div>
                                    <div class="col-md-3 text-center">Tanggal</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-anealing">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-anealing">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-anealing">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pembacaan-tab-pane" role="tabpanel" aria-labelledby="pembacaan-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Petugas</div>
                                    <div class="col-md-2">Tipe</div>
                                    <div class="col-md-3 text-center">Tanggal</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-pembacaan">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-pembacaan">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-pembacaan">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="selesai-tab-pane" role="tabpanel" aria-labelledby="selesai-tab" tabindex="0">
                    <div class="card shadow-sm m-4">
                        <div class="card-body">
                            <div class="d-flex pb-4">
                                <div class="w-100 d-flex">
                                    {{-- <div class="mx-2">
                                        <label for="filterStatusVerif" class="form-label">Status</label>
                                        <select name="statusVerif" id="filterStatusVerif" class="form-select">
                                            <option value="" selected>All</option>
                                            <option value="1">Not verif</option>
                                            <option value="2">Verifikasi</option>
                                        </select>
                                    </div>
                                    <div class="mx-2">
                                        <label for="filterLab" class="form-label">Lab</label>
                                        <select name="filterLab" id="filterLab" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div> --}}
                                </div>
                                
                                <div class="flex-shrink-1">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" aria-label="Name petugas" id="inputSearch" aria-describedby="btnSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-3">
                                <div class="header px-3 fw-bolder d-none d-md-flex row">
                                    <div class="col-md-3">Layanan</div>
                                    <div class="col-md-2">Petugas</div>
                                    <div class="col-md-2">Tipe</div>
                                    <div class="col-md-3 text-center">Tanggal</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-selesai">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-selesai">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-selesai">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="updateProgressModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="updateProgressModalLabel">Update progress</h1>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" onclick=""></button>
            </div>
            <div class="modal-body px-1">
                <input type="hidden" name="txtIdPenyelia" id="txtIdPenyelia">
                <div class="row mx-2">
                    <div class="col-sm-12 mb-3">
                        <label for="inputProgress">Progress</label>
                        <select name="inputProgress" id="inputProgress" class="form-select">
                        </select>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <label for="inputNote">Note</label>
                        <textarea name="inputNote" id="inputNote" cols="30" rows="5" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex" id="modalFooter">
                <!-- Footer buttons will be dynamically inserted here -->
                <button class="btn btn-primary" onclick="simpanProgress(this)">Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/staff/lhu.js') }}"></script>
@endpush