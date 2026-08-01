@php
    $css = file_get_contents(public_path('css/pdf.css'));
@endphp

@extends('report.template.main')
@section('style')
    <style>
        {!! $css !!} @page {
            margin: 0.5cm;
            /* top right bottom left — tambahkan bottom utk ruang footer */
        }
    </style>
@endsection

@section('content')
    @php
        // membagi $data menjadi 6 bagian
        $arrTmp = [];
        foreach ($data as $item) {
            $rangeDate = range_date($periode['start_date'], $periode['end_date'], 1);

            $tld = [
                'pengguna' => null,
                'periode' => $rangeDate['start'] . ' - ' . $rangeDate['end'],
            ];

            if ($item->jenis == 'pengguna') {
                $tld['pengguna'] = $item->entitas;
                $tld['detail'] = $item;
                array_push($arrTmp, $tld);
            } else {
                $tld['detail'] = $item;
                array_unshift($arrTmp, $tld);
            }
        }
        $chunks = array_chunk($arrTmp, 6);
    @endphp
    <div class="d-table">
        @foreach ($chunks as $row)
            <div class="table-row">
                @foreach ($row as $key => $item)
                    @php
                        $kodeLencanaVal = isset($item['detail']) && !empty($item['detail']->kode_lencana_selected) ? $item['detail']->kode_lencana_selected : ($item['pengguna'] ? $item['pengguna']->kode_lencana : '-');
                    @endphp
                    @if ($penyelia->permohonan->kontrak->jenis_tld === 2)
                        <div class="border text-center table-cell"
                            style="padding: 1px; width: 2.4cm; height: 1.25cm; max-width: 2.4cm; overflow: hidden; position: relative; vertical-align: middle;">
                            <div style="line-height: 1.1; font-size: 6.5pt; width: 100%;">
                                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $penyelia->permohonan->pelanggan->perusahaan->kode_perusahaan }}-{{ $item['pengguna'] ? $kodeLencanaVal : ($key > 1 ? 'C' . $key : 'C') }}
                                </div>
                                <div style="overflow: hidden; text-overflow: ellipsis;">
                                    {{ $item['pengguna'] ? $item['pengguna']->name : 'Kontrol' }}
                                </div>
                                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $item['periode'] }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="border text-center table-cell"
                            style="padding: 5px;height: 220px; width: 100px; position: relative;">
                            <div class="lh-16">
                                <div class="fs-1" style="white-space: nowrap; overflow: hidden;">
                                    {{ $penyelia->permohonan->pelanggan->perusahaan->kode_perusahaan }}-{{ $item['pengguna'] ? $kodeLencanaVal : ($key > 1 ? 'C' . $key : 'C') }}
                                </div>
                                <div class="fs-1">{{ $item['pengguna'] ? $item['pengguna']->name : 'Kontrol' }}</div>
                                <div class="fs-1" style="white-space: nowrap; overflow: hidden;">{{ $item['periode'] }}
                                </div>
                                <div>{{ $alias }}</div>
                            </div>
                            <div
                                style="margin-top: auto; transform: rotate(180deg);position: absolute; bottom: 0;left: 28%;">
                                belakang</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
