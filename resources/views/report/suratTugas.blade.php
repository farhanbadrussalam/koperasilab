@extends('report.template.main')

@section('content')
    <div class="title">
        <h2>SURAT TUGAS UJI</h2>
        <span>No.{{ strPad(decryptor($data->permohonan->permohonan_hash)) }}/NL-{{ $data->permohonan->layananjasa->satuanKerja->alias }}/I/{{ $date }}</span>
    </div>

    <div class="content">
        <p>
            Yang bertandatangan di bawah ini, Manajer/Penyelia Unit {{ $data->permohonan->layananjasa->satuanKerja->name }}, menugaskan
            kepada yang namanya tersebut di bawah ini untuk melaksanakan pengujian <b>{{ $data->permohonan->jenis_layanan }}</b>
             <b>{{ $data->permohonan->user->perusahaan->name }}</b> sejumlah <b>{{ $data->permohonan->jumlah }}</b> pada tanggal {{ convert_date($data->date_mulai, 4) }} sampai
            dengan {{ convert_date($data->date_selesai, 4) }}.
        </p>

        <table class="table-content">
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
                @foreach ($data->petugas as $value)
                    <tr>
                        <td style="text-align: center;">{{ $no }}.</td>
                        <td>{{ $value->petugas_id }}</td>
                        <td>
                            {{ $value->jobs }}
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
            <div>Tanggal : {{ convert_date($data->created_at, 4) }}</div>
            <div>Manajer/Penyelia Unit {{ $data->permohonan->layananjasa->satuanKerja->name }}</div>
            <img src="{{ $data->ttd_1 }}" alt="ttd" srcset="ttd">
            <div style="margin-top: 10px;">( {{ $data->signature_1->name }} )</div>
        </div>
    </div>
@endsection
