<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Mews\Purifier\Facades\Purifier;

// use Spatie\Browsershot\Browsershot;
use Barryvdh\DomPDF\Facade\Pdf;

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
use App\Models\Kontrak_detail;
use App\Models\Master_pengguna;
use Auth;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Log;

class ReportController extends Controller
{
    protected $global;
    public function __construct()
    {
        $this->global = config('customvariabel');
    }

    private function generatePDF($title, $template, $variables = [], $htmlKeys = []){
        $result = array(
            'title' => $title,
        );

        $options = [
            'html_keys' => $htmlKeys,
            'sanitizer' => fn($h,$k) => Purifier::clean($h, 'ckpdf'), // pakai jika ada mews/purifier
            'allowed_tags'=> '<p><br><strong><b><em><i><u><span><div><img>',
            'orientation' => $template->orientation,
        ];

        $result['header'] = $template->header ? renderMentionsToValuesFlexible($template->header->content, $variables, $options) : '';
        $result['footer'] = $template->footer ? renderMentionsToValuesFlexible($template->footer->content, $variables, $options) : '';

        $result['body'] = renderMentionsToValuesFlexible($template->content, $variables, $options);

        $bytes = Pdf::loadView('report.index', $result);

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
            'permohonan.kontrak.periode',
            'permohonan.dokumen' => function($q){
                $q->where('jenis', 'invoice');
            },
            'metode_pembayaran'
        ])->where('id_keuangan', $idKeuangan)->first();

        if(isset($query->metode_pembayaran)){
            $query->metode_pembayaran->content = contenMetodePembayaran($query->metode_pembayaran->content, $query->variabel_jenis_pembayaran);
        }

        $JL = jenislayanan($query->permohonan->jenis_layanan_parent, $query->permohonan->jenis_layanan);

        $data['date'] = Carbon::now();
        $data['title'] = "Invoice";
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];
        $data['is_catatan'] = !in_array($JL, $this->global['catatan_invoice']);
        $periodePemakaian = $query->permohonan->periode_pemakaian;

        if($query->permohonan->tipe_kontrak == 'adendum') {
            $periodePemakaian = $query->permohonan->kontrak->periode->filter(function($item) use ($query) {
                return $item->periode >= $query->permohonan->periode;
            })->values();

            $data['periode_start'] = array(
                'start_date' => $periodePemakaian[0]->start_date,
                'end_date' => $periodePemakaian[0]->end_date,
            );
             $data['periode_end'] = array(
                'start_date' => $periodePemakaian[count($periodePemakaian) - 1]->start_date,
                'end_date' => $periodePemakaian[count($periodePemakaian) - 1]->end_date,
            );

            $data['count_periode'] = count($periodePemakaian);
        } else {
            if($query->permohonan && count($periodePemakaian) > 0){
                $data['periode_start'] = $periodePemakaian[0];
                $data['periode_end'] = $periodePemakaian[count($periodePemakaian) - 1] ?? null;
                $data['count_periode'] = count($periodePemakaian);
            }
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

            if($dokumen->variables) {
                $variables = $dokumen->variables;
            } else {
                $variables = $this->mappingVars($template, $query, $data);

                if($dokumen->ttd) {
                    $dokumen->update([
                        'variables' => $variables
                    ]);
                }
            }
        }
        // TTD Invoice
        if($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }
        $variables['TTD_IMG'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_keuangan' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $bytes = $this->generatePDF("Invoice", $template, $variables, ['CATATAN_PEMBAYARAN', 'NOTICE', 'TTD_IMG', 'RINCIAN']);
        $filename = 'invoice-'.now()->format('Ymd-His').'.pdf';

        return $bytes->stream($filename);
        // return response($bytes, 200, [
        //     'Content-Type'        => 'application/pdf',
        //     'Content-Disposition' => 'inline; filename="'.$filename.'"',
        // ]);
    }

    public function contentInvoice($data, $params = null){
        $dataKeuangan = calculateInvoice($data->permohonan->total_harga, $data->diskon, $data->ppn, $data->pph);

        $htmlDiskon = '';
        foreach ($dataKeuangan['diskon'] as $item) {
            $htmlDiskon .= '<tr>
                <td>' . $item->name . ' ' . $item->diskon . '%</td>
                <td>- ' . formatCurrency($item->jumDiskon) . '</td>
            </tr>';
        }

        $htmlPpn = '';
        if ($data->ppn) {
            $htmlPpn .= '<tr>
                <td>PPN ' . $data->ppn . '%</td>
                <td>( ' . formatCurrency($dataKeuangan['jumPpn']) . ' )</td>
            </tr>';
        }

        $htmlPph = '';
        if ($data->pph) {
            $htmlPph .= '<tr>
                <td>PPH ' . $data->pph . '%</td>
                <td>( - ' . formatCurrency($dataKeuangan['jumPph']) . ' )</td>
            </tr>';
        }

        $result = [
            "TERBILANG" => angkaKeHuruf($dataKeuangan['subTotal']),
            "RINCIAN" => '<table class="table-invoice">
                    <tr>
                        <td>' . $data->permohonan->jumlah_pengguna + $data->permohonan->jumlah_kontrol.' Unit
                            ' . $data->permohonan->jenisTld->name . ' x ' . $params['count_periode'] . ' Periode x
                            ' . formatCurrency($data->permohonan->harga_layanan) . '</td>
                        <td>' . formatCurrency($data->permohonan->total_harga) . '</td>
                    </tr>
                    ' . $htmlDiskon . '
                    <tr>
                        <td>Sub Jumlah</td>
                        <td>' . formatCurrency($dataKeuangan['jumAfterDiskon']) . '</td>
                    </tr>
                    ' . $htmlPph . '
                    ' . $htmlPpn . '
                    <tr>
                        <td>Jumlah</td>
                        <td>' . formatCurrency($dataKeuangan['subTotal']) . '</td>
                    </tr>
                </table>'
        ];

        return $result;
    }

    public function mappingVars($template, $data, $params = null){
        $vars = array();
        switch ($template->name) {
            case 'Invoice':
                $rangeDate = range_date($params['periode_start']['start_date'], $params['periode_end']['end_date'], 2);

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
                $vars["PERIODE_MULAI"] = $rangeDate['start'];
                $vars["PERIODE_SELESAI"] = $rangeDate['end'];
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
                if($data->tipe_kontrak == 'kontrak baru') {
                    $pemakaian = $data->periode_pemakaian;
                    $vars["PERIODE_RINCIAN"] = convert_date($pemakaian[0]['start_date'], 7) . " s/d " . convert_date($pemakaian[0]['end_date'], 7);
                } else {
                    $findPeriode = $data->kontrak->periode->where('periode', $data->periode)->first();
                    $vars["PERIODE_RINCIAN"] = convert_date($findPeriode->start_date, 7) . " s/d " . convert_date($findPeriode->end_date, 7);
                }
                $vars["JUDUL"] = "TANDA TERIMA PENGUJIAN/KALIBRASI";
                $vars["NOMOR"] = $data->dokumen->first()->nomer;
                $vars["PERUSAHAAN"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars["ALAMAT"] = $data->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["JENIS_PENGUJIAN"] = $data->periode ? 'Evaluasi TLD' : 'Zero cek';
                $vars["JUMLAH"] = $data->jumlah_pengguna . " Pengguna +" . $data->jumlah_kontrol . " Kontrol";
                $vars["PERIODE"] = ($data->periode > 0 ? "Periode ". $data->periode : "Periode zero cek");
                $vars["TGL_PENERIMAAN"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["TGL_SELESAI"] = $params['selesaiPengujian'] ? convert_date($params['selesaiPengujian'], 2) : '';
                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["LOKASI"] = "Tangerang Selatan";
                $vars = array_merge($vars, $this->contentTandaTerima($data, $params));
                break;
            case "Kontrak":
                $vars["NOMOR"] = $data->no_kontrak;
                $vars["JENIS_LAYANAN"] = $data->jenis_layanan->name;
                $vars["LAYANAN_JASA"] = $data->layanan_jasa->nama_layanan;
                $vars["JENIS_TLD"] = $data->jenisTld->name;
                $vars["PERUSAHAAN"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars["ALAMAT"] = $data->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["KODE_POS"] = $data->pelanggan->perusahaan->alamat[0]->kode_pos;
                $vars["TELEPON"] = $data->pelanggan->profile->no_hp;
                $vars["FAX"] = $data->pelanggan->profile->no_fax;
                $vars["HARI"] = convert_date($data->document_kontrak[0]->created_at, 8);
                $vars["TGL_BUAT"] = convert_date($data->document_kontrak[0]->created_at, 9);
                $vars["TAHUN"] = convert_date($data->document_kontrak[0]->created_at, 10);
                $vars["NAMA_PIC"] = $data->pelanggan->name;
                $vars["JABATAN_PIC"] = $data->pelanggan->jabatan;
                $vars["EMAIL_PIC"] = $data->pelanggan->email;
                $vars["JML_UNIT"] = $data->jumlah_pengguna + $data->jumlah_kontrol;
                $vars["JML_P"] = $data->jumlah_pengguna;
                $vars["JML_P_HRF"] = angkaKeHuruf($data->jumlah_pengguna);
                $vars["JML_K"] = $data->jumlah_kontrol;
                $vars["JML_K_HRF"] = angkaKeHuruf($data->jumlah_kontrol);
                $vars["PERIODE_BULAN"] = $data->periode_all['jml_all_bulan'];
                $vars["PERIODE_MULAI"] = convert_date($data->periode_all['periode_awal'], 6);
                $vars["PERIODE_SELESAI"] = convert_date($data->periode_all['periode_akhir'], 6);
                $vars["PERIODE_JML"] = $data->periode_all['jml_periode'];
                $vars["NAMA_MANAGER"] = "Dr. Eko Pudjadi, M.Sc";
                $vars["JML_ALL_UNIT"] = $vars["JML_UNIT"] * $vars["PERIODE_JML"];
                $vars["JML_ALL_UNIT_HRF"] = angkaKeHuruf($vars["JML_ALL_UNIT"]);
                $vars["RADIOAKTIF"] = implode(", ", array_map(fn($item) => $item['nama_radiasi'], $data->data_radiasi));
                break;
            case "SuratTugas":
                $vars["NOMOR"] = $data->dokumen[0]->nomer;
                $vars['UNIT'] = $data->layanan_jasa->satuankerja->name;
                $vars["PENGUJIAN"] = $data->periode == 0 && $data->is_zerocek == 1 ? 'Zero cek' : 'Evaluasi TLD';
                $vars["LAYANAN_JASA"] = $data->layanan_jasa->nama_layanan;
                $vars["JENIS_TLD"] = $data->jenisTld->name;
                $vars["PERUSAHAAN"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars["JML_P"] = $data->jumlah_pengguna;
                $vars["JML_K"] = $data->jumlah_kontrol;
                $vars["TGL_MULAI"] = convert_date($data->lhu->start_date, 2);
                $vars["TGL_SELESAI"] = convert_date($data->lhu->end_date, 2);
                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars = array_merge($vars, $this->contentSuratTugas($data, $params));
                break;
            case "SuratPengantar":
                $zerocek = '';
                if(isset($params['permohonan']) && $params['permohonan']->is_zerocek == 1){
                    $zerocek = " & Hasil Zero Cek";
                }

                $vars["NOMOR"] = $params["dokumen"]->nomer;
                $vars["PERIODE_AWAL"] = convert_date($params['kontrak_periode']->start_date, 6);
                $vars["PERIODE_SELESAI"] = convert_date($params['kontrak_periode']->end_date, 6);
                $vars["PERIODE_NOW"] = $params['kontrak_periode']->status == 2 ? "periode pengembalian" : "periode {$params['kontrak_periode']->periode}";
                $vars["TGL_BUAT"] = convert_date($params["dokumen"]->created_at, 2);

                $vars["JML_UNIT"] = $data->jumlah_pengguna + $data->jumlah_kontrol;
                $vars["LAYANAN_JASA"] = $data->layanan_jasa->nama_layanan;
                $vars["ZEROCEK"] = $zerocek;
                $vars["JENIS_TLD"] = $data->jenisTld->name;
                $vars["PETUGAS"] = $data->pelanggan->name;
                $vars["ALAMAT"] = $data->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["TELEPON"] = $data->pelanggan->profile->no_hp;
                $vars["PETUGAS_DIVISI"] = $data->pelanggan->jabatan;
                $vars["JML_P"] = $data->jumlah_pengguna;
                $vars["JML_K"] = $data->jumlah_kontrol;
                $vars["NO_KONTRAK"] = $data->no_kontrak;
                $vars["PERUSAHAAN"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars= array_merge($vars, $this->contentSuratPengantar($data, $params));
                break;
            case "Kwitansi":
                $rangeDate = range_date($params['periode_start'], $params['periode_end'], 2);

                $vars["NOMOR"] = $data->permohonan->kontrak->dokumen[0]->nomer;
                $vars["PERUSAHAAN"] = $data->permohonan->pelanggan->perusahaan->nama_perusahaan;
                $vars["JENIS_LAYANAN"] = $data->permohonan->jenis_layanan->name;
                $vars["LAYANAN_JASA"] = $data->permohonan->layanan_jasa->nama_layanan;
                $vars["JENIS_TLD"] = $data->permohonan->jenisTld->name;
                $vars["JML_UNIT"] = $data->permohonan->jumlah_pengguna + $data->permohonan->jumlah_kontrol;
                $vars["JML_PERIODE"] = $params['periode_jumlah'];
                $vars["HARGA"] = formatCurrency($data->permohonan->harga_layanan);
                $vars["HARGA_AWAL"] = formatCurrency($data->permohonan->total_harga);
                $vars["PERIODE_AWAL"] = $rangeDate['start'];
                $vars["PERIODE_SELESAI"] = $rangeDate['end'];
                $vars["TGL_BUAT"] = convert_date($data->permohonan->kontrak->dokumen[0]->created_at, 2);
                $vars["NO_KONTRAK"] = $data->permohonan->kontrak->no_kontrak;
                $vars["LOKASI_BUAT"] = "Tangerang Selatan";
                $vars = array_merge($vars, $this->contentKwitansi($data, $params));
                break;
            case "SuratPengujian":
                $vars["JUDUL"] = "PERSETUJUAN TERHADAP PERMINTAAN PENGUJIAN";
                $vars["NOMOR"] = $data->dokumen[0]->nomer;
                $vars["PERUSAHAAN"] = $data->permohonan->pelanggan->perusahaan->nama_perusahaan;
                $vars["ALAMAT"] = $data->permohonan->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["LOKASI_BUAT"] = "Tangerang Selatan";
                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars['SATUANKERJA'] = $data->permohonan->layanan_jasa->satuankerja->name;
                $vars["ACTION"] = $data->dokumen[0]->catatan == "approve" ? "Menyetujui" : "";
                $vars["PERMINTAAN"] = "Pengujian";
                $vars = array_merge($vars, $this->contentSuratPengujian($data, $params));
                break;
            case "KontrakPengujian":
                $vars["JUDUL"] = "PERSETUJUAN TERHADAP PERMINTAAN PENGUJIAN";
                $vars["NOMOR"] = $data->dokumen[0]->nomer;
                $vars["PERMINTAAN"] = "Pengujian";
                $vars["PERUSAHAAN"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars["ALAMAT"] = $data->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["LOKASI"] = "Tangerang Selatan";
                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["UNIT"] = $data->layanan_jasa->satuankerja->name;
                if($data->permohonan->lhu->end_date){
                    $vars['TGL SELESAI'] = convert_date($data->permohonan->lhu->end_date, 2);
                }
                $vars = array_merge($vars, $this->contentKontrakPengujian($data, $params));
                break;
            case "PermohonanAdendum":
                // ["TGL_BUAT","NO_SURAT","PERIHAL","TUJUAN","ALAMAT_TUJUAN","PERUSAHAAN","LAYANAN_JASA","JENIS_TLD","JENIS_LAYANAN","NO_KONTRAK","JUMLAH_TLD","PERIODE","RINCIAN_TLD","TTD","TTD_BY","TELEPON","LOKASI"]

                $vars["PERIHAL"] = "Permohonan {$params['jenis']} {$data->jenis_layanan->name} TLD";

                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["NO_SURAT"] = $data->dokumen[0]->nomer;
                $vars['TUJUAN'] = env('NAMA_PERUSAHAAN');
                $vars['ALAMAT_TUJUAN'] = env('ALAMAT_PERUSAHAAN');
                $vars['TELEPON'] = env('TELEPON_PERUSAHAAN');
                $vars['PERUSAHAAN'] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars['JENIS_LAYANAN'] = $data->jenis_layanan->name;
                $vars['JENIS_TLD'] = $data->jenisTld->name;
                $vars['LAYANAN_JASA'] = $data->layanan_jasa->nama_layanan;
                $vars['NO_KONTRAK'] = $data->kontrak->no_kontrak;
                $vars['JUMLAH_TLD'] = $data->jumlah_pengguna + $data->jumlah_kontrol;
                $range = range_date($params['periode']->start_date, $params['periode']->end_date, 1);
                $vars['PERIODE'] = $range['start'] . '-' . $range['end'];
                $vars['LOKASI'] = "Jakarta";

                $vars['TTD'] = "
                    <div style='text-align: center;'>
                        <img src='{$data->pelanggan->ttd_image}' alt='TTD' width='100px' height='100px'>
                    </div>
                ";
                $vars['TTD_BY'] = $data->pelanggan->name;
                $vars = array_merge($vars, $this->contentPermohonanAdendum($data, $params));
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
            'permohonan.kontrak',
            'permohonan.kontrak.periode',
            'permohonan.kontrak.dokumen' => function($q){
                $q->where('jenis', 'kwitansi');
            },
            'permohonan.kontrak.dokumen.doc_template',
            'permohonan.kontrak.dokumen.doc_template.footer',
            'permohonan.kontrak.dokumen.doc_template.header'
        ])->where('id_keuangan', $idKeuangan)->first();

        $data['data'] = $query;
        $data['title'] = 'Kwitansi';
        $data['date'] = Carbon::now();
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        // mengambil periode pertama dan terakhir
        if($query->permohonan->tipe_kontrak == 'adendum'){
            $periodePemakaian = $query->permohonan->kontrak->periode->filter(function($item) use ($query) {
                return $item->periode >= $query->permohonan->periode;
            })->values();

            $start = $periodePemakaian->first()->start_date;
            $end = $periodePemakaian->last()->end_date;
            $data['periode_start'] = $start;
            $data['periode_end'] = $end;
            $data['periode_jumlah'] = $periodePemakaian->count();
        } else {
            $start = $query->permohonan->periode_pemakaian[0]['start_date'];
            $end = $query->permohonan->periode_pemakaian[count($query->permohonan->periode_pemakaian) - 1]['end_date'];
            $data['periode_start'] = $start;
            $data['periode_end'] = $end;
            $data['periode_jumlah'] = count($query->permohonan->periode_pemakaian);
        }

        // mengambil template kwitansi
        $dokumen = $query->permohonan->kontrak->dokumen->first();
        if($dokumen) {
            $template = $dokumen->doc_template;

            if($dokumen->variables) {
                $variables = $dokumen->variables ?? [];
            } else {
                $variables = $this->mappingVars($template, $query, $data);
            }
        } else {
            $template = null;
            $variables = [];
        }

        // TTD KWITANSI
        if($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }
        $variables['TTD'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' style='margin-left: 15%;' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $bytes = $this->generatePDF("Kwitansi", $template, $variables, ['RINCIAN', 'TTD']);
        $filename = 'kwitansi-'.now()->format('Ymd-His').'.pdf';

        return $bytes->stream($filename);
        // $pdf = PDF::loadView('report.kwitansi', $data);
        // $pdf->render();
        // return $pdf->stream();
    }

    private function contentKwitansi($data, $params = null) {
        $subJumlah = 0;

        $tbl_rincian = "";

        if ($data->diskon) {
            foreach ($data->diskon as $item) {
                $item->jumDiskon = $data->permohonan->total_harga * ($item->diskon / 100);
                $subJumlah += $item->jumDiskon;

                $tbl_rincian .= '
                    <tr>
                        <td width="20%"></td>
                        <td><p class="lh-16">'. $item->name .' ' . $item->diskon . '%</p></td>
                        <td style="text-align: right">' . formatCurrency($item->jumDiskon) . ' ,-</td>
                    </tr>
                ';
            }
        }

        $jumAfterDiskon = $data->permohonan->total_harga - $subJumlah;

        $jumPph = $data->pph ? $jumAfterDiskon * ($data->pph / 100) : 0;
        $jumAfterPph = $jumAfterDiskon - $jumPph;
        $jumPpn = $data->ppn ? $jumAfterPph * ($data->ppn / 100) : 0;
        $subTotal = $jumAfterPph + $jumPpn;

        if($data->pph) {
            $tbl_rincian .= '
                <tr>
                    <td width="20%"></td>
                    <td><p class="lh-16">PPH ' . $data->pph . '%</p></td>
                    <td style="text-align: right">- ' . formatCurrency($jumPph) . ' ,-</td>
                </tr>
            ';
        }

        if($data->ppn) {
            $tbl_rincian .= '
                <tr>
                    <td width="20%"></td>
                    <td><p class="lh-16">PPN ' . $data->ppn . '%</p></td>
                    <td style="text-align: right">' . formatCurrency($jumPpn) . ' ,-</td>
                </tr>
            ';
        }

        return [
            "RINCIAN" => '
                <table class="table-kwitansi" border="0">
                    '. $tbl_rincian .'
                </table>
            ',
            "HARGA_TOTAL" => formatCurrency($subTotal),
            "HARGA_TOTAL_HRF" => angkaKeHuruf($subTotal)
        ];
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
            'kontrak.periode',
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
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
            if($query->lhu && $query->lhu->end_date){
                $variables['SELESAI_PENGUJIAN'] = convert_date($query->lhu->end_date, 6);
                $dokumen->update(['variables' => $variables]);
            }
        }

        // TTD Invoice
        if($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }
        $variables['TTD_PENERIMA'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_PENERIMA_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $ttd_pemohon = $query->pelanggan->ttd_image ?? "";
        $variables['TTD_PEMOHON'] = $ttd_pemohon ? "
            <div style='text-align: center;'>
                <img src='$ttd_pemohon' alt='TTD_PEMOHON' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_PEMOHON_BY'] = $query->pelanggan ? $query->pelanggan->name : '...........................................';

        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, ['RINCIAN', 'TTD_PENERIMA', 'TTD_PEMOHON']);

        $filename = $dokumen->nama.'-'.now()->format('Ymd-His').'.pdf';

        return $bytes->stream($filename);
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
                            <td width="1%" class="text-center">'.$no++.'.</td>
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
                            <td width="1%" class="text-center">'.$no++.'.</td>
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

        // mengambil template invoice
        $dokumen = $query->dokumen->first();
        $template = Documents::with('footer', 'header')
                    ->where('id_doc', $dokumen->id_doc_template)
                    ->first();

        if($dokumen->variables){
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);

            if($dokumen->ttd){
                $dokumen->update([
                    'variables' => $variables
                ]);
            }
        }

        // TTD Surat Tugas
        if($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }
        $variables["TTD"] = $ttd ? "
            <div>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables["TTD_BY"] = $dokumen->usersig ? $dokumen->usersig->name : ".....................";

        // generate pdf
        $bytes = $this->generatePdf($data['title'], $template, $variables, ["TTD", "RINCIAN"]);

        $filename = $dokumen->nama.'-'.now()->format('Ymd-His').'.pdf';

        return $bytes->stream($filename);
        // return response($bytes, 200, [
        //     'Content-Type'        => 'application/pdf',
        //     'Content-Disposition' => 'inline; filename="'.$filename.'"',
        // ]);
        // $pdf = PDF::loadView('report.suratTugas', $data);

        // $pdf->render();

        // return $pdf->stream();
    }

    private function contentSuratTugas($data, $params) {
    $html = '';
    $no   = 1;
    $arr  = [];

    // Grouping berdasarkan user
    foreach ($data->lhu->petugas as $value) {
        $arr[$value->user->id][] = [
            "name" => $value->user->name,
            "jobs" => $value->jobs->jobs->name
        ];
    }

    $rowCount = 0;
    $html .= '
        <table class="table-surattugas" border="1">
            <thead style="text-align: center;">
                <tr>
                    <th style="width: 1%">No</th>
                    <th style="width: 40%">Nama</th>
                    <th style="width: 40%">Tugas</th>
                </tr>
            </thead>
            <tbody>
    ';

    foreach ($arr as $key => $value) {
        $html .= '
            <tr>
                <td style="text-align: center;">'.$no.'</td>
                <td>'.$value[0]['name'].'</td>
                <td>'.implode(', ', array_column($value, 'jobs')).'</td>
            </tr>
        ';

        $no++;
        $rowCount++;

        // setiap kelipatan 10
        if ($rowCount % 10 == 0 && $rowCount < count($arr)) {
            $html .= '
                </tbody>
            </table>
            <div class="page-break"></div>
            <table class="table-surattugas" border="1">
                <thead style="text-align: center;">
                    <tr>
                        <th style="width: 1%">No</th>
                        <th style="width: 40%">Nama</th>
                        <th style="width: 40%">Tugas</th>
                    </tr>
                </thead>
                <tbody>
            ';
        }
    }

    $html .= '</tbody></table>';

    return [
        "RINCIAN" => $html
    ];
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

    public function suratPengantar($id  = null, $periode_ = null)
    {
        $id = decryptor($id);
        // mengambil id permohonan
        $id_permohonan = Kontrak_periode::select('id_permohonan')->where('id_kontrak', $id)->where('periode', $periode_)->first()->id_permohonan;
        $periode = $periode_ == 0 ? 1 : $periode_;

        if($id == null){
            return redirect()->back();
        }

        $query = Kontrak::with([
            'jenisTld:id_jenisTld,name',
            'pelanggan',
            'pelanggan.perusahaan',
            'layanan_jasa:id_layanan,nama_layanan',
            'jenis_layanan:id_jenisLayanan,name',
            'signature:id,name',
        ])->find($id);

        // ambil periode kontrak saat ini
        $kontrakPeriode = Kontrak_periode::where('id_kontrak', $id)->where('periode', $periode)->first();

        // mengambil dokumen surat pengantar
        $dokumen = Permohonan_dokumen::where("id_kontrak", $id)
                    ->where("periode", $periode_)
                    ->where("jenis", "surpeng")->first();

        $template = Documents::with('footer', 'header')
                ->where('jenis', 'body')
                ->where('name', 'SuratPengantar')
                ->where('status', '1')
                ->first();

        if($kontrakPeriode->nomer_surpeng == null){
            $noSurpeng = generateNoDokumen('surpeng');
            $kontrakPeriode->nomer_surpeng = $noSurpeng;
            $kontrakPeriode->created_surpeng_at = Carbon::now()->format('Y-m-d');
            $kontrakPeriode->save();

            if(!$dokumen){
                $textPeriode = $kontrakPeriode->status == 2 ? "Pengembalian" : "Periode $periode";
                $dokumen = Permohonan_dokumen::create(array(
                    'periode' => $periode_,
                    'id_kontrak' => $id,
                    'id_permohonan' => $id_permohonan ?? null,
                    'id_doc_template' => $template->id_doc,
                    'jenis' => "surpeng",
                    "nama" => "Surat Pengantar ($textPeriode)",
                    "nomer" => $noSurpeng,
                    "created_by" => Auth::user()->id,
                    "status" => 1
                ));
            } else {
                $dokumen->nomer = $noSurpeng;
                $dokumen->save();
            }
        } else {
            // yang ini di comment lagi karna jadi issue saat pengiriman periode zero cek
            // return redirect()->back();
        }

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Pengantar';
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        $data["dokumen"] = $dokumen;
        $data["kontrak_periode"] = $kontrakPeriode ?? null;
        $data['periode'] = $periode;

        if($id_permohonan) {
            $data["permohonan"] = Permohonan::where('id_permohonan', $id_permohonan)->first();
        }
        if(isset($dokumen) && $dokumen->variables){
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
        }

        if($query->document_kontrak[0]->ttd_image) {
            $ttd = $query->document_kontrak[0]->ttd_image;
        } else {
            $ttd = $query->document_kontrak[0]->ttd ?? "";
        }
        $variables["TTD"] = $ttd ? "
            <div style='text-align: center;'>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_keuangan' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables["TTD_BY"] = $query->document_kontrak[0]->usersig ? $query->document_kontrak[0]->usersig->name : '...........................................';

        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, ["RINCIAN", "TTD"]);

        $filename = $dokumen->nama.'-'.now()->format('Ymd-His').'.pdf';

        return $bytes->stream($filename);
    }

    private function contentSuratPengantar($data, $params){
        $html = '';
        $no = 1;
        $countKontrol = 0;
        $kontrakDetail = Kontrak_detail::with([
            'entitas'
        ])
        ->where('id_kontrak', $data->id_kontrak)
        ->where('status', 1)
        ->get();

        // jika ada dendum yang baru akan di masukkan ke kontrak detail dengan jenis kontrol, jadi untuk menampilkan di surat pengantar hanya yang jenis pengguna saja, sedangkan yang jenis kontrol akan dihitung jumlahnya saja
        $detailAdendum = kontrak_detail::with([
            'entitas',
            'penggunaLama'
        ])
        ->where('id_kontrak', $data->id_kontrak)
        ->where('status', 2)
        ->where('periode', $params['periode'])
        ->get();

        foreach ($detailAdendum as $value) {
            if($value->jenis == 'pengguna'){
                if($value->type == 'ganti'){
                    $index = $kontrakDetail->search(function($item) use ($value) {
                        return $item->id_pengguna_divisi == $value->pengguna_lama;
                    });

                    if($index !== false){
                        $kontrakDetail[$index] = $value;
                    }
                } else {
                    $kontrakDetail->push($value);
                }
            }
        }

        foreach ($kontrakDetail as $value) {
            if($value->jenis == 'pengguna'){
                $htmlDesc = $value->type ?? '';
                if($value->type == 'ganti') {
                    $htmlDesc = ' (Pengganti ' . $value->penggunaLama->name . ')';
                }
                $html .= '
                    <tr>
                        <td class="text-center">'.$no++.'.</td>
                        <td style="padding-left: 5px">'.$value->entitas->name.'</td>
                        <td style="padding-left: 5px">'.$htmlDesc.'</td>
                    </tr>
                ';
            } else {
                $countKontrol++;
            }
        }
        return [
            "RINCIAN" => '
                <table class="table-surattugas" style="margin-top: 15px;">
                    <tr>
                        <th width="1%">No</th>
                        <th width="30%">Nama Pemakai TLD</th>
                        <th width="30%">Keterangan</th>
                    </tr>
                    '.$html.'
                    <tr>
                        <td class="text-center">'. $no .'.</td>
                        <td style="padding-left: 5px">TLD Kontrol</td>
                        <td style="padding-left: 5px" class="fw-bold">'. $countKontrol .' Buah</td>
                    </tr>
                </table>
            '
        ];
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
            'pelanggan.profile',
            'pelanggan.perusahaan',
            'pelanggan.perusahaan.alamat',
            'periode' => function($query) {
                return $query->whereNotNull('id_permohonan')->orWhereNotNull('nomer_surpeng')->orderBy('periode', 'desc');
            }
        ])->find($id);

        $listTld = Kontrak_detail::with([
            'entitas' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                ]);
            }
        ])->where('id_kontrak', $query->id_kontrak)->get();

        // Memisahkan radiasi yang digunakan
        $listRadiasi = false;
        foreach ($listTld as $key => $tld) {
            if($tld->jenis == 'pengguna'){
                foreach($tld->entitas->radiasi as $item) {
                    $listRadiasi[$item->id_radiasi] = $item;
                }
            }
        }

        // mengambil invoice dari permohonan pertama
        $kontrakPeriode = Kontrak_periode::with('permohonan', 'permohonan.invoice')->where('id_kontrak', $id)->orderBy('periode', 'asc')->first();
        $invoice = false;
        if($kontrakPeriode){
            $invoice = $kontrakPeriode->permohonan->invoice;
        }

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Kontrak';
        $data['list_tld'] = $listTld;
        $data['radiasi'] = $listRadiasi;
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        // Mengambil template kontrak
        $dokumen = $query->document_kontrak->first();
        $template = Documents::with('footer','header')
                    ->where('id_doc', $dokumen->id_doc_template)
                    ->first();
        if($dokumen->variables){
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
            // kondisi jika invoice belum dibuat
            // 1 = Pengajuan
            // 2 = TTD General Manager
            if(!in_array($invoice->status, [1,2])) {
                $total = $query->total_harga + ($query->total_harga * ($invoice->ppn / 100));
                $variables['INVOICE'] = "
                    ".$variables['JML_UNIT']." buah ". $variables['LAYANAN_JASA']. " " . $variables['JENIS_TLD'] . " x " . $variables['PERIODE_JML'] . " Periode x " . formatCurrency($query->harga_layanan) . ",- = " . formatCurrency($query->total_harga) . ",-
                    ditambah PPN " . $invoice->ppn . "% total biaya yang harus dibayar sebesar " . formatCurrency($total) . ",-
                ";
                $variables["INVOICE_HRF"] = angkaKeHuruf($total);
                $dokumen->update(['variables' => $variables]);
            }
        }

        // TTD
        if($query->pelanggan->ttd_image) {
            $ttd_1 = $query->pelanggan->ttd_image;
        } else {
            $ttd_1 = $query->pelanggan->ttd ?? "";
        }
        $variables["TTD_1"] = $ttd_1 ? "
            <div style='text-align: center;'>
                <img src='$ttd_1' alt='TTD PIHAK 1' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";

        $variables["TTD_2"] = "<br><br><br>";

        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, ['TTD_1', 'TTD_2']);

        $filename = $dokumen->nama.'-'.now()->format('Ymd-His').'.pdf';

        return $bytes->stream($filename);
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
        $periodeNow = $query->permohonan->periodenow;
        if($query->permohonan->periodenow->periode == 0){
            $getKperiode = Kontrak_periode::where('id_kontrak', $query->permohonan->id_kontrak)->where('periode', 1)->first();
            $periodeNow = $getKperiode;
        }

        $listTld = Kontrak_detail::with([
            'entitas' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                ]);
            },
            'tld_1',
            'tld_2'
        ])->where('id_kontrak', $query->permohonan->id_kontrak)->get();

        $data = array();

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Label';
        $data['data'] = json_decode($listTld);
        $data['penyelia'] = $query;
        $data['periode'] = $periodeNow;

        $pdf = PDF::loadView('report.label', $data);
        $pdf->setPaper('a4', 'landscape');
        $pdf->render();

        return $pdf->stream();
    }

    /**
     * Mencetak persetujuan pengujian.
     *
     * @param  int  $idPermohonan  ID permohonan
     * @return \Illuminate\Http\Response
     */
    public function SuratPengujian($idPermohonan){
        $idPermohonan = decryptor($idPermohonan);
        if($idPermohonan == null){
            return redirect()->back();
        }

        $query = Penyelia::with([
            'permohonan',
            'permohonan.layanan_jasa.satuankerja',
            'permohonan.jenisTld',
            'permohonan.pelanggan',
            'permohonan.pelanggan.perusahaan',
            'permohonan.pelanggan.perusahaan.alamat',
            'permohonan.kontrak',
            'dokumen' => function($q){
                $q->where('jenis', 'SuratPengujian');
            }
        ])->where('id_permohonan', $idPermohonan)->first();

        // mengambil dokumen surat permintaan pengujian

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Persetujuan Pengujian';
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        $dokumen = $query->dokumen->first();
        $template = Documents::with('footer', 'header')
                    ->where('id_doc', $dokumen->id_doc_template)
                    ->first();

        if($dokumen->variables){
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
        }

        // TTD
        if($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }
        $variables['TTD'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' style='margin-left: 15%;' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '';
        // generate pdf
        $bytes = $this->generatePdf($data['title'], $template, $variables, ["RINCIAN", "RINCIAN_2", "TTD"]);

        $filename = $dokumen->nama.'-'.now()->format('Ymd-His').'.pdf';
        return $bytes->stream($filename);
    }

    private function contentSuratPengujian($data, $params = null) {
        $zrcek = $data->permohonan->is_zerocek ? 'Zero Cek' : '';
        $lJasa = $data->permohonan->layanan_jasa->satuankerja->name;
        $jTld = $data->permohonan->jenisTld->name;

        $jenisPengujian = $zrcek . ' ' . $lJasa . ' ' . $jTld;
        $htmlSample = '<div>' . $lJasa . ' ' . $jTld . '</div>';

        foreach ($data->permohonan->kontrak->periode as $periode) {
            $startDate = convert_date($periode->start_date, 6);
            $endDate = convert_date($periode->end_date, 6);
            $htmlSample .= '<div>' . $data->permohonan->kontrak->jumlah_kontrol . ' + ' . $data->permohonan->kontrak->jumlah_pengguna . ' ' . $startDate . ' - ' . $endDate . '</div>';
        }

        $dokumen = $data->dokumen->first();
        $template = Documents::with('footer', 'header')
                    ->where('id_doc', $dokumen->id_doc_template)
                    ->first();

        $htmlPertanyaan = '';
        foreach ($template->data_pertanyaan as $pertanyaan) {
            $answer = '';
            foreach($dokumen->content_value['alasan'] as $alasan) {
                if($alasan['id'] == $pertanyaan->id_pertanyaan) {
                    $answer = $alasan['answer'];
                }
            }
            $htmlPertanyaan .= '
                <tr>
                    <td>' . $pertanyaan->pertanyaan . '</td>
                    <td style="text-align: center;">' . ($answer == "siap" ? "Ok" : "") . '</td>
                    <td style="text-align: center;">' . ($answer == "siap" ? "" : "X") . '</td>
                </tr>
            ';
        }

        return [
            "RINCIAN" => '
                <table class="table-surattugas" style="margin-top: 15px;">
                    <tr>
                        <th width="1%">No</th>
                        <th width="20%">Nama Sample/Alat</th>
                        <th width="20%">Jenis Pengujian</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>' . $htmlSample . '</td>
                        <td>' . $jenisPengujian . '</td>
                    </tr>
                </table>
            ',
            "RINCIAN_2" => '
                <table class="table-surattugas" style="margin-top: 15px;">
                    <tr>
                        <th width="20%"></th>
                        <th width="5%">Siap</th>
                        <th width="5%">Tidak Siap</th>
                    </tr>
                    ' . $htmlPertanyaan . '
                </table>
            ',
        ];
    }

    public function KontrakPengujian($id) {
        $id = decryptor($id);

        if($id == null){
            return redirect()->back();
        }

        $query = Kontrak::with([
            'dokumen' => function($q){
                $q->where('jenis', 'KontrakPengujian');
            },
            'pelanggan',
            'layanan_jasa',
            'layanan_jasa.satuankerja',
            'jenisTld',
            'periode',
            'periode.penyelia',
            'periode.penyelia.petugas',
            'invoice',
            'invoice.diskon',
            'pelanggan.perusahaan',
            'pelanggan.perusahaan.alamat',
            'rincian_list_tld',
            'rincian_list_tld.pengguna',
            'permohonan',
            'permohonan.lhu',
        ])->where('id_kontrak', $id)->first();

        $data['title'] = "Surat Kontrak Pengujian";
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        // mengambil template dokumen
        $dokumen = $query->dokumen->first();
        $template = Documents::with('footer', 'header')
                    ->where('id_doc', $dokumen->id_doc_template)
                    ->first();

        if($dokumen->variables) {
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
        }

        // mengambil dokumen surat permintaan pengujian
        $permintaan = Permohonan_dokumen::where('id_kontrak', $id)->where('jenis', 'SuratPengujian')->first();

        if($permintaan){
            $variables["HARI_PENGUJIAN"] = convert_date($permintaan->created_at, 8);
            $variables["TGL_PENGUJIAN"] = convert_date($permintaan->created_at, 2);
        }

        // TTD
        if($dokumen->ttd_image) {
            $ttd_manajer = $dokumen->ttd_image;
        } else {
            $ttd_manajer = $dokumen->ttd ?? "";
        }
        $variables['TTD_MANAJER'] = $ttd_manajer ? "
            <div style='text-align: center;'>
                <img src='".$data['stempel']."' class='img-fluid img-stempel' style='margin-left: 15%;' alt='Stempel-Lab'>
                <img src='$ttd_manajer' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY_MANAJER'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        // TTD PELANGGAN
        if($query->pelanggan->ttd_image) {
            $ttd_pelanggan = $query->pelanggan->ttd_image;
        } else {
            $ttd_pelanggan = $query->pelanggan->ttd ?? "";
        }
        $variables['TTD_PELANGGAN'] = $ttd_pelanggan ? "
            <div style='text-align: center;'>
                <img src='$ttd_pelanggan' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY_PELANGGAN'] = $query->pelanggan ? $query->pelanggan->name : '...........................................';

        // Generate PDF
        $bytes = $this->generatePDF($data['title'], $template, $variables, [
            "TTD_MANAJER", "TTD_BY_MANAJER",
            "TTD_PELANGGAN", "TTD_BY_PELANGGAN",
            "RINCIAN", "RINCIAN_2", "RINCIAN_3",
        ]);

        $filename = $dokumen->nama.'-'.now()->format('Ymd-His').'.pdf';

        return $bytes->stream($filename);

    }

    private function contentKontrakPengujian($data, $params = []) {
        $zrcek = $data->is_zerocek ? 'Zero Cek' : '';
        $lJasa = $data->layanan_jasa->satuankerja->name;
        $jTld = $data->jenisTld->name;

        $jenisPengujian = $zrcek . ' ' . $lJasa . ' ' . $jTld;
        $htmlSample = '<div>' . $lJasa . ' ' . $jTld . '</div>';

        foreach ($data->periode as $periode) {
            $startDate = convert_date($periode->start_date, 6);
            $endDate = convert_date($periode->end_date, 6);
            $htmlSample .= '<div>' . $data->jumlah_kontrol . ' + ' . $data->jumlah_pengguna . ' ' . $startDate . ' - ' . $endDate . '</div>';

            if($periode->periode == 1) {
                // dd($periode);
            }
        }

        $dataKeuangan = calculateInvoice($data->total_harga, $data->invoice->diskon, $data->invoice->ppn, $data->invoice->pph);

        // Mengambil personil

        // Mengambil LIST TLD yang digunakan
        $htmlListTld = '';
        $htmlPengguna = '';
        foreach ($data->rincian_list_tld as $key => $value) {
            foreach($value->tld as $tld) {
                $htmlListTld .= '
                    <tr>
                        <td>' . ($key + 1) . '</td>
                        <td>' . $tld->no_seri_tld . '</td>
                        <td>' . ($tld->merk ?? '') . '</td>
                    </tr>
                ';
            }

            if($value->pengguna) {
                $htmlPengguna .= '
                    <li>' . $value->pengguna->name . '</li>
                ';
            }
        }

        return [
            "RINCIAN" => '
                <table class="table-surattugas">
                    <tr>
                        <th width="1%">No</th>
                        <th width="30%">Nama Alat/Sampel</th>
                        <th width="20%">Biaya</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>' . $htmlSample . '</td>
                        <td style="text-align: center">' . formatCurrency($dataKeuangan['subTotal']) . '</td>
                        <td></td>
                    </tr>
                </table>
            ',
            'RINCIAN_2' => '
                <ol>
                    '.$htmlPengguna.'
                </ol>
            ',
            "RINCIAN_3" => '
                <table class="table-surattugas">
                    <tr>
                        <th width="1%">No</th>
                        <th width="30%">Nama Alat</th>
                        <th width="20%">Merk/Tipe</th>
                    </tr>
                    '.$htmlListTld.'
                </table>
            '
        ];
    }

    public function adendum($idPermohonan) {
        $idPermohonan = decryptor($idPermohonan);

        $query = Permohonan::with([
            'jenisTld:id_jenisTld,name',
            'pelanggan',
            'kontrak',
            'kontrak.periode',
            'pelanggan.perusahaan',
            'jenis_layanan:id_jenisLayanan,name',
            'layanan_jasa:id_layanan,nama_layanan',
            'dokumen' => function($query) {
                return $query->where('jenis', 'adendum');
            },
            'dokumen.doc_template',
            'signature:id,name',
            'permohonan_detail',
            'permohonan_detail.entitas' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                ]);
            },
            'permohonan_detail.penggunaLama'
        ])->find($idPermohonan);

        $data['data'] = $query;
        $data['date'] = Carbon::now();
        $data['title'] = "Permohonan Adendum";
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $permohonan_detail = $query->permohonan_detail;
        $data['jenis'] = $permohonan_detail->where('type', 'baru')->first() ? 'Penambahan' : 'Pergantian';
        $data['periode'] = $query->kontrak->periode->where('periode', $query->periode)->first();


        // mengambil template adendum
        $dokumen = $query->dokumen->first();
        $template = $dokumen->doc_template;

        if($dokumen->variables){
            $variables = $dokumen->variables;
        } else {
            $variables = $this->mappingVars($template, $query, $data);
            $dokumen->update(['variables' => $variables]);
        }

        // mengambil header
        $header = Documents::where('id_perusahaan', $query->pelanggan->perusahaan->id_perusahaan)
                    ->where('jenis', 'header')
                    ->where('status', 1)
                    ->where('view', 1)
                    ->first();

        $template->header = $header;
        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, ['TTD', 'RINCIAN_TLD']);

        $filename = $data['title'] . '-' . date('Y-m-d') . '.pdf';

        return $bytes->stream($filename);
    }

    public function contentPermohonanAdendum($data, $params) {
        $html = "";
        foreach($data->permohonan_detail as $key => $value) {
            $col1 = "";
            $col2 = "";
            if($value->type == 'baru') {
                if($value->jenis == 'pengguna') {
                    $col1 = $value->entitas->name;
                    $col2 = '';
                } else {
                    $col1 = "TLD Kontrol " . $key + 1;
                }
            } else {
                $col1 = $value->entitas->name . ' (Pengganti ' . $value->penggunaLama->name . ')';
                $col2 = '';
            }
            $html .= '
                <tr>
                    <td>' . ($key + 1) . '</td>
                    <td>' . $col1 . '</td>
                    <td>' . $col2 . '</td>
                    <td></td>
                </tr>
            ';
        }

        return [
            "RINCIAN_TLD" => '
                <table class="table-surattugas">
                    <tr>
                        <th width="1%">No</th>
                        <th width="30%">Nama Pengguna TLD</th>
                        <th width="10%">Divisi</th>
                        <th width="20%">Zat Radioaktif/Energi yang digunakan</th>
                    </tr>
                    '.$html.'
                </table>
            '
        ];
    }
}
