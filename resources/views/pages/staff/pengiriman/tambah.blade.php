@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content col-md-12">
        <div class="container">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item px-3">
                    <a href="{{ route('staff.pengiriman') }}" class="icon-link text-danger"><i class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="card card-default border-0 color-palette-box shadow py-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">Jenis pengiriman</label>
                                <select name="jenis_pengiriman" id="jenis_pengiriman" class="form-select" multiple>
                                    <option></option>
                                    <option value="invoice">Invoice</option>
                                    <option value="lhu">LHU</option>
                                    <option value="tld">TLD</option>
                                    <option value="spk">SPK</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">No Resi</label>
                                <input type="text" name="no_resi" id="no_resi" class="form-control" placeholder="Inputkan no resi">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">No kontrak/Permohonan</label>
                                <div class="input-group">
                                    <select name="tipe-search" id="tipe-search" class="form-select">
                                        <option value="no_kontrak">No kontrak</option>
                                        <option value="permohonan">Permohonan</option>
                                    </select>
                                    <input type="text" name="no_permohonan" id="no_permohonan" class="form-control w-auto" disabled>
                                    <button class="btn btn-outline-secondary" id="btn-modal-search">Pilih</button>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">Tujuan</label>
                                <input type="text" name="pelanggan" id="pelanggan" class="form-control" disabled>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">Periode</label>
                                <div class="input-group">
                                    <select name="periode" id="periode" class="form-select">
                                        <option value="">Pilih periode</option>
                                    </select>
                                    <input type="text" class="form-control w-auto" id="text-periode" disabled>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">Upload bukti pengiriman</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="uploadBuktiPengiriman" accept="image/*" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                    <button class="btn btn-outline-primary" id="btnTambahBukti">Tambah</button>
                                    <button class="btn btn-outline-secondary" type="button" id="fotoBuktiPengiriman"><i class="bi bi-camera"></i></button>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <ul class="list-group" id="list-jenis">
                                    
                                </ul>
                            </div>
                            <div class="mb-3 col-md-6 d-flex flex-wrap" id="list-preview-bukti">

                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">Alamat</label>
                                <select name="alamat" id="alamat" class="form-select">
                                    <option value="utama">Alamat Utama</option>
                                    <option value="tld">Alamat TLD</option>
                                    <option value="lhu">Alamat LHU</option>
                                    <option value="invoice">Alamat Invoice</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-12">
                                <textarea name="" id="" cols="30" rows="5" class="form-control" disabled></textarea>
                            </div>
                            <div class="mb-3 col-md-12 d-flex justify-content-end">
                                <button class="btn btn-primary" id="btnSendDocument">Kirim Document</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-search" tabindex="-1" aria-labelledby="modal-searchLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="modal-searchLabel">Pilih <span id="label-tipe-search"></span></h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex pb-4">
            <div class="col-12">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search" id="inputSearch" aria-describedby="btnSearch">
                    <button class="btn btn-outline-secondary" type="button" id="btnSearch"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </div>
        <div id="loading-content" class="text-center"></div>
        <div id="list-content">
            <div class="card mb-2">
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="flex-fill">
                        <div class="title" id="txt-title">S-0002/JKRL/X/2024</div>
                        <small class="subdesc text-body-secondary fw-light lh-sm">
                            <div>Pelanggan: Pt sejahtera</div>
                            <div>Layanan TLD - Evaluasi</div>
                        </small>
                    </div>
                    <div class="flex-fill text-center">
                        <div id="txt-periode">3 Periode</div>
                        <div id="txt-pengguna">5 Pengguna</div>
                    </div>
                    <div class="flex-fill text-center">
                        <button class="btn btn-outline-primary btn-sm">Pilih</button>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modal-preview" tabindex="-1" aria-labelledby="modal-searchLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-center">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="#" alt="preview" class="img-fluid rounded" id="modal-preview-image">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/staff/pengiriman_tambah.js') }}"></script>
@endpush
