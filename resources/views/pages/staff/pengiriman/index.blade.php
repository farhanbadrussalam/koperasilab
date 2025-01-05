@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-end pt-2 me-4">
    <a class="btn btn-primary" href="{{ route('staff.pengiriman.tambah') }}"><i class="bi bi-plus"></i> Buat pengiriman</a>
</div>
<div class="card shadow-sm m-4 mt-2">
    <div class="card-body">
        <div class="d-flex">
            <div class="flex-grow-1"><button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button></div>
        </div>
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
                <div class="col-md-3">&nbsp;</div>
                <div class="col-md-2">&nbsp;</div>
                <div class="col-md-2">Tujuan</div>
                <div class="col-md-2 text-center">Status</div>
                <div class="col-md-3 text-center">Action</div>
            </div>
            <hr>
            <div class="body-placeholder my-3" id="list-placeholder-pengiriman">

            </div>
            <div class="body my-3" id="list-container-pengiriman">
            </div>
            <div aria-label="Page navigation example" id="list-pagination-pengiriman">
                    
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-buat-pengiriman" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Create pengiriman</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row justify-content-center">
                <div class="col-6">
                    <div class="mb-3">
                        <label for="" class="form-label">No kontrak/Permohonan</label>
                        
                    </div>
                </div>
                <div class="col-6"></div>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail-pengiriman" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="detailModalLabel">Detail Pengiriman</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <label for="detailNoKontrak" class="form-label">No Kontrak/Permohonan</label>
                        <input type="text" class="form-control" id="detailNoKontrak" readonly>
                    </div>
                    <div class="col-6">
                        <label for="detailTanggal" class="form-label">Tanggal</label>
                        <input type="text" class="form-control" id="detailTanggal" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label for="detailJenis" class="form-label">Jenis</label>
                        <input type="text" class="form-control" id="detailJenis" readonly>
                    </div>
                    <div class="col-6">
                        <label for="detailTujuan" class="form-label">Tujuan</label>
                        <input type="text" class="form-control" id="detailTujuan" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="detailStatus" class="form-label">Status</label>
                        <input type="text" class="form-control" id="detailStatus" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="detailItems" class="form-label">Items</label>
                        <ul class="list-group" id="detailItems">
                            <!-- List of items like LHU / Invoice will be appended here -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-kirim-dokumen" tabindex="-1" aria-labelledby="kirimDokumenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="kirimDokumenModalLabel">Kirim Dokumen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="no_pengiriman" class="form-label">No pengiriman</label>
                    <input type="text" class="form-control bg-secondary-subtle" id="no_pengiriman" name="no_pengiriman" readonly>
                </div>
                <div class="mb-3">
                    <label for="jasa_kurir" class="form-label">Jasa Kurir</label>
                    <select class="form-select" id="jasa_kurir" name="jasa_kurir" required>
                        <option value="" selected disabled>Pilih Jasa Kurir</option>
                        @foreach ($ekspedisi as $value)
                            <option value="{{ $value->ekspedisi_hash }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="noResi" class="form-label">No Resi</label>
                    <input type="text" class="form-control" id="noResi" name="noResi" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="formKirimDokumen" class="btn btn-primary" onclick="kirimDokumen(this)">Kirim</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/staff/pengiriman.js') }}"></script>
@endpush