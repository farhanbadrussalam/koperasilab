@extends('layouts.main')

@section('content')
    <input type="hidden" name="id_permohonan" id="id_permohonan" value="{{ $permohonan->permohonan_hash }}">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item px-3">
            <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="icon-link text-danger"><i
                    class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
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
                        <label for="jenis_layana" class="col-form-label">Jenis layanan<span
                                class="text-danger ms-1">*</span></label>
                        <select name="jenis_layanan" id="jenis_layanan" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($jenisLayanan as $value)
                                @if ($value->parent == null)
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
                <div class="w-100 mt-2" id="div-buat-form">
                    <button class="btn btn-primary float-end" id="btn-buat-form">Buat form</button>
                </div>
            </div>
        </div>

        <div class="card shadow d-none border-0" id="form-inputan">
            <div class="card-body">
                <form action="#" method="post" id="form-simpan-pengajuan">
                    @csrf
                    <div class="row g-0 g-md-3">
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
                            <label class="col-form-label" for="periode">Periode pemakaian<span
                                    class="text-danger ms-1">*</span></label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control bg-secondary-subtle" id="periode-pemakaian"
                                    aria-label="Periode pemakaian" readonly>
                                <button class="btn btn-outline-danger d-none" type="button"
                                    id="btn-clear-periode">Clear</button>
                                <button class="btn btn-outline-secondary" type="button" id="btn-periode">Select
                                    periode</button>
                            </div>
                        </div>
                        <div class="col-md-6" id="form-jum-pengguna">
                            <div class="d-flex justify-content-between">
                                <label class="col-form-label" for="jum_pengguna">Pengguna<span
                                        class="text-danger ms-1">*</span></label>
                                <a class="text-decoration-none cursor-pointer text-primary hover-text pt-2"
                                    id="btn-add-pengguna"><i class="bi bi-plus-circle"></i> Tambah</a>
                            </div>
                            <input type="text" name="jum_pengguna" id="jum_pengguna"
                                class="form-control bg-secondary-subtle" readonly>
                            <div id="pengguna-list-container"
                                class="border border-opacity-50 rounded p-1 bg-body-tertiary overflow-y-auto overflow-x-hidden collapse show"
                                style="max-height: 40vh;">

                            </div>
                            <div class="d-flex justify-content-end">
                                <a class="text-decoration-none cursor-pointer text-primary-emphasis hover-text"
                                    data-bs-toggle="collapse" data-bs-target="#pengguna-list-container"
                                    aria-expanded="true" aria-controls="pengguna-list-container"
                                    onclick="showHideCollapse(this)">
                                    <i class="bi bi-eye-slash"></i> Lebih sedikit
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6" id="form-jum-kontrol">
                            <div class="d-flex justify-content-between">
                                <label class="col-form-label" for="jum_kontrol">Kontrol<span
                                        class="text-danger ms-1">*</span></label>
                                <a id="btnTambahKontrol"
                                    class="text-decoration-none cursor-pointer text-primary hover-text pt-2"
                                    onclick="addFormKontrol()"><i class="bi bi-plus-circle"></i> Tambah</a>
                            </div>
                            <input type="number" name="jum_kontrol" id="jum_kontrol"
                                class="form-control bg-secondary-subtle" oninput="calcPrice()" readonly>
                            <div id="divKontrolEvaluasi">
                                <div id="kontrol-list-container"
                                    class="border border-opacity-50 rounded p-1 bg-body-tertiary overflow-y-auto overflow-x-hidden collapse show"
                                    style="max-height: 40vh;">

                                </div>
                                <div class="d-flex justify-content-end">
                                    <a class="text-decoration-none cursor-pointer text-primary-emphasis hover-text"
                                        data-bs-toggle="collapse" data-bs-target="#kontrol-list-container"
                                        aria-expanded="true" aria-controls="kontrol-list-container"
                                        onclick="showHideCollapse(this)">
                                        <i class="bi bi-eye-slash"></i> Lebih sedikit
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="form-pic">
                            <label class="col-form-label" for="pic">PIC<span
                                    class="text-danger ms-1">*</span></label>
                            <input type="text" name="pic" id="pic" class="form-control">
                        </div>
                        <div class="col-md-6" id="form-nohp">
                            <label class="col-form-label" for="nohp">No HP<span
                                    class="text-danger ms-1">*</span></label>
                            <input type="text" name="nohp" id="nohp" class="form-control maskTelepon">
                        </div>
                        <div class="col-md-6" id="form-alamat">
                            <label for="" class="form-label col-md-3">Alamat</label>
                            <div>
                                <select name="selectAlamat" id="selectAlamat" class="form-select">
                                    <option value="">Pilih alamat</option>
                                </select>
                                <textarea name="txt_alamat" id="txt_alamat" cols="30" rows="2"
                                    class="form-control mt-1 bg-secondary-subtle" readonly></textarea>
                            </div>
                        </div>
                        <div class="col-md-6" id="form-periode-next">
                            <label class="col-form-label" for="periode_next">Periode pemakaian selanjutnya</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control bg-secondary-subtle" id="periode_next"
                                    aria-label="Periode pemakaian selanjutnya" readonly>
                                <button class="btn btn-outline-danger d-none" type="button"
                                    id="btn-clear-periode-next">Clear</button>
                                <button class="btn btn-outline-secondary" type="button" id="btn-periode-next">Select
                                    periode</button>
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
                                <input type="text" name="total_harga" id="total_harga"
                                    class="form-control bg-secondary-subtle rupiah" readonly>
                            </div>
                            <small class="text-info">*Tidak termasuk PPN</small>
                        </div>
                    </div>
                </form>
                {{-- <div class="my-3 d-flex gap-2">
                    <div class="flex-fill">
                        <button class="btn btn-outline-secondary btn-sm" id="btn-add-pengguna">Tambah Pengguna</button>
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
                    <div class="flex-fill">
                        <button class="btn btn-outline-secondary btn-sm" id="btn-add-kontrol">Tambah Kontrol</button>
                    </div>
                </div> --}}
                <div class="d-flex justify-content-between mt-3">
                    <button class="btn btn-outline-danger" id="hapusPengajuan" onclick="remove()">Hapus
                        pengajuan</button>
                    <button class="btn btn-primary" id="simpanPengajuan">Simpan pengajuan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal-add-tld-pengguna" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Tambahkan pengguna</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body g-2 row">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="flex-grow-1">
                            <button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i
                                    class="bi bi-arrow-clockwise"></i> Refresh data</button>
                        </div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-add-pengguna"><i
                                class="bi bi-plus"></i> Tambah pengguna</button>
                    </div>
                    <table class="table table-hover w-100 align-middle" id="table-pengguna">
                        <thead>
                            <th width="5%">No</th>
                            <th>Name</th>
                            <th width="20%">Divisi</th>
                            <th width="15%" class="text-center">Action</th>
                        </thead>
                    </table>
                    <div id="form-kode-lencana-pengguna">
                        <label for="kodeLencanaPengguna" class="col-form-label">No Seri TLD</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control rounded-start" id="noSeriPengguna"
                                placeholder="Pilih No Seri" readonly>
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="openInventory(this, 'pengguna')"><i class="bi bi-arrow-repeat"></i>
                                Ganti</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('pages.management.pengguna.create')
@endsection
@push('scripts')
    <script>
        const dataPermohonan = @json($permohonan);
    </script>
    <script src="{{ asset('js/permohonan/pengajuan_tambah.js') }}"></script>
@endpush
