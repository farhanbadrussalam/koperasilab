@extends('report.template.main')
@section('style')
    @include('report.template.style-kwitansi')
@endsection

@section('content')
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
    <div class="title my-1"><h2 class="fw-normal">DENGAN</h2></div>
    
@endsection