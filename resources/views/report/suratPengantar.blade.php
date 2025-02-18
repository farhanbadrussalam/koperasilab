@extends('report.template.main')
@section('style')
    @include('report.template.style-suratPengantar')
@endsection

@php
    $jumlahTld = $data->jumlah_pengguna + $data->jumlah_kontrol;
    $nomer = $data->dokumen[0]->nomer;
    $layanan = $data->layanan_jasa->nama_layanan;
    $jenisTld = $data->jenisTld->name;
    $kontrak = $data->kontrak?->no_kontrak ?? '-';
    $startDate = $data->lhu->start_date;
    $endDate = $data->lhu->end_date;
    $created = $data->dokumen[0]->created_at;
@endphp

@section('content')
    <table class="table-header fs-3">
        <tr>
            <td width="1%">Nomor</td>
            <td width="50%">: {{ $nomer }}</td>

            <td class="text-end">{{ convert_date($created, 2) }}</td>
        </tr>
        <tr>
            <td>Lamp</td>
            <td>: <span class="fw-bold">{{ $jumlahTld }} Buah {{ $layanan }}</span></td>
        </tr>
        <tr>
            <td>Hal</td>
            <td>: {{ $layanan }} {{ $jenisTld }}</td>
        </tr>
    </table>

    <div class="fs-3 lh-2">
        <div>Kepada Yth.</div>
        <div>Petugas Proteksi Radiasi</div>
        <div class="fw-bold">{{ $data->pelanggan->perusahaan->nama_perusahaan }}</div>
        <div class="text-wrap w-40">
            {{ $data->pelanggan->perusahaan->alamat[0]->alamat }}, {{ $data->pelanggan->perusahaan->alamat[0]->kode_pos }}
        </div>
        <div>Telp. {{ $data->pelanggan->telepon }}</div>
        <div>Attn. {{ $data->pelanggan->name }}</div>
    </div>

    <div class="fs-3 lh-2" style="margin-top: 15px;">
        <p class="text-indent">
            Dengan ini kami kirimkan <span class="fw-bold">sebanyak {{ $data->jumlah_pengguna }} buah {{ $layanan }} {{ $jenisTld }} monitor</span>
            beserta <span class="fw-bold">{{ $data->jumlah_kontrol }} buah TLD Kontrol</span> untuk pemakaian <span class="fw-bold">bulan {{ convert_date($startDate, 6) }} s.d {{ convert_date($endDate, 6) }} periode
            Terakhir,</span> Kontrak No. ({{ $kontrak }}) daftar nama terlampir.
        </p>
        <p class="text-indent">
            Demikian, atas perhatian dan kerjasamanya kami ucapkan terima kasih.
        </p>
    </div>

    <table class="table-ttd fs-3" style="margin-top: 40px;">
        <tr>
            <td width="50%">
                &nbsp;
            </td>
            <td width="50%">
                <div class="text-center d-flex">
                    <div class="flex-1">NuklindoLab</div>
                    <div>Koperasi Jasa Keselamatan</div>
                    <div>Radiasi dan Lingkungan</div>
                    <img class="ttd-image" src="{{ $data->ttd ? $data->ttd : $ttd_default }}" alt="ttd" srcset="ttd">
                    <div class="flex-1 text-underline">( {{ $data->signature ? $data->signature->name : '................................' }} )</div>
                    <div>Manajer Unit Administrasi</div>
                </div>
            </td>
        </tr>
    </table>
    <div style="page-break-before: always"></div>
    <div class="fs-3">Lampiran Surat Nomor : {{ $nomer }}</div>
    <div class="fs-3 fw-bold" style="margin-top: 15px;">
        Nama Instansi : {{ $data->pelanggan->perusahaan->nama_perusahaan }}
    </div>
    <div class="w-100 text-center text-underline fw-bold fs-4" style="margin-top: 15px">Daftar Nama Pemakai {{ $layanan }} {{ $jenisTld }} : </div>
    <table class="table-content fs-3 w-100 table" style="margin-top: 15px;" border="1">
        <tr>
            <th width="10%">No</th>
            <th width="40%">Nama Pemakai TLD</th>
            <th width="40%">Keterangan</th>
        </tr>
        @foreach ($data->pengguna as $key => $value)
            <tr>
                <td class="text-center">{{ $key + 1 }}.</td>
                <td style="padding-left: 5px">{{ $value->nama }}</td>
                <td style="padding-left: 5px" class="fw-bold">{{ $value->keterangan ?? 'Baru' }}</td>
            </tr>
        @endforeach

        @if(!empty($data->list_tld))
            <tr>
                <td class="text-center">{{ count($data->pengguna) + 1 }}.</td>
                <td style="padding-left: 5px">TLD Kontrol</td>
                <td style="padding-left: 5px" class="fw-bold">{{ count($data->list_tld) }} Buah</td>
            </tr>
        @endif
    </table>
@endsection