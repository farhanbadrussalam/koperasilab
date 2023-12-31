@extends('report.template.main')

@section('content')
    <div class="title">
        <h2>KWITANSI</h2>
    </div>
    <br><br>
    <table class="table-kwitansi">
        <tr>
            <td colspan="3">
                <b>No.</b>
                <span class="border-bottom">{{ strPad(decryptor($kip->kip_hash)) }}/KW-MZR/JKRL/III/{{ $date->year }}</span>
            </td>
            <td width="1%">
                LPH
            </td>
        </tr>
        <tr>
            <th width="30%">Telah Terima dari</th>
            <td width="1%">:</td>
            <td class="border-bottom" colspan="2">{{ $kip->permohonan->user->perusahaan->name }}</td>
        </tr>
        <tr>
            <th width="30%">Uang Sejumlah</th>
            <td width="1%">:</td>
            <td class="border-bottom" colspan="2">{{ angkaKeHuruf($kip->harga + $kip->pajak) }} rupiah</td>
        </tr>
        <tr>
            <th width="30%">Untuk Pembayaran</th>
            <td width="1%">:</td>
            <td class="border-bottom" colspan="2">{{ $kip->permohonan->jenis_layanan }}</td>
        </tr>
        <tr>
            <td width="30%">&nbsp;</td>
            <td width="1%">&nbsp;</td>
            <td class="border-bottom" colspan="2">
                <span style="float: left;">{{ $kip->permohonan->jumlah }} Unit</span>
                <span style="float: right;">{{ formatCurrency($kip->harga) }},-</span>
            </td>
        </tr>
        <tr>
            <td width="30%">&nbsp;</td>
            <td width="1%">&nbsp;</td>
            <td class="border-bottom" colspan="2">
                <span style="float: left;">PPN 11%</span>
                <span style="float: right;">{{ formatCurrency($kip->pajak) }},-</span>
            </td>
        </tr>
    </table>

    <table style="width: 100%;margin-top: 50px;">
        <tr>
            <td width="50%" class="align-top text-center">
                <h2 class="border px-5" style="width: 90%;"><b>{{ formatCurrency($kip->harga + $kip->pajak) }},-</b></h2>
            </td>
            <td width="50%" class="text-center">
                <div>
                    <div>Tangerang Selatan, {{ convert_date($date, 3) }}</div>
                    <div class="p-2"><img src="{{ $kip->ttd_1 }}" alt="TTD_keuangan" class="img-fluid"></div>
                    <div>{{ $kip->user->name }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 60px;font-size: 13px;">
        Catatan : Materai Rp. 10.000,- sudah ditempelkan di invoice sehingga kwitansi tidak memerlukan materai.
    </div>
@endsection
