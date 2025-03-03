@extends('layouts.main')

@section('content')
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item px-3">
        <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="icon-link text-danger"><i class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
    </li>
</ul>
<div class="card shadow-sm border-0 mb-2">
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
                    <label for="" class="form-label col-md-3">Harga</label>
                    <span>{{ formatCurrency($kontrak->total_harga) }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-1">
                    <div class="d-flex align-items-center column-gap-2">
                        <div class="border border-secondary rounded p-2 bg-secondary-subtle shadow-sm">
                            <label for="">(Periode {{ $periodeNow->periode }})</label>
                            <div>{{ convert_date($periodeNow->start_date, 2) }} - {{ convert_date($periodeNow->end_date, 2) }}</div>
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
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-2">
    <div class="card-body row">
        <div class="col-12">
            <h2 class="text-center">TLD Pengguna</h2>
        </div>
        <div class="col-12 overflow-auto" style="max-height: 25rem;">
            <table class="table w-100" id="pengguna-table">
                <thead>
                    <tr>
                        <th width="1%">
                            <input class="form-check-input mt-0" id="checkAllTldPengguna" name="checkAllTldPengguna" type="checkbox" value="" aria-label="Checkbox for following text input">
                        </th>
                        <th width="1%">No</th>
                        <th width="20%">Nama</th>
                        <th width="40%">Radiasi</th>
                        <th width="20%">Kode Lencana TLD</th>
                        <th width="10%">ktp</th>
                    </tr>
                </thead>
                <tbody id="pengguna-list-container" class="align-middle"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-2">
    <div class="card-body row">
        <div class="col-12">
            <h2 class="text-center">TLD Kontrol</h2>
        </div>
        <div class="col-12 overflow-auto" style="max-height: 25rem;">
            <div id="tld-kontrol-content" class="row"></div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-2">
    <div class="card-body row">
        <div class="col-12 text-end">
            <button class="btn btn-outline-primary" onclick="buatPermohonan(this)">Buat Permohonan Evaluasi</button>
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
        const permohonanHash = @json($permohonan);
    </script>
    <script src="{{ asset('js/permohonan/kontrak_evaluasi.js') }}"></script>
@endpush