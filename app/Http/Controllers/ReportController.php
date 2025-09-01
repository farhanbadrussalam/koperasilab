<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Mews\Purifier\Facades\Purifier;
use Spatie\Browsershot\Browsershot;

use App\Models\Kontrak;
use App\Models\Kontrak_tld;
use App\Models\Kontrak_periode;
use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;
use App\Models\Keuangan;
use App\Models\Keuangan_diskon;
use App\Models\jadwal;
use App\Models\Jadwal_petugas;
use App\Models\Penyelia;
use App\Models\Master_tld;

use App\Models\Documents;

use PDF;
use Auth;
use Log;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->global = config('customvariabel');
    }

    private function generatePDF($title, $template, $variables, $htmlKeys = []){
        $result = array(
            'title' => $title,
        );

        $options = [
            'html_keys' => $htmlKeys,
            'sanitizer' => fn($h,$k) => Purifier::clean($h, 'ckpdf'), // pakai jika ada mews/purifier
            'allowed_tags'=> '<p><br><strong><b><em><i><u><span><div><img>',
        ];

        $result['header'] = $template->header ? renderMentionsToValuesFlexible($template->header->content, $variables, $options) : '';
        $result['footer'] = $template->footer ? renderMentionsToValuesFlexible($template->footer->content, $variables, $options) : '';
        $classHeader = $template->header ? 'withHeader' : '';
        $classFooter = $template->footer ? 'withFooter' : '';
        $result['body'] = "<div class='" . $classHeader . " " . $classFooter . "'>" . renderMentionsToValuesFlexible($template->content, $variables, $options) . "</div>";

        $html = view('report.index', $result)->render();

        $b = Browsershot::html($html)
            ->emulateMedia('screen')
            ->showBackground()                 // penting agar warna/background ikut tercetak
            ->setOption('displayHeaderFooter', false) // penting!
            ->format('A4')
            ->margins(0, 0, 0, 0)         // mm: top,right,bottom,left
            ->waitUntilNetworkIdle()          // tunggu asset selesai dimuat
            ->addChromiumArguments(['--allow-file-access-from-files']) // kadang perlu
            ->setOption('waitUntil', 'networkidle0')
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox']); // untuk banyak server Linux

            $bytes = $b->pdf();

        return $bytes;
    }
    public function invoice($id)
    {
        $idKeuangan = decryptor($id);

        if($idKeuangan == null){
            return redirect()->back();
        }

        $query = Keuangan::with([
            'diskon',
            'usersig',
            'permohonan',
            'permohonan.layanan_jasa:id_layanan,nama_layanan',
            'permohonan.jenisTld:id_jenisTld,name',
            'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
            'permohonan.jenis_layanan_parent',
            'permohonan.pelanggan',
            'permohonan.pelanggan.perusahaan',
            'permohonan.pelanggan.perusahaan.alamat',
            'permohonan.kontrak',
            'permohonan.dokumen' => function($q){
                $q->where('jenis', 'invoice');
            },
            'metode_pembayaran'
        ])->where('id_keuangan', $idKeuangan)->first();

        if($query->metode_pembayaran){
            $query->metode_pembayaran->content = contenMetodePembayaran($query->metode_pembayaran->content, $query->variabel_jenis_pembayaran);
        }

        $JL = jenislayanan($query->permohonan->jenis_layanan_parent, $query->permohonan->jenis_layanan);

        $data['date'] = Carbon::now();
        $data['title'] = "Invoice";
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];
        $data['is_catatan'] = !in_array($JL, $this->global['catatan_invoice']);

        $periodePemakaian = $query->permohonan->periode_pemakaian;

        if($query->permohonan && count($periodePemakaian) > 0){
            $data['periode_start'] = $periodePemakaian[0];
            $data['periode_end'] = $periodePemakaian[count($periodePemakaian) - 1] ?? null;
        }

        // mengambil template invoice
        $dokumen = $query->permohonan->dokumen->first();
        if(!$dokumen->id_doc_template){
            $template = Documents::with('footer', 'header')
                        ->where('jenis', 'body')
                        ->where('name', 'Invoice')
                        ->where('status', '1')
                        ->first();

            $variables = $this->mappingVars($template, $query, $data);
            $dokumen->update([
                'id_doc_template' => $template->id_doc,
                'variables' => $variables
            ]);
        } else {
            $template = Documents::with('footer', 'header')
                        ->where('id_doc', $dokumen->id_doc_template)
                        ->first();

            $variables = $dokumen->variables;
        }
        // TTD Invoice
        $ttd = $dokumen->ttd ?? "";
        $variables['TTD_IMG'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_keuangan' width='200px' height='200px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $bytes = $this->generatePDF("Invoice", $template, $variables, ['CATATAN_PEMBAYARAN', 'NOTICE', 'TTD_IMG', 'RINCIAN']);
        $filename = 'invoice-'.now()->format('Ymd-His').'.pdf';

        return response($bytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function contentInvoice($data, $params = null){
        $subJumlah = 0;

        if ($data->diskon) {
            foreach ($data->diskon as $item) {
                $item->jumDiskon = $data->permohonan->total_harga * ($item->diskon / 100);
                $subJumlah += $item->jumDiskon;
            }
        }

        $jumAfterDiskon = $data->permohonan->total_harga - $subJumlah;

        $jumPph = $data->pph ? $jumAfterDiskon * ($data->pph / 100) : 0;
        $jumAfterPph = $jumAfterDiskon - $jumPph;
        $jumPpn = $data->ppn ? $jumAfterPph * ($data->ppn / 100) : 0;

        $htmlDiskon = '';
        foreach ($data->diskon as $item) {
            $htmlDiskon .= '<tr>
                <td>' . $item->name . ' ' . $item->diskon . '%</td>
                <td>( ' . formatCurrency($item->jumDiskon) . ' )</td>
            </tr>';
        }

        $htmlPpn = '';
        if ($data->ppn) {
            $htmlPpn .= '<tr>
                <td>PPN ' . $data->ppn . '%</td>
                <td>( ' . formatCurrency($jumPpn) . ' )</td>
            </tr>';
        }

        $htmlPph = '';
        if ($data->pph) {
            $htmlPph .= '<tr>
                <td>PPH ' . $data->pph . '%</td>
                <td>( ' . formatCurrency($jumPph) . ' )</td>
            </tr>';
        }

        $result = [
            "TERBILANG" => angkaKeHuruf($jumAfterPph + $jumPpn),
            "RINCIAN" => '<table class="table-invoice">
                    <tr>
                        <td>' . $data->permohonan->jumlah_pengguna + $data->permohonan->jumlah_kontrol.' Unit
                            ' . $data->permohonan->jenisTld->name . ' x ' . count($data->permohonan->periode_pemakaian) . ' Periode x
                            ' . formatCurrency($data->permohonan->harga_layanan) . '</td>
                        <td>' . formatCurrency($data->permohonan->total_harga) . '</td>
                    </tr>
                    ' . $htmlDiskon . '
                    <tr>
                        <td>Sub Jumlah</td>
                        <td>' . formatCurrency($jumAfterDiskon) . '</td>
                    </tr>
                    ' . $htmlPph . '
                    ' . $htmlPpn . '
                    <tr>
                        <td>Jumlah</td>
                        <td>' . formatCurrency($jumAfterPph + $jumPpn) . '</td>
                    </tr>
                </table>'
        ];

        return $result;
    }

    public function mappingVars($template, $data, $params = null){
        $vars = array();
        switch ($template->name) {
            case 'Invoice':
                $vars["NOMOR"] = $data->no_invoice;
                $vars["LAMPIRAN"] = "Faktur Pajak";
                $vars["PERIHAL"] = "Invoice " . $data->permohonan->jenis_layanan_parent->name . " " . $data->permohonan->layanan_jasa->nama_layanan . " " . $data->permohonan->jenisTld->name;
                $vars["LOKASI"] = "Tangerang Selatan";
                $vars["TANGGAL"] = convert_date($data->permohonan->dokumen[0]->created_at, 2);
                $vars["PERUSAHAAN"] = $data->permohonan->pelanggan->perusahaan->nama_perusahaan;
                $vars["ALAMAT"] = $data->permohonan->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["KODE_POS"] = $data->permohonan->pelanggan->perusahaan->alamat[0]->kode_pos;
                $vars["TELEPON"] = $data->permohonan->pelanggan->telepon;
                $vars["JENIS_LAYANAN"] = $data->permohonan->jenis_layanan_parent->name;
                $vars["LAYANAN_JASA"] = $data->permohonan->layanan_jasa->nama_layanan;
                $vars["JENIS_TLD"] = $data->permohonan->jenisTld->name;
                $vars["PERIODE_MULAI"] = convert_date($params['periode_start']['start_date'], 6);
                $vars["PERIODE_SELESAI"] = convert_date($params['periode_end']['end_date'], 6);
                $vars["NO_KONTRAK"] = $data->permohonan->kontrak->no_kontrak;
                $vars["RINCIAN"] = "";
                $vars["TERBILANG"] = "";
                $vars["CATATAN_PEMBAYARAN"] = $data->metode_pembayaran->content;
                $vars["NOTICE"] = $params['is_catatan'] ? '
                    <div class="payment-notice">
                        PEMBAYARAN MAX 30 HARI<br>
                        DARI TANGGAL INVOICE<br>
                        KORESPONDENSI<br>
                        TELP. 021 - 74786334
                    </div>
                ' : "";
                $vars = array_merge($vars, $this->contentInvoice($data, $params));
                break;
            case "TandaTerima":
                $vars["JUDUL"] = "TANDA TERIMA PENGUJIAN/KALIBRASI";
                $vars["NOMOR"] = $data->dokumen->first()->nomer;
                $vars["PERUSAHAAN"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars["ALAMAT"] = $data->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["JENIS_PENGUJIAN"] = $data->periode ? 'Evaluasi TLD' : 'Zero cek';
                $vars["JUMLAH"] = $data->jumlah_pengguna . " Pengguna +" . $data->jumlah_kontrol . " Kontrol";
                $vars["PERIODE"] = $data->periode > 0 ? "Periode ". $data->periode : "Periode zero cek";
                $vars["TGL_PENERIMAAN"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["TGL_SELESAI"] = $params['selesaiPengujian'] ? convert_date($params['selesaiPengujian'], 2) : '.......';
                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["LOKASI"] = "Tangerang Selatan";
                $vars = array_merge($vars, $this->contentTandaTerima($data, $params));
                break;
            default:
                # code...
                break;
        }

        // cek apakah ada yang kurang dari template
        $keys = array_keys($vars);
        $missing = array_diff($template->variables ?? [], $keys);
        if(count($missing) > 0){
            $vars = array_merge($vars, array_fill_keys($missing, ""));
        }
        return $vars;
    }

    public function kwitansi($id)
    {
        $idKeuangan = decryptor($id);

        if($idKeuangan == null){
            return redirect()->back();
        }

        $query = keuangan::with([
            'permohonan',
            'permohonan.jenis_layanan',
            'permohonan.pelanggan',
            'permohonan.pelanggan.perusahaan',
            'permohonan.kontrak:id_kontrak,no_kontrak',
            'permohonan.dokumen' => function($q){
                $q->where('jenis', 'kwitansi');
            }
        ])->where('id_keuangan', $idKeuangan)->first();

        $data['data'] = $query;
        $data['title'] = 'Kwitansi';
        $data['date'] = Carbon::now();
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        // mengambil periode pertama dan terakhir
        $start = $query->permohonan->periode_pemakaian[0]['start_date'];
        $end = $query->permohonan->periode_pemakaian[count($query->permohonan->periode_pemakaian) - 1]['end_date'];
        $data['periode_start'] = convert_date($start, 6);
        $data['periode_end'] = convert_date($end, 6);

        $pdf = PDF::loadView('report.kwitansi', $data);

        $pdf->render();

        return $pdf->stream();
    }

    public function tandaTerima($idPermohonan)
    {
        $idPermohonan = decryptor($idPermohonan);

        if($idPermohonan == null){
            return redirect()->back();
        }

        $query = Permohonan::with([
            'jenisTld:id_jenisTld,name',
            'pelanggan',
            'pelanggan.perusahaan',
            'kontrak',
            'tandaterima',
            'tandaterima.pertanyaan',
            'jenis_layanan:id_jenisLayanan,name',
            'dokumen' => function($query) {
                return $query->where('jenis', 'tandaterima');
            },
            'lhu',
            'signature:id,name',
        ])->find($idPermohonan);

        $data['data'] = $query;
        $data['date'] = Carbon::now();
        $data['title'] = "Tanda Terima";
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];
        $data['selesaiPengujian'] = $query->lhu ? $query->lhu->end_date : null;

        // mengambil template invoice
        $dokumen = $query->dokumen->first();
        $template = Documents::with('footer', 'header')
                        ->where('id_doc', $dokumen->id_doc_template)
                        ->first();
        if($dokumen->variables){
            $variables = $dokumen->variables;
        } else {
            $variables = $this->mappingVars($template, $query, $data);
            $dokumen->update(['variables' => $variables]);
        }

        // TTD Invoice
        $ttd = $dokumen->ttd ?? "";
        $variables['TTD_PENERIMA'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='200px' height='200px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_PENERIMA_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $ttd_pemohon = $query->pelanggan->ttd ?? "";
        $variables['TTD_PEMOHON'] = $ttd_pemohon ? "
            <div style='text-align: center;'>
                <img src='$ttd_pemohon' alt='TTD_PEMOHON' width='200px' height='200px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_PEMOHON_BY'] = $query->pelanggan ? $query->pelanggan->name : '...........................................';

        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, ['RINCIAN', 'TTD_PENERIMA', 'TTD_PEMOHON']);

        $filename = $dokumen->nama.'-'.now()->format('Ymd-His').'.pdf';

        return response($bytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    private function contentTandaTerima($data, $params = null) {
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
        $jenisPengujian = $data->periode ? 'Evaluasi TLD' : 'Zero cek';

        return [
            "RINCIAN" => '
                <table class="table-tandaterima content-table ck-table-resized" border="1">
                    <colgroup>
                        <col style="width: 5%%" />
                        <col style="width: 45%" />
                        <col style="width: 5%%" />
                        <col style="width: 45%" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <td colspan="4">Jenis Pengujian/Kalibrasi: <span class="text-secondary">'. $jenisPengujian .'</span></td>
                        </tr>
                        '. $tdContent .'
                    </tbody>
                </table>
            '
        ];
    }

    public function suratTugas($id = null)
    {
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $query = Permohonan::with([
            'jenisTld:id_jenisTld,name',
            'pelanggan',
            'pelanggan.perusahaan',
            'layanan_jasa',
            'layanan_jasa.satuankerja',
            'jenis_layanan',
            'kontrak',
            'dokumen' => function($query) {
                return $query->where('jenis', 'surattugas');
            },
            'lhu',
            'lhu.petugas',
            'lhu.petugas.user:id,name',
            'lhu.petugas.jobs:id_map,id_jobs',
            'lhu.petugas.jobs.jobs:id_jobs,name',
            'lhu.createBy',
            'lhu.usersig:id,name',
        ])->find($id);

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'SURAT TUGAS UJI';
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];
        $data['data'] = $query;

        $pdf = PDF::loadView('report.suratTugas', $data);

        $pdf->render();

        return $pdf->stream();
    }

/**
 * Generates a PDF stream of the "Surat Pengantar" report.
 *
 * This function retrieves the report data based on the provided ID,
 * sets the necessary title and date information, and then loads
 * the 'suratPengantar' view to generate a PDF document. The PDF
 * is then rendered and streamed back to the user.
 *
 * @param string|null $id Encrypted report identifier.
 * @return \Illuminate\Http\Response The PDF stream response.
 */

    public function suratPengantar($id  = null, $periode = null)
    {
        $id = decryptor($id);
        $periode = $periode == 0 ? 1 : $periode;

        if($id == null){
            return redirect()->back();
        }

        $query = Kontrak::with([
            'jenisTld:id_jenisTld,name',
            'pelanggan',
            'pelanggan.perusahaan',
            'layanan_jasa:id_layanan,nama_layanan',
            'jenis_layanan:id_jenisLayanan,name',
            'periode' => function($query) use ($periode) {
                return $query->where('periode', $periode);
            },
            'signature:id,name',
        ])->find($id);

        if($query->periode[0]->nomer_surpeng == null){
            $noSurpeng = generateNoDokumen('surpeng');
            $query->periode[0]->nomer_surpeng = $noSurpeng;
            $query->periode[0]->created_surpeng_at = Carbon::now()->format('Y-m-d');
            $query->periode[0]->save();
        }

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Pengantar';
        $data['data'] = $query;
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        $pdf = PDF::loadView('report.suratPengantar', $data);

        $pdf->render();

        return $pdf->stream();
    }

    public function kontrak($id = null){
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $query = Kontrak::with([
            'jenisTld:id_jenisTld,name',
            'jenis_layanan:id_jenisLayanan,name',
            'jenis_layanan_parent:id_jenisLayanan,name',
            'layanan_jasa:id_layanan,nama_layanan',
            'pelanggan',
            'pelanggan.perusahaan',
            'periode' => function($query) {
                return $query->whereNotNull('id_permohonan')->orWhereNotNull('nomer_surpeng')->orderBy('periode', 'desc');
            }
        ])->find($id);

        $listTld = false;

        if($query && count($query->periode) > 0) {
            $listTld = Kontrak_tld::with('pengguna', 'divisi')
            ->where('id_kontrak', $query->id_kontrak)
            ->where('count_tld', $query->periode[0]->count_tld)->get();
        }

        // Memisahkan radiasi yang digunakan
        $listRadiasi = false;
        if(count($listTld) > 0) {
            foreach ($listTld as $key => $tld) {
                if(isset($tld->pengguna)){
                    foreach($tld->pengguna->radiasi as $item) {
                        $listRadiasi[$item->id_radiasi] = $item;
                    }
                }
            }
        }

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Kontrak';
        $data['data'] = $query;
        $data['list_tld'] = $listTld;
        $data['radiasi'] = $listRadiasi;
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        // Mengambil template kontrak
        $dokumen = $query->document_kontrak;
        $template = Documents::with('footer','header')
                    ->where('id_doc', $dokumen->id_doc_template)
                    ->first();
        if($dokumen->variables){
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
        }

        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, []);

        $filename = $dokumen->nama.'-'.now()->format('Ymd-His').'.pdf';

        return response($bytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
        // $pdf = PDF::loadView('report.perjanjian', $data);
        // $pdf->render();

        // return $pdf->stream();
    }

    public function label($id = null){
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $query = Penyelia::with([
            'permohonan',
            'permohonan.kontrak',
            'permohonan.pelanggan.perusahaan',
            'permohonan.periodenow',
        ])->where('id_penyelia', $id)->first();

        // mengambil list tld di kontrak
        $listTld = Kontrak_tld::with('pengguna', 'divisi')->where('id_kontrak', $query->permohonan->id_kontrak)
                    ->where('count_tld', $query->permohonan->periodenow->count_tld)
                    ->orderBy('id_pengguna', 'asc')
                    ->orderBy('id_divisi', 'asc')
                    ->get();

        $listTld->each(function($item) {
            $item->tld = $item->id_tld ? Master_tld::whereIn('id_tld', $item->id_tld)->get() : null;
        });

        $dataPeriode = Kontrak_periode::where('id_kontrak', $query->permohonan->id_kontrak)
                    ->where('periode', $query->permohonan->periode)
                    ->first();

        $data = array();

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Label';
        $data['data'] = json_decode($listTld);
        $data['penyelia'] = $query;
        $data['periode'] = $query->permohonan->periodenow;

        $pdf = PDF::loadView('report.label', $data);
        $pdf->render();

        return $pdf->stream();
    }

    /**
     * Mencetak persetujuan pengujian.
     *
     * @param  int  $idPermohonan  ID permohonan
     * @return \Illuminate\Http\Response
     */
    public function persetujuanPengujian($idPermohonan){
        $idPermohonan = decryptor($idPermohonan);

        if($idPermohonan == null){
            return redirect()->back();
        }

        // mengambil dokumen surat permintaan pengujian
        $dokumen = Permohonan_dokumen::where('id_permohonan', $idPermohonan)
                    ->where('jenis', 'permintaanpengujian')->first();

        if(!$dokumen) {
            // generate nomer dokumen
            $nodokumen = generateNoDokumen('permintaanpengujian', $idPermohonan);

            // Simpan dokumen permintaan pengujian
            $dokumen = Permohonan_dokumen::create(array(
                'id_permohonan' => $idPermohonan,
                'created_by' => Auth::user()->id,
                'nama' => 'Permintaan Pengujian',
                'jenis' => 'permintaanpengujian',
                'status' => 1,
                'nomer' => $nodokumen
            ));
        }

        $data = array();

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Persetujuan Pengujian';
        // $data['data'] = $dokumen;

        $pdf = PDF::loadView('report.permintaanPengujian', $data);
        $pdf->render();

        return $pdf->stream();
    }
}
