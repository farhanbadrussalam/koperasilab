@extends('layouts.main')

@section('content')
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item px-3">
        <a href="{{ route('permohonan.kontrak') }}" class="icon-link text-danger"><i class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
    </li>
</ul>
<div class="card shadow-sm mt-2">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-1">
                    <label for="" class="form-label col-md-3">No Kontrak</label>
                    <span>{{ $kontrak->no_kontrak ?? '-' }}</span>
                </div>
                <div class="mb-1">
                    <label for="" class="form-label col-md-3">Pelanggan</label>
                    <span>{{ $kontrak->pelanggan->perusahaan->nama_perusahaan }} - {{ $kontrak->pelanggan->name }}</span>
                </div>
                <div class="mb-1">
                    <label for="" class="form-label col-md-3">Layanan</label>
                    <span><span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">{{ $kontrak->jenis_layanan->name }}</span></span>
                </div>
                <div class="mb-1">
                    <label for="" class="form-label col-md-3">pengguna</label>
                    <span>{{ $kontrak->jumlah_pengguna }}</span>
                </div>
                <div class="mb-1">
                    <label for="" class="form-label col-md-3">Kontrol</label>
                    <span>{{ $kontrak->jumlah_kontrol }}</span>
                </div>
                <div class="mb-1">
                    <label for="" class="form-label col-md-3">Harga</label>
                    <span>{{ formatCurrency($kontrak->total_harga) }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-1">
                    <label for="" class="form-label col-md-3">
                        Periode
                    </label>
                    <div class="d-flex align-items-center column-gap-2">
                        <div class="border border-secondary rounded p-2 bg-secondary-subtle shadow-sm">
                            <label for="">Sekarang <i>(Periode {{ $periodeNow->periode }})</i></label>
                            <div>{{ convert_date($periodeNow->start_date, 2) }} - {{ convert_date($periodeNow->end_date, 2) }}</div>
                        </div>
                        <div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                        <div class="border border-success rounded p-2 bg-success-subtle shadow-sm">
                            <label for="">Selanjutnya @if ($periodeNext)<i>(Periode {{ $periodeNext->periode }})</i>@endif</label>
                            <div>
                                @if($periodeNext)
                                    {{ convert_date($periodeNext->start_date, 2) }} - {{ convert_date($periodeNext->end_date, 2) }}
                                @else
                                    Periode Selesai
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-1">
                    <label for="" class="form-label col-md-3">Alamat</label>
                    <div>
                        <select name="selectAlamat" id="selectAlamat" class="form-select">
                            <option value="">Pilih alamat</option>
                        </select>
                        <textarea name="txt_alamat" id="txt_alamat" cols="30" rows="2" class="form-control mt-1 bg-secondary-subtle" readonly></textarea>
                    </div>
                </div>
            </div>
            <div class="col-12 text-end mt-3">
                <button class="btn btn-outline-primary" onclick="buatPermohonan(this)">Buat Permohonan Evaluasi</button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" value="" id="flexCheckTldAll" name="flexCheckPenggunaAll" checked>
                    <label class="form-check-label" for="flexCheckTldAll">All</label>
                </div>
                <h5 class="card-title">TLD</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" id="listTld">
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" value="" id="flexCheckPenggunaAll" name="flexCheckPenggunaAll" checked>
                    <label class="form-check-label cursor-pointer" for="flexCheckPenggunaAll">All</label>
                </div>
                <h5 class="card-title">Pengguna</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" id="listPengguna">
                    
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const dataKontrak = @json($kontrak);
        const dataPeriodeNow = @json($periodeNow);
        const dataPeriodeNext = @json($periodeNext);
        const dataJenisLayanan = @json($jenisLayanan);
    </script>
    <script src="{{ asset('js/permohonan/kontrak_evaluasi.js') }}"></script>
@endpush