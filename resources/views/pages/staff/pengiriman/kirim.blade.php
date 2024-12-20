@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content col-md-12">
            <div class="container">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item px-3">
                        <a href="{{ route('staff.pengiriman.permohonan') }}" class="icon-link text-danger"><i
                                class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
                    </li>
                </ul>
                <div class="card card-default border-0 color-palette-box shadow py-3">
                    <div class="card-body">
                        <div class="fw-semibold fs-5">Rincian</div>
                        <div class="row row-gap-3">
                            <div class="col-md-6">
                                <label for="" class="form-label">No Pengiriman</label>
                                <input type="text" name="no_pengiriman" id="no_pengiriman"
                                    class="form-control bg-secondary-subtle" value="{{ $noPengiriman }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="" class="form-label">Pelanggan</label>
                                <input type="text" class="form-control bg-secondary-subtle" name="txtPelanggan"
                                    id="txtPelanggan"
                                    value="{{ $permohonan->pelanggan->perusahaan->nama_perusahaan }} - {{ $permohonan->pelanggan->name }}"
                                    readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="" class="form-label">No kontrak</label>
                                <input type="text" name="txt_no_kontrak" id="txt_no_kontrak"
                                    class="form-control bg-secondary-subtle" value="{{ $permohonan->kontrak->no_kontrak }}"
                                    readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="" class="form-label">Jenis</label>
                                <input type="text" name="txt_jenis" id="txt_jenis"
                                    class="form-control bg-secondary-subtle"
                                    value="{{ $permohonan->jenis_layanan_parent->name }}-{{ $permohonan->jenis_layanan->name }}"
                                    readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="" class="form-label">No resi</label>
                                <input type="text" name="txt_resi" id="txt_resi" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="" class="form-label">Alamat tujuan</label>
                                <select name="select_alamat" id="select_alamat" class="form-select">
                                    <option value="">Pilih alamat</option>
                                </select>
                                <textarea name="alamatTujuan" id="alamatTujuan" cols="30" rows="4"
                                    class="form-control bg-secondary-subtle mt-2" readonly></textarea>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">Upload bukti pengiriman</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="uploadBuktiPengiriman" accept="image/*"
                                        aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                    <button class="btn btn-outline-primary" id="btnTambahBukti">Tambah</button>
                                    <button class="btn btn-outline-secondary" type="button" id="fotoBuktiPengiriman"><i
                                            class="bi bi-camera"></i></button>
                                </div>
                                <div id="list-preview-bukti" class="mt-2 d-flex column-gap-2">

                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="fw-semibold fs-5">List document</div>
                        <div class="col-md-12 mt-2 row-gap-2" id="list-document">
                            
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-secondary me-3" id="btnCetakSurat"><i
                                    class="bi bi-printer-fill"></i> Cetak Surat Pengantar</button>
                            <button type="button" class="btn btn-primary" onclick="buatPengiriman(this)">Buat pengiriman</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
    <script>
        const permohonan = @json($permohonan);
    </script>
    <script src="{{ asset('js/staff/pengiriman_send.js') }}"></script>
@endpush
