@extends('report.template.main')
@section('style')
    @include('report.template.style-tandaterima')
@endsection

@section('content')

    <h1 class="center w-100 text-underline">TANDA TERIMA PENGUJIAN/KALIBRASI</h1>
    <div class="w-100 center">
        <label for="">Nomor : </label>
        <span>..............</span>
    </div>
    <br>
    <table class="table">
        <tr>
            <td width="20%">Telah terima dari</td>
            <td width="1%">:</td>
            <td width="75%">.........................</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>.........................</td>
        </tr>
    </table>
    
    <table class="table table-content" border="1">
        <tr>
            <td>Jenis Pengujian/Kalibrasi:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td width="5%">a</td>
            <td>Kebocoran Sumber Radioaktif:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Uji Kesesuaian:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Surveymeter/Pendose:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Analisis Radionuklida:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>NORM/TENORM:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Lain-lain:</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    <p>Jumlah: &nbsp;</p>
    <p>Tanggal Penerimaan: &nbsp; Tanggal Selesai Pengujian: &nbsp;</p>
    <p>Tangerang Selatan, &nbsp;</p>

    <p>Pemohon,</p>
    <p>&nbsp;</p>
    <p>(&nbsp;nama jelas&nbsp;)</p>

    <p>Catatan:</p>
    <ol>
        <li>Dengan penyerahan benda uji / alat ini, pihak pemohon menyetujui pekerjaan pengujian/kalibrasi yang akan
            dilakukan Laboratorium Pengujian BATAN sesuai dengan biaya pengujiannya/kalibrasinya.</li>
        <li>Untuk benda uji / alat yang dikrim melalui jasa ekspedisi, bila saat diterima ada kerusakan bukan menjadi
            tanggung jawab BATAN.</li>
        <li>Pengujian / kalibrasi tidak akan diproses sebelum ada surat permohonan dari pelanggan.</li>
    </ol>
    <p>*) Pilih yang sesuai</p>

    <p>Diisi rangkap 3 (tiga)</p>
    <p>Lembar 1 (Putih) : Pemohon</p>
    <p>Lembar 2 (Biru) : Unit Administrasi</p>
    <p>Lembar 3 (Kuning) : Unit Terkait Teknis</p>

    <p>Halaman 1 dari 1</p>
@endsection
