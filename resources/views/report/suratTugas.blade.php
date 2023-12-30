@extends('report.template.main')

@section('content')
    <div class="title">
        <h2>SURAT TUGAS UJI</h2>
        <span>No.{{ decryptor($permohonan->permohonan_hash) }}/NL-{{ $permohonan->layananjasa->satuanKerja->alias }}/I/{{ $date }}</span>
    </div>

    <div class="content">
        <p>
            Yang bertandatangan di bawah ini, Manajer/Penyelia Unit {{ $permohonan->layananjasa->satuanKerja->name }}, menugaskan
            kepada yang namanya tersebut di bawah ini untuk melaksanakan pengujian <b>{{ $permohonan->jenis_layanan }}</b>
             <b>{{ $permohonan->user->perusahaan->name }}</b> sejumlah <b>{{ $permohonan->jumlah }}</b> pada tanggal {{ convert_date($permohonan->jadwal->date_mulai, 3) }} sampai
            dengan {{ convert_date($permohonan->jadwal->date_selesai, 3) }}.
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
                @foreach ($permohonan->petugas as $value)
                    <tr>
                        <td style="text-align: center;">{{ $no }}.</td>
                        <td>{{ $value->petugas->name }}</td>
                        <td>
                            @foreach ($value->otorisasi as $otorisasi)
                                {{ stringSplit($otorisasi->name, 'Otorisasi-') }}
                            @endforeach
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
            <div>Tanggal : {{ convert_date($permohonan->jadwal->date_mulai, 3) }}</div>
            <div>Manajer/Penyelia Unit {{ $permohonan->layananjasa->satuanKerja->name }}</div>

            <div style="margin-top: 80px;">( {{ $permohonan->layananjasa->manager->name }} )</div>
        </div>
    </div>
@endsection
