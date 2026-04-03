@php
    $css = file_get_contents(public_path('css/pdf.css'));
@endphp

@extends('report.template.main')
@section('style')
    <style>
        {!! $css !!}
        @page {
        margin: 10px; /* top right bottom left — tambahkan bottom utk ruang footer */
        }
    </style>
@endsection

@section('content')

@php
    // membagi $data menjadi 6 bagian
    $arrTmp = array();
    foreach ($data as $item) {
        $rangeDate = range_date($periode->start_date, $periode->end_date, 1);

        $tld = array(
            'pengguna' => null,
            'periode' => $rangeDate['start'] . ' - ' . $rangeDate['end'],
        );

        if($item->jenis == 'pengguna') {
            $tld['pengguna'] = $item->entitas;
            array_push($arrTmp, $tld);
        } else {
            array_unshift($arrTmp, $tld);
        }
    }
    $chunks = array_chunk($arrTmp, 6);
@endphp
<div class="d-table">
    @foreach ($chunks as $row)
    <div class="table-row">
        @foreach ($row as $key => $item)
        @if($penyelia->permohonan->kontrak->jenis_tld === 2)
        <div class="border text-center table-cell" style="padding: 2px;height: 100px; width: 200px; position: relative;">
            <div class="lh-20">
                <div>{{ $penyelia->permohonan->pelanggan->perusahaan->kode_perusahaan }}-{{ $item['pengguna'] ? $item['pengguna']->kode_lencana : ($key > 1 ? 'C'.$key : 'C') }}</div>
                <div class="fs-1">{{ $item['pengguna'] ? $item['pengguna']->name : 'Kontrol' }}</div>
                <div class="fs-1">{{ $item['periode'] }}</div>
            </div>
        </div>
        @else
        <div class="border text-center table-cell" style="padding: 5px;height: 220px; width: 100px; position: relative;">
            <div class="lh-16">
                <div>{{ $penyelia->permohonan->pelanggan->perusahaan->kode_perusahaan }}-{{ $item['pengguna'] ? $item['pengguna']->kode_lencana : ($key > 1 ? 'C'.$key : 'C') }}</div>
                <div class="fs-1">{{ $item['pengguna'] ? $item['pengguna']->name : 'Kontrol' }}</div>
                <div class="fs-1">{{ $item['periode'] }}</div>
                <div>{{ substr($penyelia->permohonan->kontrak->no_kontrak, 0, 1) }}</div>
            </div>
            <div style="margin-top: auto; transform: rotate(180deg);position: absolute; bottom: 0;left: 28%;">belakang</div>
        </div>
        @endif
        @endforeach
    </div>
    @endforeach
</div>
@endsection
