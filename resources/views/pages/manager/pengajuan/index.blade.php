@extends('layouts.main')

@section('content')
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
                <div class="col-md-3">Jenis</div>
                <div class="col-md-2">Tipe</div>
                <div class="col-md-2">Pelanggan</div>
                <div class="col-md-2 text-center">Action</div>
            </div>
            <hr>
            <div class="body-placeholder my-3" id="list-placeholder">
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
            <div class="body my-3" id="list-container">
                
            </div>
            <div aria-label="Page navigation example" id="list-pagination">
                
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-verif-invoice" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modal-verif-invoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="verifInvoiceModalLabel">Verifikasi Invoice</h1>
          <button type="button" class="btn-close" aria-label="Close" onclick="closeInvoice()"></button>
        </div>
        <div class="modal-body px-4">
            <input type="hidden" name="idKeuangan" id="idKeuangan">
            <div class="row mx-2">
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">No Invoice</label>
                    <div id="txtNoInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">No Kontrak</label>
                    <div id="txtNoKontrakInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Jenis</label>
                    <div id="txtJenisInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Pengguna</label>
                    <div id="txtPenggunaInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Tipe Kontrak</label>
                    <div id="txtTipeKontrakInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Pelanggan</label>
                    <div id="txtPelangganInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Jenis TLD</label>
                    <div id="txtJenisTldInvoice">-</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Instansi</label>
                    <div id="txtInstansiInvoice">-</div>
                </div>
            </div>
            <hr class="my-2">
            <div class="border rounded p-3 mt-3">
                <table class="table w-100 text-center">
                    <thead>
                        <tr>
                            <th class="text-start" width="40%">Rincian</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Periode (Bulan)</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody id="deskripsiInvoice">
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <div class="col-md-12 d-flex justify-content-center">
                    <div class="wrapper" id="content-ttd"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer d-flex justify-content-center">
          <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-verif-invalid">Ditolak</button>
          <button type="button" class="btn btn-primary" onclick="simpanInvoice(this)">Setuju</button>
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="modal-verif-invalid" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Data ditolak</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row justify-content-center">
                <div class="row">
                    <div class="col-md-12">
                        <label class="col-form-label" for="txt_note">Note</label>
                        <textarea name="txt_note" id="txt_note" rows="3" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="tolakInvoice(this)">Return</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/manager/pengajuan.js') }}"></script>
@endpush