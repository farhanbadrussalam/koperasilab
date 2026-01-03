@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-end pt-2 me-4">
    {{-- <a class="btn btn-primary" href="{{ route('staff.pengiriman.tambah') }}"><i class="bi bi-plus"></i> Buat pengiriman</a> --}}
</div>
<div class="card shadow-sm m-4 mt-2">
    <div class="card-body">
        <div class="d-flex">
            <div class="flex-grow-1">
                <button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearFilter()">
                    <i class="bi bi-funnel"></i> Clear Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span>
                </button>
            </div>
        </div>
        <div id="list-filter"></div>
        <div class="my-3">
            <div class="body-placeholder my-3" id="list-placeholder-pengiriman">
                @for ($i = 0; $i < 5; $i++)
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
                    <label for="jasa_kurir" class="form-label">Jasa Kurir<span class="text-danger ms-1">*</span></label>
                    <select class="form-select" id="jasa_kurir" name="jasa_kurir" required>
                        <option value="" selected disabled>Pilih Jasa Kurir</option>
                        @foreach ($ekspedisi as $value)
                            <option value="{{ $value->ekspedisi_hash }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="noResi" class="form-label">No Resi<span class="text-danger ms-1">*</span></label>
                    <input type="text" class="form-control" id="noResi" name="noResi" required>
                </div>
                <div class="mb-3">
                    <label for="" class="form-label">Upload bukti pengiriman<span class="text-danger ms-1">*</span></label>
                    <div id="uploadBuktiPengiriman"></div>
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
