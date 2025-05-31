@extends('report.template.main')
@section('style')
    @include('report.template.style-label')
@endsection

@section('content')

@php
    // membagi $data menjadi 6 bagian
    $arrTmp = array();
    foreach ($data as $item) {
        foreach ($item->tld as $tld) {
            $tld->pengguna = $item->pengguna;
            $tld->divisi = $item->divisi;
            $tld->count = $item->count;
            array_push($arrTmp, $tld);
        };
    }
    $chunks = array_chunk($arrTmp, 6);
@endphp
<div class="d-table">
    @foreach ($chunks as $row)
    <div class="table-row">
        @foreach ($row as $key => $item)
        <div class="border center table-cell" style="padding: 5px;height: 220px; width: 100px; position: relative;">
            <div class="lh-5">
                <div>{{ $penyelia->permohonan->pelanggan->perusahaan->kode_perusahaan }}-{{ $item->pengguna ? $item->pengguna->kode_lencana : ($item->count > 1 ? 'C'.$key : 'C') }}</div>
                <div class="fs-1">{{ $item->pengguna ? $item->pengguna->name : 'Kontrol' }}</div>
                <div class="fs-1">{{ convert_date($periode->start_date, 7) }} - {{ convert_date($periode->end_date, 7) }}</div>
                <div>{{ substr($penyelia->permohonan->kontrak->no_kontrak, 0, 1) }}</div>
            </div>
            <div style="margin-top: auto; transform: rotate(180deg);position: absolute; bottom: 0;left: 28%;">belakang</div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endsection
