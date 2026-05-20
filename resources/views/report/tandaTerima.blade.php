@php
    $css = file_get_contents(public_path('css/tandaterima.css'));
@endphp
@extends('report.template.main')
@section('style')
@endsection

@section('header')
    @include('report.template.header')
@endsection

@section('content')
    <?php
        $tdContent = '';
        $getPertanyaan = [];
        foreach ($data->tandaterima as $key => $value) {
            array_push($getPertanyaan, $value->pertanyaan);
        }
        $half = ceil(count($getPertanyaan) / 2);
        $no = 'a';
        for ($i = 0; $i < $half; $i++) {
            $tdContent .= '<tr>';
                // kolom kiri
                if(isset($getPertanyaan[$i])){
                    $question = $getPertanyaan[$i]->pertanyaan;
                    $answer = $data->tandaterima[$i]->jawaban;

                    if($getPertanyaan[$i]->type == 1){
                        $tdContent .= '
                            <td width="5%" class="text-center">'.$no++.'.</td>
                            <td>
                                '.$getPertanyaan[$i]->pertanyaan.' :<br>
                                <span class="text-secondary">'.$data->tandaterima[$i]->jawaban.'</span>
                            </td>
                        ';
                    }else{
                        $tdContent .= '
                            <td colspan="2">
                                '.$getPertanyaan[$i]->pertanyaan.' : <span class="text-secondary">'.$data->tandaterima[$i]->jawaban.'</span><br>
                                Bila cacat, sebutkan : '.$data->tandaterima[$i]->note.'
                            </td>
                        ';
                    }

                }

                // kolom kanan

                if(isset($getPertanyaan[$i + $half])){
                    $question = $getPertanyaan[$i + $half]->pertanyaan;
                    $answer = $data->tandaterima[$i + $half]->jawaban;

                    if($getPertanyaan[$i + $half]->type == 1){
                        $tdContent .= '
                            <td width="5%" class="text-center">'.$no++.'.</td>
                            <td>
                                '.$getPertanyaan[$i + $half]->pertanyaan.' :<br>
                                <span class="text-secondary">'.$data->tandaterima[$i + $half]->jawaban.'</span>
                            </td>
                        ';
                    }else{
                        $tdContent .= '
                            <td colspan="2">
                                '.$getPertanyaan[$i + $half]->pertanyaan.' : <span class="text-secondary">'.$data->tandaterima[$i + $half]->jawaban.'</span><br>
                                Bila cacat, sebutkan : '.$data->tandaterima[$i + $half]->note.'
                            </td>
                        ';
                    }
                }
            $tdContent .= '</tr>';
        }

        $jenisPengujian = $data->periode ? 'Evaluasi TLD' : 'Zero Check';
    ?>
    <h1 class="center w-100 text-underline lh-1">TANDA TERIMA PENGUJIAN/KALIBRASI</h1>
    <div class="w-100 center" style="line-height: .2">
        <h2 class=" fw-normal">Nomor : <span class="text-secondary">{{ $data->dokumen[0]->nomer }}</span></h2>
    </div>
    <table class="table-header">
        <tr>
            <td width="20%">Telah terima dari</td>
            <td>: <span class="text-secondary">{{ $data->pelanggan->perusahaan->nama_perusahaan }}</span></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: <span class="text-secondary">Tangerang</span></td>
        </tr>
    </table>

    <table class="table table-content" border="1">
        <tr>
            <td colspan="4">Jenis Pengujian/Kalibrasi: <span class="text-secondary">{{ $jenisPengujian }}</span></td>
        </tr>
        <?php echo $tdContent; ?>
    </table>

    <table class="table-footer">
        <tr>
            <td colspan="2">Jumlah : <span class="text-secondary">{{ $data->jumlah_pengguna . " Pengguna +" . $data->jumlah_kontrol . " Kontrol" }}</span> <span>{{ $data->periode > 0 ? "Periode ". $data->periode : "Periode zero cek" }}</span></td>
        </tr>
        <tr>
            <td width="50%">Tanggal Penerimaan : <span class="text-secondary">{{ convert_date($data->dokumen[0]->created_at, 2) }}</span></td>
            <td>Tanggal Selesai Pengujian : <span class="text-secondary">{{ $selesaiPengujian ? convert_date($selesaiPengujian, 2) : '.......' }}</span></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>Tangerang Selatan, {{ convert_date($date, 2) }}</td>
        </tr>
    </table>

    <table class="table-ttd">
        <tr>
            <td width="50%">
                <div class="text-center d-flex">
                    <div class="flex-1">Pemohon,</div>
                    <img class="ttd-image" src="{{ $data->pelanggan->ttd ? $data->pelanggan->ttd : $ttd_default }}" alt="ttd" srcset="ttd">
                    <div class="flex-1">( {{ $data->pelanggan ? $data->pelanggan->name : '................................' }} )</div>
                    <div>Nama jelas</div>
                </div>
            </td>
            <td width="50%">
                <div class="text-center d-flex">
                    <div class="flex-1">Yang menerima,</div>
                    <img class="ttd-image" src="{{ $data->ttd ? $data->ttd : $ttd_default }}" alt="ttd" srcset="ttd">
                    <div class="flex-1">( {{ $data->signature ? $data->signature->name : '................................' }} )</div>
                    <div>Nama jelas</div>
                </div>
            </td>
        </tr>
    </table>

    <p style="line-height: .2">Catatan:</p>
    <ol>
        <li>Dengan penyerahan benda uji / alat ini, pihak pemohon menyetujui pekerjaan pengujian/kalibrasi yang akan
            dilakukan NuklindoLab-Koperasi JKRL dan bersedia menanggung biaya pengujiannya/kalibrasinya.</li>
        <li>Untuk benda uji / alat yang dikrim melalui jasa ekspedisi, bila saat diterima ada kerusakan bukan menjadi
            tanggung jawab NuklindoLab-Koperasi JKRL.</li>
        <li>Pengujian / kalibrasi tidak akan diproses sebelum ada surat permohonan dari pelanggan.</li>
    </ol>
    <p>*) Pilih yang sesuai</p>
@endsection
@section('footer')
    @include('report.template.footer')
@endsection
