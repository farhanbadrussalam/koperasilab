@extends('report.template.main')
@section('style')
    @include('report.template.style-kwitansi')
@endsection

@section('header')
    @include('report.template.header')
@endsection

@section('content')
    <div class="title">
        <h2>KWITANSI</h2>
    </div>
    <br><br>
    @php
        $subJumlah = 0;

        if ($data->diskon) {
            foreach ($data->diskon as $item) {
                $item->jumDiskon = $data->permohonan->total_harga * ($item->diskon / 100);
                $subJumlah += $item->jumDiskon;
            }
        }

        $jumAfterDiskon = $data->permohonan->total_harga - $subJumlah;

        $jumPph = $data->pph ? $jumAfterDiskon * ($data->pph / 100) : 0;
        $jumAfterPph = $jumAfterDiskon - $jumPph;
        $jumPpn = $data->ppn ? $jumAfterPph * ($data->ppn / 100) : 0;
    @endphp
    <table class="table-kwitansi">
        <tr>
            <td colspan="3">
                <b>No.</b>
                <span class="border-bottom text-secondary">{{ $data->no_invoice }}</span>
            </td>
            <td width="1%" class="text-secondary">
                LPH
            </td>
        </tr>
        <tr>
            <th width="30%">Telah Terima dari</th>
            <td width="1%">:</td>
            <td class="border-bottom text-secondary" colspan="2">{{ $data->permohonan->pelanggan->perusahaan->nama_perusahaan }}</td>
        </tr>
        <tr>
            <th width="30%">Uang Sejumlah</th>
            <td width="1%">:</td>
            <td class="border-bottom text-secondary" colspan="2">{{ angkaKeHuruf($jumAfterPph + $jumPpn) }} rupiah</td>
        </tr>
        <tr>
            <th width="30%">Untuk Pembayaran</th>
            <td width="1%">:</td>
            <td class="border-bottom text-secondary" colspan="2">{{ $data->permohonan->jenis_layanan->name }} {{ $data->permohonan->layanan_jasa->nama_layanan }} {{ $data->permohonan->jenisTld->name }}</td>
        </tr>
        <tr>
            <td width="30%">&nbsp;</td>
            <td width="1%">&nbsp;</td>
            <td class="border-bottom text-secondary" colspan="2">
                <span style="float: left;">
                    {{ $data->permohonan->jumlah_pengguna + $data->permohonan->jumlah_kontrol }} Unit
                    {{ $data->permohonan->jenisTld->name }} x {{ count($data->permohonan->periode_pemakaian) }} Periode x
                    {{ formatCurrency($data->permohonan->harga_layanan) }}
                </span>
                <span style="float: right;">{{ formatCurrency($data->permohonan->total_harga) }},-</span>
            </td>
        </tr>
        @foreach ($data->diskon as $item)
        <tr>
            <td width="30%">&nbsp;</td>
            <td width="1%">&nbsp;</td>
            <td class="border-bottom text-secondary" colspan="2">
                <span style="float: left;">{{ $item->name }} {{ $item->diskon }}%</span>
                <span style="float: right;">( {{ formatCurrency($item->jumDiskon) }},- )</span>
            </td>
        </tr>
        @endforeach
        @if ($data->pph)
            <tr>
                <td width="30%">&nbsp;</td>
                <td width="1%">&nbsp;</td>
                <td class="border-bottom text-secondary" colspan="2">
                    <span style="float: left;">PPH {{ $data->pph }}%</span>
                    <span style="float: right;">( {{ formatCurrency($jumPph) }},- )</span>
                </td>
            </tr>
        @endif
        @if ($data->ppn)
            <tr>
                <td width="30%">&nbsp;</td>
                <td width="1%">&nbsp;</td>
                <td class="border-bottom text-secondary" colspan="2">
                    <span style="float: left;">PPN</span>
                    <span style="float: right;">{{ formatCurrency($jumPpn) }},-</span>
                </td>
            </tr>
        @endif
        <tr>
            <td width="30%">&nbsp;</td>
            <td width="1%">&nbsp;</td>
            <td class="border-bottom text-secondary" colspan="2">
                <span style="float: left;">Pemakaian Bulan {{ $periode_start }} s.d. {{ $periode_end }}</span>
            </td>
        </tr>
        @if(isset($data->permohonan->kontrak))
        <tr>
            <td width="30%">&nbsp;</td>
            <td width="1%">&nbsp;</td>
            <td class="border-bottom text-secondary" colspan="2">
                <span style="float: left;">Surat Perjanjian No. {{ $data->permohonan->kontrak->no_kontrak }}</span>
            </td>
        </tr>
        @endif
    </table>

    <table style="width: 100%;margin-top: 50px;">
        <tr>
            <td width="50%" class="align-top text-center">
                <h2 class="border px-5 text-secondary" style="width: 90%;"><b>{{ formatCurrency($jumAfterPph + $jumPpn) }},-</b></h2>
            </td>
            <td width="50%" class="text-center">
                <div>
                    <div>Tangerang Selatan, {{ convert_date($date, 4) }}</div>
                    <div class="p-2">
                        @if ($data->ttd)
                        <div class="img-stempel">
                            <img src="{{ $stempel }}" class="img-fluid" alt="Stempel-Lab">
                        </div>
                        @endif
                        <img src="{{ $data->ttd ? $data->ttd : $ttd_default }}" alt="TTD_keuangan">
                    </div>
                    <div>{{ $data->usersig->name }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 60px;font-size: 13px;">
        Catatan : Materai Rp. 10.000,- sudah ditempelkan di invoice sehingga kwitansi tidak memerlukan materai.
    </div>
@endsection
