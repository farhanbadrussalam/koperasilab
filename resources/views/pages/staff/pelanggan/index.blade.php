@extends('layouts.main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div id="list-container"></div>
            <div id="list-pagination"></div>
        </div>
    </div>
</div>

<!-- Modal Pelanggan Baru -->
<div class="modal fade" id="modalVerifikasiBaru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verifikasi Pelanggan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_request">
                <p>Pastikan kelengkapan data perusahaan dan surat kuasa pelanggan baru sudah sesuai dan valid.</p>
                <div class="mb-3" id="formKodePerusahaan">
                    <label class="form-label" id="labelKodePerusahaan">Kode Perusahaan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="kode_perusahaan" placeholder="Masukkan kode perusahaan secara manual...">
                    <div class="invalid-feedback" id="errorKodePerusahaan">
                        Please choose a username.
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <div>
                    <button type="button" class="btn btn-danger">Tolak</button>
                    <button type="button" class="btn btn-primary" onclick="verifikasiPelanggan(this, 'baru')" id="btnVerifikasiBaru" disabled>Verifikasi Data</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pelanggan Lama -->
<div class="modal fade" id="modalVerifikasiLama" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verifikasi Pelanggan Lama</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah surat kuasa yang dilampirkan sudah sesuai dengan perusahaan terkait?</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <div>
                    <button type="button" class="btn btn-danger" onclick="tolakPelanggan(this)">Tolak</button>
                    <button type="button" class="btn btn-primary" onclick="verifikasiPelanggan(this, 'baru')" id="btnVerifikasiBaru">Verifikasi Data</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/staff/approval_pelanggan.js') }}"></script>
@endpush
