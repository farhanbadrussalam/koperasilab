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
            {{ $data->permohonan->layanan_jasa->nama_layanan }} {{ $data->permohonan->jenisTld->name }} periode pemakaian bulan Maret 2023 s.d. Februari 2024, sesuai dengan Surat
            Perjanjian No. {{ $data->permohonan->kontrak?->no_kontrak ?? '-' }}, dengan ini kami mohon penyelesaian pembayaran dengan rincian sebagai berikut
            :</p>
    </div>

    <table class="table">
        <tr>
            <td>15 Unit BARC x 4 Periode x Rp. 125.000</td>
            <td>Rp. 7.500.000</td>
        </tr>
        <tr>
            <td>Discount 2.5 %</td>
            <td>( Rp. 187.500 )</td>
        </tr>
        <tr>
            <td>Sub Jumlah</td>
            <td>Rp. 7.312.500</td>
        </tr>
        <tr>
            <td>PPN</td>
            <td>Rp. 804.375</td>
        </tr>
        <tr>
            <td>Jumlah</td>
            <td>Rp. 8.116.875</td>
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
            <td width="50%" style="text-align: center">
                <div>
                    <div class="p-2"><img src="{{ $data->ttd ? $data->ttd : $ttd_default }}" alt="TTD_keuangan" class="img-fluid"></div>
                    <div>{{ $data->usersig ? $data->usersig->name : '................................' }}</div>
                </div>
            </td>
        </tr>
    </table>
@endsection
