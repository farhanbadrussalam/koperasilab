@extends('layouts.main')

@section('content')
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
                <div class="col-md-1">&nbsp;</div>
                <div class="col-md-2">Tujuan</div>
                <div class="col-md-2 text-center">Status</div>
                <div class="col-md-2 text-center">Tanggal</div>
                <div class="col-md-2 text-center">Action</div>
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

<!-- Modal -->
<div class="modal fade" id="modal-diterima" tabindex="-1" aria-labelledby="modal-diterimaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="modal-diterimaLabel">Dokumen diterima</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="mb-2 col-md-12">
                <label for="" class="form-label">Tanggal diterima</label>
                <input type="hidden" name="idPengiriman" id="idPengiriman">
                <input type="hidden" name="isLhuSend" id="isLhuSend">
                <input type="text" class="form-control" name="txt_date_diterima" id="txt_date_diterima">
            </div>
            <div class="mb-2 col-md-12">
                <div id="surpengDiv"></div>
            </div>
            <p class="d-inline-flex gap-1 mb-2 col-md-12">
                <button class="btn btn-outline-info btn-sm w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBuktiPengiriman" aria-expanded="false" aria-controls="collapseBuktiPengiriman">
                    Lihat Bukti Pengiriman
                </button>
            </p>
            <div class="collapse" id="collapseBuktiPengiriman">
                <div class="card card-body mb-2">
                    <div id="showBuktiPengiriman"></div>
                </div>
            </div>
            <div class="mb-2 col-md-12">
                <label for="" class="form-label">Kelengkapan dokumen<span class="text-danger ms-1">*</span></label>
                <ul class="list-group w-100" id="list-kelengkapan">

                </ul>
            </div>
            <div class="mb-2 col-md-12">
                <label for="" class="form-label">Upload bukti penerima<span class="text-danger ms-1">*</span></label>
                <div id="uploadBuktiPenerima">
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="btnSendDocument">Simpan</button>
        </div>
      </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const idPelanggan = `{{ Auth::user()->user_hash }}`;
    </script>
    <script src="{{ asset('js/permohonan/pengiriman.js') }}"></script>
@endpush