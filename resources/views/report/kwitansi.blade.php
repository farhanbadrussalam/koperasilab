@extends('report.template.main')
@section('style')
    @include('report.template.style')
@endsection

@section('content')
    <div class="title">
        <h2>KWITANSI</h2>
    </div>
    <br><br>
    <table class="table-kwitansi">
        <tr>
            <td colspan="3">
                <b>No.</b>
                <span class="border-bottom">{{ strPad(decryptor($data->keuangan_hash)) }}/KW-MZR/JKRL/III/{{ $date->year }}</span>
            </td>
            <td width="1%">
                LPH
            </td>
        </tr>
        <tr>
            <th width="30%">Telah Terima dari</th>
            <td width="1%">:</td>
            <td class="border-bottom" colspan="2">{{ $data->permohonan->pelanggan->perusahaan->nama_perusahaan }}</td>
        </tr>
        <tr>
            <th width="30%">Uang Sejumlah</th>
            <td width="1%">:</td>
            <td class="border-bottom" colspan="2">{{ angkaKeHuruf(300000) }} rupiah</td>
        </tr>
        <tr>
            <th width="30%">Untuk Pembayaran</th>
            <td width="1%">:</td>
            <td class="border-bottom" colspan="2">{{ $data->permohonan->jenis_layanan->name }}</td>
        </tr>
        <tr>
            <td width="30%">&nbsp;</td>
            <td width="1%">&nbsp;</td>
            <td class="border-bottom" colspan="2">
                <span style="float: left;">{{ $data->permohonan->jumlah_pengguna + $data->permohonan->jumlah_kontrol }} Unit</span>
                <span style="float: right;">{{ formatCurrency($data->permohonan->total_harga) }},-</span>
            </td>
        </tr>
        <tr>
            <td width="30%">&nbsp;</td>
            <td width="1%">&nbsp;</td>
            <td class="border-bottom" colspan="2">
                <span style="float: left;">PPN 11%</span>
                <span style="float: right;">{{ formatCurrency(30000000) }},-</span>
            </td>
        </tr>
    </table>

    <table style="width: 100%;margin-top: 50px;">
        <tr>
            <td width="50%" class="align-top text-center">
                <h2 class="border px-5" style="width: 90%;"><b>{{ formatCurrency(5000000) }},-</b></h2>
            </td>
            <td width="50%" class="text-center">
                <div>
                    <div>Tangerang Selatan, {{ convert_date($date, 4) }}</div>
                    <div class="p-2"><img src="{{ $data->ttd }}" alt="TTD_keuangan" class="img-fluid"></div>
                    <div>{{ $data->usersig->name }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 60px;font-size: 13px;">
        Catatan : Materai Rp. 10.000,- sudah ditempelkan di invoice sehingga kwitansi tidak memerlukan materai.
    </div>
@endsection
