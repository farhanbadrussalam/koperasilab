@extends('report.template.main')
@section('style')
    @include('report.template.style-invoice')
@endsection

@section('content')
    <table class="table">
        <tr>
            <td style="width: 60%;">
                <table>
                    <tr>
                        <td>Nomor</td>
                        <td>:</td>
                        <td class="text-left">{{ $data->permohonan->kontrak?->no_kontrak ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Lampiran</td>
                        <td>:</td>
                        <td class="text-left">Faktur Pajak</td>
                    </tr>
                    <tr>
                        <td>Perihal</td>
                        <td>:</td>
                        <td class="text-left">Invoice {{ $data->permohonan->jenis_layanan_parent->name }}
                            {{ $data->permohonan->layanan_jasa->nama_layanan }} {{ $data->permohonan->jenisTld->name }}</td>
                    </tr>
                </table>
            </td>
            <td>Tangerang Selatan, {{ convert_date($date, 2) }}</td>
        </tr>
    </table>
    <div class="header" style="line-height: 1.3em">
        <div>Kepada Yth.,</div>
        <div>{{ $data->permohonan->pelanggan->perusahaan->nama_perusahaan }}</div>
        <div>{{ $data->permohonan->pelanggan->perusahaan->alamat[0]->alamat }}</div>
        <div>Kode Pos : {{ $data->permohonan->pelanggan->perusahaan->alamat[0]->kode_pos }}</div>
        <div>Telp : {{ $data->permohonan->pelanggan->telepon }}</div>
    </div>

    <div class="content" style="line-height: 1.3em">
        <p>Sehubungan dengan {{ $data->permohonan->jenis_layanan_parent->name }}
            {{ $data->permohonan->layanan_jasa->nama_layanan }} {{ $data->permohonan->jenisTld->name }} periode pemakaian
            bulan {{ convert_date($periode_start['start_date'], 6) }} s.d.
            {{ convert_date($periode_end['end_date'], 6) }}, sesuai dengan Surat
            Perjanjian No. {{ $data->permohonan->kontrak?->no_kontrak ?? '-' }}, dengan ini kami mohon penyelesaian
            pembayaran dengan rincian sebagai berikut :</p>
    </div>

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

    <table class="table">
        <tr>
            <td>{{ $data->permohonan->jumlah_pengguna + $data->permohonan->jumlah_kontrol }} Unit
                {{ $data->permohonan->jenisTld->name }} x {{ count($data->permohonan->periode_pemakaian) }} Periode x
                {{ formatCurrency($data->permohonan->harga_layanan) }}</td>
            <td>{{ formatCurrency($data->permohonan->total_harga) }}</td>
        </tr>
        @foreach ($data->diskon as $item)
        <tr>
            <td>{{ $item->name }} {{ $item->diskon }}%</td>
            <td>( {{ formatCurrency($item->jumDiskon) }} )</td>
        </tr>
        @endforeach
        <tr>
            <td>Sub Jumlah</td>
            <td>{{ formatCurrency($jumAfterDiskon) }}</td>
        </tr>
        @if ($data->pph)
            <tr>
                <td>PPH {{ $data->pph }}%</td>
                <td>( {{ formatCurrency($jumPph) }} )</td>
            </tr>
        @endif
        @if ($data->ppn)
            <tr>
                <td>PPN</td>
                <td>{{ formatCurrency($jumPpn) }}</td>
            </tr>
        @endif
        <tr>
            <td>Jumlah</td>
            <td>{{ formatCurrency($jumAfterPph + $jumPpn) }}</td>
        </tr>
    </table>

    <p>(Terbilang : <em>Delapan juta seratus enam belas ribu delapan ratus tujuh puluh lima rupiah</em>)</p>

    <div class="note">
        <p>Note : Kwitansi asli dan TLD akan kami kirimkan setelah menerima bukti pembayaran. (Mohon Bukti Potong PPh 23
            dikirimkan kepada kami apabila memotongnya)</p>
    </div>

    <div class="payment-info" style="line-height: 1.3em">
        <p>Pembayaran dapat dilakukan secara Tunai atau melalui Virtual Account Bank Mandiri dengan No : 89029120231337</p>
        <p>Apabila telah melakukan pembayaran, kami mohon bukti transfer dikirim melalui e-mail ke tld@kop-jkrl.co.id dan
            diberi keterangan untuk pembayaran dimaksud berdasarkan invoice/kwitansi tersebut diatas.</p>
        <p>Atas perhatian dan kerjasamanya, diucapkan terima kasih</p>
    </div>

    <div class="footer-invoice" style="margin: 0px;">
        <span>Koperasi Jasa Keselamatan Radiasi dan Lingkungan</span>
    </div>

    <table style="width: 100%;">
        <tr>
            <td width="50%" class="align-top text-center">
                <div class="payment-notice">
                    PEMBAYARAN MAX 30 HARI<br>
                    DARI TANGGAL INVOICE<br>
                    KORESPONDENSI<br>
                    TELP. 021 - 74786334
                </div>
            </td>
            <td width="50%" style="text-align: center;">
                <div class="p-2">
                    @if ($data->ttd)
                    <div class="img-stempel">
                        <img src="{{ $stempel }}" class="img-fluid" alt="Stempel-Lab">
                    </div>
                    @endif
                    <img src="{{ $data->ttd ? $data->ttd : $ttd_default }}" alt="TTD_keuangan">
                </div>
                <div>{{ $data->usersig ? $data->usersig->name : '................................' }}</div>
            </td>
        </tr>
    </table>
@endsection
