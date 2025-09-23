@extends('report.template.main')
@section('style')
    @include('report.template.style-perjanjian')
@endsection

@section('header')
    @include('report.template.header')
@endsection

@section('content')
<div class="fs-1">
    <div class="title lh-2">
        <h2>
            PERJANJIAN KERJASAMA<br>
            TENTANG<br>
            {{ strtoupper($data->jenis_layanan->name) }} {{ strtoupper($data->layanan_jasa->nama_layanan) }} {{ strtoupper($data->jenisTld->name) }}
        </h2>
    </div>
    <div class="title my-2">
        <h2 class="fw-normal">Nomor : <span class="text-secondary">{{ $data->no_kontrak }}</span></h2>
    </div>
    <div class="title my-1"><h2 class="fw-normal">ANTARA</h2></div>
    <div class="center lh-2">
        <h2>
            KOPERASI JASA KESELAMATAN RADIASI DAN LINGKUNGAN<br>
            (NUKLINDOLAB)<br>
            Plaza Ciputat Mas Blok B Kav P-Q<br>
            Jl.  Ir. H. Juanda No. 5A, Ciputat Timur - Tangerang Selatan<br>
            Telp. 021 - 74786334<br>
            Email : cs@kop-jkrl.co.id, tld@kop-jkrl.co.id dan analisis@kop-jkrl.co.id
        </h2>
    </div>
    <div class="title my-2"><h2 class="fw-normal">DENGAN</h2></div>
    <table class="w-100 my-3 fs-3">
        <tr>
            <td width="25%">Instansi/Perusahaan</td>
            <td>: <span class="text-secondary">{{ $data->pelanggan->perusahaan->nama_perusahaan }}</span></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: <span class="text-secondary">{{ $data->pelanggan->perusahaan->alamat[0]->alamat }}, {{ $data->pelanggan->perusahaan->alamat[0]->kode_pos }}</span></td>
        </tr>
        <tr>
            <td colspan="2">Kode Pos : <span class="text-secondary">{{ $data->pelanggan->perusahaan->alamat[0]->kode_pos }}</span> Telp. <span class="text-secondary">{{ $data->pelanggan->telepon }}</span> </td>
        </tr>
    </table>
    <div class="my-2 fs-3">
        Pada hari ini : <span class="text-secondary">{{ convert_date($data->created_at, 8) }}</span>
        Tanggal <span class="text-secondary">{{ convert_date($data->created_at, 9) }}</span>
        Tahun <span class="text-secondary">{{ convert_date($data->created_at, 10) }}</span>
        Yang bertanda tangan dibawah ini :
    </div>

    @php
        $contentRadiasi = '';
        $count = 0;
        foreach ($radiasi as $key => $value) {
            // kalo terakhir koma tidak perlu
            if($count == count($radiasi) - 1) {
                $contentRadiasi .= $value->nama_radiasi;
            } else {
                $contentRadiasi .= $value->nama_radiasi . ', ';
            }

            $count++;
        }
    @endphp

    <ol class="fs-3 list-roman lh-5">
        <li>
            Nama : <span class="text-secondary">{{ $data->pelanggan->name }}</span>
            Jabatan : @if($data->pelanggan->jabatan != null)<span class="text-secondary">{{ $data->pelanggan->jabatan }}</span>@else ...................@endif
            <b>(PIMPINAN)</b> Bertindak atas nama Instansi/Perusahaan tersebut diatas,
            Selanjutnya disebut <b>PIHAK KESATU.</b><br>
            PIHAK KESATU menggunakan sumber <span class="text-secondary">{{ $contentRadiasi }}</span> dengan jumlah <span class="text-secondary">{{ $data->jumlah_pengguna + $data->jumlah_kontrol }}</span> unit
        </li>
        <li>
            Nama : <b>Dr. Eko Pudjadi, M.Sc</b> bertindak selaku General Manager Koperasi Jasa Keselamatan Radiasi dan Lingkungan,
            selanjutnya disebut <b>PIHAK KEDUA.</b>
        </li>
    </ol>

    <div class="my-2 fs-3 lh-5">
        Berdasarkan hal-hal tersebut diatas PIHAK KESATU dan PIHAK KEDUA sepakat mengadakan Kerjasama Jasa Evaluasi/Pembacaan
        TLD Badge dengan Ketentuan sebagai berikut:
    </div>
</div>
@include('report.template.footer')
@endsection
