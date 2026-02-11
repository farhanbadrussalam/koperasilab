@extends('layouts.main')

@section('content')
@php
    $pengembalianStart = '';
    $pengembalianEnd = '';
    if(!$periode2Next || !$isSewa){
        $startDate = new DateTime($periodeNow->end_date);
        // $startDate->modify('first day of this month');
        $startDate->modify('first day of +4 months');

        $endDate = clone $startDate;
        $endDate->modify('last day of +2 months');

        $pengembalianStart = $startDate->format('Y-m-d');
        $pengembalianEnd = $endDate->format('Y-m-d');
    }
@endphp
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item px-3">
        <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="icon-link text-danger"><i class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
    </li>
</ul>
<div class="row g-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 mb-0">
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
                            <div class="d-flex align-items-center column-gap-2 align-items-stretch align-self-center">
                                <div class="border border-secondary rounded p-2 bg-secondary-subtle shadow-sm {{ $periodeNow->start_date ? '' : 'd-none' }}">
                                    <div for="">Periode Pemakaian</div>
                                    <small>{{ convert_date($periodeNow->start_date, 2) }} - {{ convert_date($periodeNow->end_date, 2) }}</small>
                                </div>
                                <div class="border border-primary rounded p-2 bg-primary-subtle shadow-sm {{ $periode2Next ? '' : 'd-none' }}">
                                    <div for="">Periode Berikutnya</div>
                                    <small>{{ $periode2Next ? convert_date($periode2Next->start_date, 2) : '-' }} - {{ $periode2Next ? convert_date($periode2Next->end_date, 2) : '-' }}</small>
                                </div>
                                <div class="border border-primary rounded p-2 bg-primary-subtle shadow-sm align-content-center {{ $periode2Next || $isSewa ? 'd-none' : '' }}">
                                    <div for="">Pengembalian ke {{ $periodeNow->periode % 2 == 0 ? '1' : '2' }}</div>
                                    <small>{{ convert_date($pengembalianStart, 2) }} - {{ convert_date($pengembalianEnd, 2) }}</small>
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
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-center mb-3">TLD Pengguna</h5>
                <div class="table-responsive" style="max-height: 25rem;">
                    <table class="table table-hover w-100" id="pengguna-table">
                        <tbody id="pengguna-list-container" class="align-middle"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-center mb-3">TLD Kontrol</h5>
                <div class="overflow-auto flex-grow-1" style="max-height: 25rem;">
                    <div id="tld-kontrol-content" class="row g-3 px-2"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card shadow-sm border-0 mb-2">
            <div class="card-body row">
                <div class="col-12 text-end">
                    <button class="btn btn-outline-primary" onclick="buatPermohonan(this)">Buat Permohonan Evaluasi</button>
                </div>
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
