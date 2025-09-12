@extends('layouts.main')

@section('content')
    <div class="row" id="pembayaran-content"></div>

    {{-- modal untuk tambah methode pembayaran --}}
    <div class="modal fade" id="modal-tambah-pembayaran" tabindex="-1"  aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLabel">Tambah Metode Pembayaran</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="content-tambah-pembayaran">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="col-form-label" for="txt_nama">Nama</label>
                            <input type="text" name="txt_nama" id="txt_nama" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Variables (huruf BESAR, pisahkan koma)</label>
                            <textarea id="varsInput" class="form-control"></textarea>
                            <input type="hidden" name="variables[]" id="varsHidden">
                            <small class="text-muted">Contoh: NAMA,NO_VA,TANGGAL,ALAMAT</small>
                        </div>
                        <div class="col-md-12">
                            <label class="col-form-label" for="txt_content">Content</label>
                            <textarea name="txt_content" id="txt_content" cols="30" rows="5" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="simpan(this)">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/staff/pembayaran.js') }}"></script>
@endpush
