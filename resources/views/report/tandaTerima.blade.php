@extends('report.template.main')
@section('style')
    @include('report.template.style')
    @include('report.template.style-tandaterima')
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

        $jenisPengujian = $data->periode ? $data->jenis_layanan->name : 'Zero cek';
    ?>
    <h1 class="center w-100 text-underline lh-2">TANDA TERIMA PENGUJIAN/KALIBRASI</h1>
    <div class="w-100 center lh-1">
        <h2 class=" fw-normal">Nomor : <span class="text-secondary">{{ $data->dokumen[0]->nomer }}</span></h2>
    </div>
    <table class="table-header">
        <tr>
            <td width="20%">Telah terima dari</td>
            <td>: <span class="text-secondary">Nuklindolab koperasi JKRL</span></td>
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
            <td colspan="2">Jumlah : <span class="text-secondary">{{ $data->jumlah_pengguna + $data->jumlah_kontrol }}</span></td>
        </tr>
        <tr>
            <td width="50%">Tanggal Penerimaan : <span class="text-secondary">{{ convert_date($data->dokumen[0]->created_at, 2) }}</span></td>
            <td>Tanggal Selesai Pengujian : <span class="text-secondary">.......</span></td>
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

    <p>Catatan:</p>
    <ol>
        <li>Dengan penyerahan benda uji / alat ini, pihak pemohon menyetujui pekerjaan pengujian/kalibrasi yang akan
            dilakukan Laboratorium Pengujian BATAN sesuai dengan biaya pengujiannya/kalibrasinya.</li>
        <li>Untuk benda uji / alat yang dikrim melalui jasa ekspedisi, bila saat diterima ada kerusakan bukan menjadi
            tanggung jawab BATAN.</li>
        <li>Pengujian / kalibrasi tidak akan diproses sebelum ada surat permohonan dari pelanggan.</li>
    </ol>
    <p>*) Pilih yang sesuai</p>

    {{-- <div class=" d-flex justify-content-end" style="margin-top: 40px; border: 1px solid black;">
        <table class="table-catatan lh-1">
            <tr>
                <td colspan="2">Diisi rangkap 3 (tiga)</td>
            </tr>
            <tr>
                <td>Lembar 1 (Putih)</td>
                <td>: Pemohon</td>
            </tr>
            <tr>
                <td>Lembar 2 (Biru)</td>
                <td>: Unit Administrasi</td>
            </tr>
            <tr>
                <td>Lembar 3 (Kuning)</td>
                <td>: Unit Terkait Teknis</td>
            </tr>
        </table>
    </div> --}}
@endsection
