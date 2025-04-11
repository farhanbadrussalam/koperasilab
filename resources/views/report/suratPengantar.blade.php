@extends('report.template.main')
@section('style')
    @include('report.template.style-suratPengantar')
@endsection

@php
    $jumlahTld = $data->jumlah_pengguna + $data->jumlah_kontrol;
    $nomer = $data->periode[0]->nomer_surpeng;
    $layanan = $data->layanan_jasa->nama_layanan;
    $jenisTld = $data->jenisTld->name;
    $kontrak = $data->no_kontrak ?? '-';
    $startDate = $data->periode[0]->start_date;
    $endDate = $data->periode[0]->end_date;
    $created = $data->periode[0]->created_surpeng_at;

    $periode = "bulan ". convert_date($startDate, 6) ." s.d ". convert_date($endDate, 6) ." periode ".$data->periode[0]->periode;
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
            beserta <span class="fw-bold">{{ $data->jumlah_kontrol }} buah TLD Kontrol</span> untuk pemakaian <span class="fw-bold">{{ $data->periode ? $periode : 'Zero cek' }},</span> Kontrak No. ({{ $kontrak }}) daftar nama terlampir.
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
        @php
            $count = 1;
            $countKontrol = 0;
        @endphp
        @foreach ($data->rincian_list_tld as $value)
            @if($value->pengguna)
                <tr>
                    <td class="text-center">{{ $count++ }}.</td>
                    <td style="padding-left: 5px">{{ $value->pengguna->nama }}</td>
                    <td style="padding-left: 5px" class="fw-bold">{{ $value->keterangan ?? '' }}</td>
                </tr>
            @else
                @php $countKontrol++; @endphp
            @endif
        @endforeach

        <tr>
            <td class="text-center">{{ $count }}.</td>
            <td style="padding-left: 5px">TLD Kontrol</td>
            <td style="padding-left: 5px" class="fw-bold">{{ $countKontrol }} Buah</td>
        </tr>
    </table>
@endsection