@extends('layouts.main')

@section('content')

    <input type="hidden" name="id_permohonan" id="id_permohonan" value="{{ $permohonan->permohonan_hash }}">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item px-3">
            <a href="{{ route('permohonan.pengajuan') }}" class="icon-link text-danger"><i class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
        </li>
    </ul>
    <div class="m-0 row">
        <div class="card mb-3 shadow border-0">
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <h3 class="fw-bold">Pilih Layanan Jasa</h3>
                        <select name="layanan_jasa" id="layanan_jasa" class="form-select">
                            @foreach ($layanan_jasa as $value)
                            <option value="{{ $value->layanan_hash }}">{{ $value->nama_layanan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 shadow border-0">
            <div class="card-body">
                <div class="row g-2 g-md-3">
                    <div class="col-md-6">
                        <label for="jenis_layana" class="col-form-label">Jenis layanan</label>
                        <select name="jenis_layanan" id="jenis_layanan" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($jenisLayanan as $value)
                                @if($value->parent == null)
                                <option value="{{ $value->jenis_layanan_hash }}">{{ $value->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="jenis_layanan_2" class="col-form-label d-none d-md-flex">&nbsp;</label>
                        <select name="jenis_layanan_2" id="jenis_layanan_2" class="form-select">
                            <option value="">Pilih</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow d-none border-0" id="form-inputan">
            <div class="card-body">
                <form action="#" method="post" id="form-simpan-pengajuan">
                    @csrf
                    <div class="row g-0 g-md-3">
                        <div class="col-md-6" id="form-tipe-kontrak">
                            <label class="col-form-label" for="tipe_kontrak">Tipe kontrak</label>
                            <select name="tipe_kontrak" id="tipe_kontrak" class="form-select">
                                <option value="kontrak baru">Kontrak Baru</option>
                                <option value="perpanjangan">Perpanjangan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div id="form-no-kontrak">
                                <label class="col-form-label" for="no_kontrak">No kontrak</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control bg-secondary-subtle" name="no_kontrak" id="no_kontrak" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="btn-kontrak">Select kontrak</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="form-zero-cek">
                            <label class="col-form-label" for="zero_cek">Pilih zero cek</label>
                            <select name="zero_cek" id="zero_cek" class="form-select">
                                <option value="">Pilih</option>
                                <option value="zerocek">Dengan zero cek</option>
                                <option value="tanpazerocek">Tanpa zero cek</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="form-jenis-tld">
                            <label class="col-form-label" for="jenis_tld">Jenis TLD</label>
                            <select name="jenis_tld" id="jenis_tld" class="form-select">
                                <option value="">Pilih</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="form-periode">
                            <label class="col-form-label" for="periode">Periode pemakaian</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control bg-secondary-subtle" id="periode-pemakaian" aria-label="Periode pemakaian" readonly>
                                <button class="btn btn-outline-danger d-none" type="button" id="btn-clear-periode">Clear</button>
                                <button class="btn btn-outline-secondary" type="button" id="btn-periode">Select periode</button>
                            </div>
                        </div>
                        <div class="col-md-6" id="form-jum-pengguna">
                            <label class="col-form-label" for="jum_pengguna">Jumlah Pengguna</label>
                            <input type="number" name="jum_pengguna" id="jum_pengguna" class="form-control bg-secondary-subtle" readonly>
                        </div>
                        <div class="col-md-6" id="form-jum-kontrol">
                            <label class="col-form-label" for="jum_kontrol">Jumlah Kontrol</label>
                            <input type="number" name="jum_kontrol" id="jum_kontrol" class="form-control" oninput="calcPrice()">
                        </div>
                        <div class="col-md-6" id="form-pic">
                            <label class="col-form-label" for="pic">PIC</label>
                            <input type="text" name="pic" id="pic" class="form-control">
                        </div>
                        <div class="col-md-6" id="form-nohp">
                            <label class="col-form-label" for="nohp">No HP</label>
                            <input type="text" name="nohp" id="nohp" class="form-control">
                        </div>
                        <div class="col-md-6" id="form-alamat">
                            <label class="col-form-label" for="alamat">Alamat</label>
                            <input type="text" name="alamat" id="alamat" class="form-control">
                        </div>
                        <div class="col-md-6" id="form-periode-next">
                            <label class="col-form-label" for="periode_next">Periode pemakaian selanjutnya</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control bg-secondary-subtle" id="periode_next" aria-label="Periode pemakaian selanjutnya" readonly>
                                <button class="btn btn-outline-danger d-none" type="button" id="btn-clear-periode-next">Clear</button>
                                <button class="btn btn-outline-secondary" type="button" id="btn-periode-next">Select periode</button>
                            </div>
                        </div>
                        <div class="col-md-6" id="form-periode-1">
                            <label class="col-form-label" for="periode_1">Periode 1</label>
                            <input type="text" name="periode_1" id="periode_1" class="form-control">
                        </div>
                        <div class="col-md-6" id="form-periode-2">
                            <label class="col-form-label" for="periode_2">Periode 2</label>
                            <input type="text" name="periode_2" id="periode_2" class="form-control">
                        </div>
                        <div class="col-md-12" id="form-total-harga">
                            <label class="col-form-label" for="total_harga" id="label_total_harga">Total harga </label>
                            <div class="input-group">
                                <span class="input-group-text" id="inputGroup-sizing-default">Rp</span>
                                <input type="text" name="total_harga" id="total_harga" class="form-control bg-secondary-subtle rupiah" readonly>
                            </div>
                            <small class="text-info">*Tidak termasuk PPN</small>
                        </div>
                    </div>
                </form>
                <div class="pengguna my-3">
                    <button class="btn btn-secondary" id="btn-add-pengguna">Tambah Pengguna</button>
                    <button class="btn btn-secondary d-none" id="btn-pilih-pengguna">Pilih Pengguna</button>
                    <div class="border border-opacity-50 rounded my-3 p-3">
                        <div class="body-placeholder my-3" id="pengguna-placeholder">
                            @for ($i = 1; $i < 6; $i++)
                            <div class="card mb-2 shadow-sm border-dark">
                                <div class="card-body row align-items-center">
                                    <div class="placeholder-glow col-md-4 lh-sm d-flex flex-column">
                                        <div class="placeholder w-50 mb-1"></div>
                                        <div class="placeholder w-25 mb-1"></div>
                                    </div>
                                    <div class="placeholder-glow col-md-3">
                                        <div class="placeholder w-50 mb-1"></div>
                                    </div>
                                    <div class="placeholder-glow col-md-3">
                                        <div class="placeholder w-50 mb-1"></div>
                                    </div>
                                    <div class="placeholder-glow col-md-2 text-end">
                                        <div class="placeholder w-50 mb-1"></div>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                        <div class="body my-3" id="pengguna-list-container">
                            
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" id="simpanPengajuan">Simpan pengajuan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal-add-pengguna" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Create pengguna</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body g-2 row">
                    <div>
                        <label for="nama_pengguna" class="col-form-label">Nama Pengguna</label>
                        <input type="text" name="nama_pengguna" id="nama_pengguna" class="form-control">
                    </div>
                    <div>
                        <label for="divisi_pengguna" class="col-form-label">Divisi Pengguna</label>
                        <input type="text" name="divisi_pengguna" id="divisi_pengguna" class="form-control">
                    </div>
                    <div>
                        <label for="jenis_radiasi" class="col-form-label">Jenis/Energi Radiasi</label>
                        <select name="jenis_radiasi" id="jenis_radiasi" class="form-select" multiple="multiple">
                            @foreach ($dataRadiasi as $value)
                                <option value="{{ $value->radiasi_hash }}">{{ $value->nama_radiasi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="upload_ktp" class="col-form-label">Upload KTP</label>
                        <div class="card mb-0" style="height: 150px;">
                            <input type="file" name="dokumen" id="uploadKtpPengguna" accept="image/*" class="form-control dropify">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-tambah-pengguna">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-pilih-pengguna" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">List pengguna</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row justify-content-center">
                    <div class="body my-3 d-none">
                        @for ($i=0;$i<5;$i++)
                        <div class="row align-items-center rounded border-bottom mb-2">
                            <div class="col-md-8 lh-sm">
                                <div>Nama sesuai KTP</div>
                                <small class="subdesc text-body-secondary fw-light">Divisi</small>
                            </div>
                            <div class="col-md-3">
                                Jenis radiasi
                            </div>
                            <div class="col-md-1">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" checked>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary">Pilih Pengguna</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/permohonan/pengajuan_tambah.js') }}"></script>
@endpush
