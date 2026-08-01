<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pekerja Radiasi</title>
    <style>
        @page {
            size: landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .table-data th,
        .table-data td {
            border: 1px solid black;
            padding: 5px;
        }

        .table-data th {
            text-align: center;
            vertical-align: middle;
        }

        .info-table {
            margin-top: 20px;
        }

        .info-table td {
            padding: 2px 5px;
            vertical-align: top;
        }

        .signature-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signature-table td {
            vertical-align: top;
        }

        .keterangan {
            font-size: 10px;
        }

        .img-stempel {
            width: 100px;
            height: 100px;
            position: absolute;
            z-index: 2;
            /* Lapisan depan lebih tinggi */
            margin-left: 3cm;
        }

        .img-fluid {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <h3 class="text-center font-bold">DATA PEKERJA RADIASI</h3>

    <table class="info-table">
        <tr>
            <td class="font-bold">Nama Instansi</td>
            <td class="font-bold">:</td>
            <td>{{ $kontrak->pelanggan->perusahaan->nama_perusahaan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">Alamat</td>
            <td class="font-bold">:</td>
            <td>{{ $kontrak->pelanggan->perusahaan->alamat->first()?->alamat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold"><br>Telepon, No HP</td>
            <td class="font-bold"><br>:</td>
            <td><br>{{ $kontrak->pelanggan->profile->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">Kontak Person</td>
            <td class="font-bold">:</td>
            <td>{{ $kontrak->pelanggan->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%">No.</th>
                <th rowspan="2" style="width: 15%">Nama Pekerja Radiasi<br>(sesuai KTP) *)</th>
                <th rowspan="2" style="width: 10%">Nomor Lencana<br>TLD</th>
                <th rowspan="2" style="width: 12%">Nomor Induk Kependudukan<br>(NIK)</th>
                <th rowspan="2" style="width: 8%">Jenis Kelamin<br>(L/P)</th>
                <th rowspan="2" style="width: 15%">Tempat dan Tanggal<br>Lahir</th>
                <th rowspan="2" style="width: 12%">Divisi dan<br>Bagian</th>
                <th colspan="2">Sumber Radioaktif</th>
            </tr>
            <tr>
                <th style="width: 12.5%">Zat Radioaktif **)</th>
                <th style="width: 12.5%">X-Ray ***)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $hasData = false;
            @endphp
            @if (isset($list_tld) && count($list_tld) > 0)
                @foreach ($list_tld as $tld)
                    @if ($tld->jenis == 'pengguna' && $tld->entitas)
                        @php
                            $hasData = true;
                            $zatRad = [];
                            $xRay = [];
                            if ($tld->entitas->radiasi) {
                                foreach ($tld->entitas->radiasi as $rad) {
                                    if (
                                        stripos($rad->nama_radiasi, 'x-ray') !== false ||
                                        stripos($rad->nama_radiasi, 'xray') !== false
                                    ) {
                                        $xRay[] = $rad->nama_radiasi;
                                    } else {
                                        $zatRad[] = $rad->nama_radiasi;
                                    }
                                }
                            }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $no++ }}.</td>
                            <td>{{ $tld->entitas->name ?? '-' }}</td>
                            <td class="text-center">{{ $tld->tld_awal->no_seri_tld ?? '-' }}</td>
                            <td class="text-center">{{ $tld->entitas->nik ?? '-' }}</td>
                            <td class="text-center">
                                @if (strtolower($tld->entitas->jenis_kelamin) == 'laki-laki' || strtolower($tld->entitas->jenis_kelamin) == 'l')
                                    L
                                @elseif(strtolower($tld->entitas->jenis_kelamin) == 'perempuan' || strtolower($tld->entitas->jenis_kelamin) == 'p')
                                    P
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                {{ $tld->entitas->tempat_lahir ?? '-' }},
                                {{ $tld->entitas->tanggal_lahir ? \Carbon\Carbon::parse($tld->entitas->tanggal_lahir)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="text-center">{{ $tld->divisiSelected->name ?? ($tld->entitas->divisi->name ?? ($tld->entitas->divisi->nama_divisi ?? '-')) }}</td>
                            <td class="text-center">{{ count($zatRad) > 0 ? implode(', ', $zatRad) : '-' }}</td>
                            <td class="text-center">{{ count($xRay) > 0 ? implode(', ', $xRay) : '-' }}</td>
                        </tr>
                    @endif
                @endforeach
            @endif

            @if (!$hasData)
                @for ($i = 1; $i <= 3; $i++)
                    <tr>
                        <td class="text-center">{{ $i }}.</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            @endif
            <tr>
                <td class="text-center">Dst.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 65%">
                <div class="keterangan font-bold">Keterangan:</div>
                <div class="keterangan">*) Melampirkan copy KTP yang berlaku</div>
                <div class="keterangan">**) Cs-137, Co-60, Ba-133, Am-241, Ni-63, Kr-85, Sr/Y-90, Pm-147, Tenorm</div>
            </td>
            <td style="width: 35%; text-align: center;">
                <br>
                {{ $lokasi }}, {{ $date }}
                <br><br><br>
                {!! $signature !!}
                ({{ $nama_signature }})
            </td>
        </tr>
    </table>
</body>

</html>
