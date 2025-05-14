@extends('report.template.main')
@section('style')
    @include('report.template.style-kwitansi')
@endsection

@section('header')
    @include('report.template.header')
@endsection

@section('content')
    <div class="title">
        <h2>SURAT TUGAS UJI</h2>
        <span>No.{{ $data->dokumen[0]->nomer }}</span>
    </div>

    <div class="content">
        <p>
            Yang bertandatangan di bawah ini, Manajer/Penyelia Unit {{ $data->lhu->createBy->satuanKerja->name }}, menugaskan
            kepada yang namanya tersebut di bawah ini untuk melaksanakan pengujian <b>{{ $data->jenis_layanan->name }}</b>
             <b>{{ $data->pelanggan->perusahaan->name }}</b> sejumlah <b>{{ $data->jumlah }}</b> pada tanggal {{ convert_date($data->lhu->start_date, 2) }} sampai
            dengan {{ convert_date($data->lhu->end_date, 2) }}.
        </p>

        <table class="table table-content" border="1">
            <thead style="text-align: center;">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Tugas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $no = 1;
                ?>
                @foreach ($data->lhu->petugas as $value)
                    <tr>
                        <td style="text-align: center;">{{ $no }}.</td>
                        <td>{{ $value->user->name }}</td>
                        <td>
                            {{ $value->jobs->jobs->name }}
                        </td>
                    </tr>
                    <?= $no++; ?>
                @endforeach
            </tbody>
        </table>

        <p>
            Semua pelaksana tugas diminta untuk menyelesaikan pengujian sesuai dengan tugasnya
            masing-masing.
        </p>

        <div style="margin-top: 50px;">
            <div>Tanggal : {{ convert_date($data->lhu->created_at, 2) }}</div>
            <div>Manajer/Penyelia Unit {{ $data->lhu->createBy->satuanKerja->name }}</div>
            <img src="{{ $data->lhu->ttd ? $data->lhu->ttd : $ttd_default }}" alt="ttd" srcset="ttd">
            <div style="margin-top: 10px;">( {{ $data->lhu->usersig ? $data->lhu->usersig->name : '................................' }} )</div>
        </div>
    </div>
@endsection
