@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content col-md-12">
        <div class="container">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="surattugas-tab" onclick="switchLoadTab(1)" data-bs-toggle="tab" data-bs-target="#surattugas-tab-pane" type="button" role="tab" aria-controls="surattugas-tab-pane" aria-selected="true">Penerbitan surat tugas</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="penerbitanlhu-tab" onclick="switchLoadTab(2)" data-bs-toggle="tab" data-bs-target="#penerbitanlhu-tab-pane" type="button" role="tab" aria-controls="penerbitanlhu-tab-pane" aria-selected="true">Penerbitan LHU</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="surattugas-tab-pane" role="tabpanel" aria-labelledby="surattugas-tab" tabindex="0">
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
                                    <div class="col-md-2">Jenis</div>
                                    <div class="col-md-2">Tipe</div>
                                    <div class="col-md-2">Pelanggan</div>
                                    <div class="col-md-3 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-surattugas">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-2 text-center">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="body my-3" id="list-container-surattugas">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-surattugas">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="penerbitanlhu-tab-pane" role="tabpanel" aria-labelledby="penerbitanlhu-tab" tabindex="0">
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
                                    <div class="col-md-3">Tipe</div>
                                    <div class="col-md-4 text-center">Tanggal</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>
                                <hr>
                                <div class="body-placeholder my-3" id="list-placeholder-penerbitanlhu">
                                    @for ($i = 0; $i < 3; $i++)
                                    <div class="card mb-2">
                                        <div class="card-body row align-items-center">
                                            <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-50 mb-1"></div>
                                                <div class="placeholder w-75 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-3 text-end text-md-start">
                                                <div class="placeholder w-50 mb-1"></div>
                                            </div>
                                            <div class="placeholder-glow col-6 col-md-4 text-center">
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
                                <div class="body my-3" id="list-container-penerbitanlhu">

                                </div>
                                <div aria-label="Page navigation example" id="list-pagination-penerbitanlhu">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="suratTugasModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="suratTugasModalLabel">Buat surat tugas</h1>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" onclick="closeModal()"></button>
            </div>
            <div class="modal-body px-1">
                <input type="hidden" name="txtIdPenyelia" id="txtIdPenyelia">
                <div class="row mx-2">
                    <div class="col-sm-6 mb-3">
                        <label for="inputStartDate">Start Date</label>
                        <input type="text" name="inputStartDate" id="inputStartDate" class="form-control datepicker">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="inputEndDate">End Date</label>
                        <input type="text" name="inputEndDate" id="inputEndDate" class="form-control bg-secondary-subtle" readonly>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <div class="input-group">
                            <select name="inputPetugas" id="inputPetugas"></select>
                            <button class="btn btn-secondary" onclick="tambahPetugas()"><i class="bi bi-person-plus-fill"></i> Petugas</button>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <div class="rounded border p-2" id="list-petugas"></div>
                    </div>
                    <div class="col-md-12 d-flex justify-content-center mb-3">
                        <div class="wrapper" id="content-ttd"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex" id="modalFooter">
                <!-- Footer buttons will be dynamically inserted here -->
                <button class="btn btn-primary" onclick="simpanSuratTugas(this)">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateProgressModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="updateProgressModalLabel">Update progress</h1>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" onclick=""></button>
            </div>
            <div class="modal-body px-1">
                <div class="row mx-2">
                    <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                        <label for="" class="fw-bold">Tanggal</label>
                        <div>
                            <input type="text" class="form-control" id="dateProgress">
                        </div>
                    </div>
                    <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                        <label for="" class="fw-bold">Status</label>
                        <div>
                            <div class="form-check form-check-inline" id="divReturnProgress">
                                <input class="form-check-input" type="radio" name="statusProgress" id="statusReturn" value="return">
                                <label class="form-check-label text-danger" for="statusReturn">Return</label>
                            </div>
                            <div class="form-check form-check-inline" id="divDoneProgress">
                                <input class="form-check-input" type="radio" name="statusProgress" id="statusDone" value="done" checked>
                                <label class="form-check-label text-success" for="statusDone">Done</label>
                            </div>
                        </div>
                    </div>
                    <span class="fw-bold">Progress</span>
                    <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
                        <input type="text" class="form-control bg-secondary-subtle" name="prosesNow" id="prosesNow" readonly>
                        <span class="mx-2">To</span>
                        <input type="text" class="form-control bg-secondary-subtle" name="prosesNext" id="prosesNext" readonly>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <label for="inputNote">Note</label>
                        <textarea name="inputNote" id="inputNote" cols="30" rows="5" class="form-control"></textarea>
                    </div>
                    <div id="divUploadDocLhu">
                        <label for="upload_document" class="col-form-label">Upload Document LHU</label>
                        <div class="card mb-0" style="height: 150px;">
                            <input type="file" name="dokumen" id="upload_document" class="form-control dropify">
                        </div>
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
    <script src="{{ asset('js/staff/penyelia.js') }}"></script>
@endpush