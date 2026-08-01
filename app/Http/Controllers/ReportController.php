<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;
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
use App\Services\Keuangan\FinancialCalculatorService;
use Auth;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Log;

class ReportController extends Controller
{
    protected array $global;
    protected FinancialCalculatorService $calculator;

    public function __construct()
    {
        $this->global = config('customvariabel') ?? [];
        $this->calculator = resolve(FinancialCalculatorService::class);
    }

    private function generatePDF(string $title, Documents $template, array $variables = [], array $htmlKeys = [], string $css = '')
    {
        $result = array(
            'title' => $title,
        );

        $options = [
            'html_keys' => $htmlKeys,
            'sanitizer' => fn($h, $k) => Purifier::clean($h, 'ckpdf'), // pakai jika ada mews/purifier
            'allowed_tags' => '<p><br><strong><b><em><i><u><span><div><img>',
            'orientation' => $template->orientation,
        ];

        $contentNoFormulir = '
            <p style="font-size: 10px; margin-bottom: -2px">' . $template->no_formulir . '</p>
        ';

        $result['header'] = $template->header ? renderMentionsToValuesFlexible($template->header->content, $variables, $options) : '';
        $result['footer'] = $template->footer ? renderMentionsToValuesFlexible($template->footer->content, $variables, $options) : '';
        $result['no_formulir'] = $template->no_formulir ? renderMentionsToValuesFlexible($contentNoFormulir, $variables, $options) : '';
        $result['body'] = renderMentionsToValuesFlexible($template->content, $variables, $options);
        $result['template_css'] = $css;

        $bytes = Pdf::loadView('report.index', $result);

        return $bytes;
    }
    public function template_default(String $jenis, String $id)
    {
        $idTemplate = decryptor($id);
        $name_template = '';
        $variables = [];
        $htmlKeys = [];
        $template = null;
        $title = '';
        if ($jenis == 'kop_surat') {
            $name_template = 'KopSuratDefault';
            $template = Documents::where('name', $name_template)->first();
            $header = Documents::where('id_doc', $idTemplate)->first();

            $template->header = $header;
            $users = Auth::user();

            $stempel = $users->perusahaan?->stempel_perusahaan;
            $url_stempel = "";
            if ($stempel) {
                $url_stempel = "data:image/png;base64," . base64_encode(file_get_contents(public_path('storage/' . $stempel->file_path . '/' . $stempel->file_hash)));
            }

            $variables['TTD'] = $users->ttd_image ? "
                <div style='text-align: center;'>
                    <img src='$url_stempel' class='img-fluid img-stempel' alt='Stempel-Lab'>
                    <img src='{$users->ttd_image}' alt='TTD_keuangan' width='100px' height='100px'>
                </div>
            " : "<br><br><br>";

            $variables['TTD_BY'] = $users->name;
            $htmlKeys = ['TTD'];
            $title = 'Dummy Kop Surat';
        }

        // generate pdf
        $bytes = $this->generatePDF($title, $template, $variables, $htmlKeys);

        return $bytes->stream('dummy.pdf');
    }
    public function invoice(Request $request, String $id)
    {
        $idKeuangan = decryptor($id);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';

        if ($idKeuangan == null) {
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
            'permohonan.dokumen' => function ($q) {
                $q->where('jenis', 'invoice');
            },
            'metode_pembayaran'
        ])->where('id_keuangan', $idKeuangan)->first();

        if (isset($query->metode_pembayaran)) {
            $query->metode_pembayaran->content = contenMetodePembayaran($query->metode_pembayaran->content, $query->variabel_jenis_pembayaran);
        }

        $JL = jenislayanan($query->permohonan->jenis_layanan_parent, $query->permohonan->jenis_layanan);

        $data['date'] = Carbon::now();
        $data['title'] = "Invoice";
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];
        $data['is_catatan'] = !in_array($JL, $this->global['catatan_invoice']);
        $periodePemakaian = $query->permohonan->periode_pemakaian;

        if ($query->permohonan->tipe_kontrak == 'adendum') {
            $periodePemakaian = $query->permohonan->kontrak->periode->filter(function ($item) use ($query) {
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
            if ($query->permohonan && count($periodePemakaian) > 0) {
                $data['periode_start'] = $periodePemakaian[0];
                $data['periode_end'] = $periodePemakaian[count($periodePemakaian) - 1] ?? null;
                $data['count_periode'] = count($periodePemakaian);
            }
        }


        // mengambil template invoice
        $dokumen = $query->permohonan->dokumen->first();
        if (!$dokumen->id_doc_template) {
            $template = Documents::with(['footer', 'header'])
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
            $template = Documents::with(['footer', 'header'])
                ->where('id_doc', $dokumen->id_doc_template)
                ->first();

            if ($dokumen->variables) {
                $variables = $dokumen->variables;
            } else {
                $variables = $this->mappingVars($template, $query, $data);

                if ($dokumen->ttd) {
                    $dokumen->update([
                        'variables' => $variables
                    ]);
                }
            }
        }
        // TTD Invoice
        if ($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }

        if ($is_download && $type == 'original') {
            $ttd = false;
            $template->header = false;
            $template->footer = false;
        }

        $variables['TTD_IMG'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='" . $data['stempel'] . "' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_keuangan' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $bytes = $this->generatePDF("Invoice", $template, $variables, ['CATATAN_PEMBAYARAN', 'NOTICE', 'TTD_IMG', 'RINCIAN']);
        $filename = 'invoice-' . now()->format('Ymd-His') . '.pdf';

        if ($is_download) {
            return $bytes->download($filename);
        }
        return $bytes->stream($filename);
    }

    public function contentInvoice(mixed $data, ?array $params)
    {
        $dataKeuangan = $this->calculator->calculateInvoice($data->permohonan->total_harga, $data->diskon, $data->ppn, $data->pph);

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
                <td>PPN</td>
                <td>' . formatCurrency($dataKeuangan['jumPpn']) . '</td>
            </tr>';
        }

        $htmlPph = '';
        if ($data->pph) {
            $htmlPph .= '<tr>
                <td>PPH ' . $data->pph . '%</td>
                <td>( - ' . formatCurrency($dataKeuangan['jumPph']) . ' )</td>
            </tr>';
        }

        $subJumlah = '';
        if (count($dataKeuangan['diskon']) > 0) {
            $subJumlah .= '<tr>
                <td>Sub Jumlah</td>
                <td>' . formatCurrency($dataKeuangan['jumAfterDiskon']) . '</td>
            </tr>';
        }

        $result = [
            "TERBILANG" => angkaKeHuruf($dataKeuangan['subTotal']) . ' rupiah',
            "RINCIAN" => '<table class="table-invoice" style="font-size: 12px;font-family: times new roman;">
                    <tr>
                        <td>' . $data->permohonan->jumlah_pengguna + $data->permohonan->jumlah_kontrol . ' Unit
                            ' . $data->permohonan->jenisTld->name . ' x ' . $params['count_periode'] . ' Periode x
                            ' . formatCurrency($data->permohonan->harga_layanan) . '</td>
                        <td>' . formatCurrency($data->permohonan->total_harga) . '</td>
                    </tr>
                    ' . $htmlDiskon . '
                    ' . $subJumlah . '
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

    public function mappingVars(mixed $template, mixed $data, array $params)
    {
        $vars = array();
        $lab_perusahaan = AppSettings::where('key', 'lab_name')->first()->value;
        $lab_alamat = AppSettings::where('key', 'lab_address')->first()->value;
        $lab_lokasi = AppSettings::where('key', 'lab_lokasi')->first()->value;
        $lab_telp = AppSettings::where('key', 'lab_phone')->first()->value;

        switch ($template->name) {
            case 'Invoice':
                $rangeDate = range_date($params['periode_start']['start_date'], $params['periode_end']['end_date'], 2);

                $tglTerbit = $data->permohonan->dokumen[0]->published_at ?? $data->permohonan->dokumen[0]->created_at;
                $vars["NOMOR"] = $data->no_invoice;
                $vars["LAMPIRAN"] = "Faktur Pajak";
                $vars["PERIHAL"] = "Invoice " . $data->permohonan->jenis_layanan_parent->name . " " . $data->permohonan->layanan_jasa->nama_layanan . " " . $data->permohonan->jenisTld->name;
                $vars["LOKASI"] = $lab_lokasi;
                $vars["TANGGAL"] = convert_date($tglTerbit, 2);
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
                if ($data->tipe_kontrak == 'kontrak baru') {
                    $pemakaian = $data->periode_pemakaian;
                    $periode1 = convert_date($pemakaian[0]['start_date'], 7) . " s/d " . convert_date($pemakaian[0]['end_date'], 7);
                    $periode2 = isset($pemakaian[1]) ? convert_date($pemakaian[1]['start_date'], 7) . " s/d " . convert_date($pemakaian[1]['end_date'], 7) : '';

                    if ($data->is_zerocek == 1) {
                        $vars["PERIODE_RINCIAN"] = "- $periode1 <br> - $periode2";
                    } else {
                        $vars["PERIODE_RINCIAN"] = "- $periode1";
                    }
                } else if ($data->tipe_kontrak == 'adendum' && $data->is_zerocek == 1) {
                    $findPeriode1 = $data->kontrak->periode->where('periode', $data->periode)->first();
                    $findPeriode2 = $data->kontrak->periode->where('periode', $data->periode + 1)->first();

                    $startDate1 = $findPeriode1 ? $findPeriode1->start_date : '';
                    if ($startDate1 && $data->bulan_mulai && $data->bulan_mulai > 1) {
                        $startDate1 = \Carbon\Carbon::parse($startDate1)->addMonths($data->bulan_mulai - 1)->toDateTimeString();
                    }

                    $periode1 = $findPeriode1 ? convert_date($startDate1, 7) . " s/d " . convert_date($findPeriode1->end_date, 7) : '';
                    $periode2 = $findPeriode2 ? convert_date($findPeriode2->start_date, 7) . " s/d " . convert_date($findPeriode2->end_date, 7) : '';

                    $vars["PERIODE_RINCIAN"] = "- $periode1" . ($periode2 != '' ? "<br> - $periode2" : '');
                } else {
                    $findPeriode = $data->kontrak->periode->where('periode', $data->periode)->first();
                    $startDate = $findPeriode ? $findPeriode->start_date : '';
                    if ($data->tipe_kontrak == 'adendum' && $startDate && $data->bulan_mulai && $data->bulan_mulai > 1) {
                        $startDate = \Carbon\Carbon::parse($startDate)->addMonths($data->bulan_mulai - 1)->toDateTimeString();
                    }
                    $vars["PERIODE_RINCIAN"] = $findPeriode ? "- " . convert_date($startDate, 7) . " s/d " . convert_date($findPeriode->end_date, 7) : '';
                }
                $vars["JUDUL"] = "TANDA TERIMA PENGUJIAN/KALIBRASI";
                $vars["NOMOR"] = $data->dokumen->first()->nomer;
                $vars["PERUSAHAAN"] = $data->pelanggan?->perusahaan->nama_perusahaan;
                $vars["ALAMAT"] = $data->pelanggan?->perusahaan->alamat[0]->alamat;
                $vars["JENIS_PENGUJIAN"] = $data->is_zerocek == 1 ? 'Zero Check' : 'Evaluasi TLD';
                $vars["JUMLAH"] = $data->jumlah_pengguna . " Pengguna + " . $data->jumlah_kontrol . " Kontrol";
                $textPeriode = "Periode " . $data->periode;
                if ($data->periode > 0) {
                    if ($data->is_zerocek == 1) {
                        $showZeroCekText = true;
                        if ($data->tipe_kontrak === 'adendum') {
                            $hasPenambahan = $data->permohonan_detail()->where('type', 'baru')->exists();
                            if (!$hasPenambahan) {
                                $showZeroCekText = false;
                            }
                        }
                        if ($showZeroCekText) {
                            $textPeriode .= " + Zero Check";
                        }
                    }
                } else {
                    $textPeriode = "Periode Zero Check";
                }
                $vars["PERIODE"] = $textPeriode;
                $vars["TGL_PENERIMAAN"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["TGL_SELESAI"] = $params['selesaiPengujian'] ? convert_date($params['selesaiPengujian'], 2) : '';
                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["LOKASI"] = $lab_lokasi;
                $vars = array_merge($vars, $this->contentTandaTerima($data, $params));
                break;
            case "Kontrak":
                $vars["NOMOR"] = $data->no_kontrak;
                $vars["JENIS_LAYANAN"] = $data->jenis_layanan->name;
                $vars["LAYANAN_JASA"] = $data->layanan_jasa->nama_layanan;
                $vars["JENIS_TLD"] = $data->jenisTld->name;
                $vars["PERUSAHAAN"] = $data->pelanggan?->perusahaan->nama_perusahaan;
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
                $vars["PENGUJIAN"] = $data->periode == 0 && $data->is_zerocek == 1 ? 'Zero Check' : 'Evaluasi TLD';
                $vars["LAYANAN_JASA"] = $data->layanan_jasa->nama_layanan;
                $vars["JENIS_TLD"] = $data->jenisTld->name;
                $vars["PERUSAHAAN"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars["JML_P"] = $data->jumlah_pengguna;
                $vars["JML_K"] = $data->jumlah_kontrol;
                $vars["TGL_MULAI"] = convert_date($data->lhu->start_date, 2);
                $vars["TGL_SELESAI"] = convert_date($data->lhu->end_date, 2);
                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["PERIODE"] = $data->periode == 0 ? 'Zero Check' : "Periode {$data->periode}";
                $vars = array_merge($vars, $this->contentSuratTugas($data, $params));
                break;
            case "SuratPengantar":
                $zerocek = '';
                if (isset($params['permohonan']) && $params['permohonan']->is_zerocek == 1) {
                    $zerocek = " & Hasil Zero Check";
                }
                $start_date = $data->periode_next ? $data->periode_next[0]['start_date'] : $params['kontrak_periode']->start_date;
                $end_date = $data->periode_next ? $data->periode_next[0]['end_date'] : $params['kontrak_periode']->end_date;

                if (isset($params['permohonan']) && $params['permohonan']->tipe_kontrak == 'adendum') {
                    $bulanMulai = $params['permohonan']->bulan_mulai;
                    if ($bulanMulai && $bulanMulai > 1) {
                        $start_date = \Carbon\Carbon::parse($start_date)->addMonths($bulanMulai - 1)->toDateTimeString();
                    }
                }

                $vars["NOMOR"] = $params["dokumen"]->nomer;
                $vars["PERIODE_AWAL"] = convert_date($start_date, 6);
                $vars["PERIODE_SELESAI"] = convert_date($end_date, 6);
                if ($data->periode_next) {
                    $vars['PERIODE_NOW'] = "periode berikutnya";
                } else {
                    $vars["PERIODE_NOW"] = $params['kontrak_periode']->status == 2 ? "periode pengembalian" : "periode {$params['kontrak_periode']->periode}";
                }
                $vars["TGL_BUAT"] = convert_date($params["dokumen"]->created_at, 2);

                $vars["LAYANAN_JASA"] = $data->layanan_jasa->nama_layanan;
                $vars["ZEROCEK"] = $zerocek;
                $vars["JENIS_TLD"] = $data->jenisTld->name;
                $vars["PETUGAS"] = $data->pelanggan->name;
                $vars["ALAMAT"] = $data->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["TELEPON"] = $data->pelanggan->profile->no_hp;
                $vars["PETUGAS_DIVISI"] = $data->pelanggan->jabatan;
                $vars["NO_KONTRAK"] = $data->no_kontrak;
                $vars["PERUSAHAAN"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars = array_merge($vars, $this->contentSuratPengantar($data, $params));
                break;
            case "Kwitansi":
                $rangeDate = range_date($params['periode_start'], $params['periode_end'], 2);
                $type = ($data->status == 5 ? 'L' : '') .
                    ($data->pph ? 'PH' : '') .
                    ($data->ppn ? 'N' : '');

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
                // $vars["TGL_BUAT"] = convert_date($data->permohonan->kontrak->dokumen[0]->created_at, 2);
                $vars["TGL_BUAT"] = convert_date($data->paid_at, 2);
                $vars["NO_KONTRAK"] = $data->permohonan->kontrak->no_kontrak;
                $vars["LOKASI_BUAT"] = $lab_lokasi;
                $vars["TYPE"] = $params['catatan'] ?? $type;
                $vars = array_merge($vars, $this->contentKwitansi($data, $params));
                break;
            case "SuratPengujian":
                $vars["JUDUL"] = "PERSETUJUAN TERHADAP PERMINTAAN PENGUJIAN";
                $vars["NOMOR"] = $data->dokumen[0]->nomer;
                $vars["PERUSAHAAN"] = $data->permohonan->pelanggan->perusahaan->nama_perusahaan;
                $vars["ALAMAT"] = $data->permohonan->pelanggan->perusahaan->alamat[0]->alamat;
                $vars["LOKASI_BUAT"] = $lab_lokasi;
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
                $vars["LOKASI"] = $lab_lokasi;
                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["UNIT"] = $data->layanan_jasa->satuankerja->name;
                if ($data->permohonan->lhu->end_date) {
                    $vars['TGL SELESAI'] = convert_date($data->permohonan->lhu->end_date, 2);
                }
                $vars = array_merge($vars, $this->contentKontrakPengujian($data, $params));
                break;
            case "PermohonanAdendum":
                // ["TGL_BUAT","NO_SURAT","PERIHAL","TUJUAN","ALAMAT_TUJUAN","PERUSAHAAN","LAYANAN_JASA","JENIS_TLD","JENIS_LAYANAN","NO_KONTRAK","JUMLAH_TLD","PERIODE","RINCIAN_TLD","TTD","TTD_BY","TELEPON","LOKASI"]

                $vars["PERIHAL"] = "Permohonan {$params['jenis']} {$data->jenis_layanan->name} TLD";

                $vars["TGL_BUAT"] = convert_date($data->dokumen[0]->created_at, 2);
                $vars["NO_SURAT"] = $data->dokumen[0]->nomer;
                $vars['TUJUAN'] = $lab_perusahaan;
                $vars['ALAMAT_TUJUAN'] = $lab_alamat;
                $vars['TELEPON'] = $lab_telp;
                $vars['PERUSAHAAN'] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars['JENIS_LAYANAN'] = $data->jenis_layanan->name;
                $vars['JENIS_TLD'] = $data->jenisTld->name;
                $vars['LAYANAN_JASA'] = $data->layanan_jasa->nama_layanan;
                $vars['NO_KONTRAK'] = $data->kontrak->no_kontrak;
                $vars['JUMLAH_TLD'] = $data->jumlah_pengguna + $data->jumlah_kontrol;

                $startDate = $params['periode']->start_date;
                if ($data->bulan_mulai && $data->bulan_mulai > 1) {
                    $startDate = \Carbon\Carbon::parse($startDate)->addMonths($data->bulan_mulai - 1)->toDateTimeString();
                }
                $range = range_date($startDate, $params['periode']->end_date, 1);
                $vars['PERIODE'] = $range['start'] . '-' . $range['end'];
                $vars['LOKASI'] = $data->pelanggan->perusahaan->alamat[0]->kota;

                $vars['TTD'] = "
                    <div style='text-align: center;'>
                        <img src='{$data->pelanggan->ttd_image}' alt='TTD' width='100px' height='100px'>
                    </div>
                ";
                $vars['TTD_BY'] = $data->pelanggan->name;
                $vars = array_merge($vars, $this->contentPermohonanAdendum($data, $params));
                break;
            case 'PermohonanEvaluasi':
                // ["PERUSAHAAN","ALAMAT","NO_TELP","PERUSAHAAN_P","KODE_P","ALAMAT_P","NAMA_P","NO_HP_P","JML_TLD_P","JML_TLD_K","PERIODE","CONTENT","KOTA","TANGGAL","TTD","TTD_BY"]
                $vars["PERUSAHAAN"] = $lab_perusahaan;
                $vars["ALAMAT"] = $lab_alamat;
                $vars["NO_TELP"] = $lab_telp;
                $vars["PERUSAHAAN_P"] = $data->pelanggan->perusahaan->nama_perusahaan;
                $vars["ALAMAT_P"] = $data->alamat->alamat;
                $vars["KODE_P"] = $data->pelanggan->perusahaan->kode_perusahaan;
                $vars["NAMA_P"] = $data->pelanggan->name;
                $vars["NO_HP_P"] = $data->pelanggan->profile->no_hp;
                $vars["KOTA"] = $data->alamat->kota;
                $vars["JML_TLD_P"] = $data->jumlah_pengguna;
                $vars["JML_TLD_K"] = $data->jumlah_kontrol;

                $range = range_date($data->periode_pemakaian[0]['start_date'], $data->periode_pemakaian[0]['end_date'], 2);
                $vars["PERIODE"] = $range['start'] . '-' . $range['end'];

                // ambil stempel jika ada
                $stempel = $data->pelanggan->perusahaan?->stempel_perusahaan;
                $url_stempel = "";
                if ($stempel) {
                    $url_stempel = "data:image/png;base64," . base64_encode(file_get_contents(public_path('storage/' . $stempel->file_path . '/' . $stempel->file_hash)));
                }
                $vars["TTD"] = "
                    <div style='text-align: center;'>
                        <img src='$url_stempel' class='img-fluid img-stempel' alt='Stempel-Lab'>
                        <img src='{$data->pelanggan->ttd_image}' alt='TTD' width='100px' height='100px'>
                    </div>
                ";
                $vars["TTD_BY"] = $data->pelanggan->name;
                $vars["TANGGAL"] = convert_date($data->created_at, 13);
                $vars = array_merge($vars, $this->contentPermohonanEvaluasi($data, $params));
                break;
            default:
                # code...
                break;
        }

        // cek apakah ada yang kurang dari template
        $keys = array_keys($vars);
        $missing = array_diff($template->variables ?? [], $keys);
        if (count($missing) > 0) {
            $vars = array_merge($vars, array_fill_keys($missing, ""));
        }
        return $vars;
    }

    public function kwitansi(Request $request, String $id)
    {
        $idKeuangan = decryptor($id);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';

        if ($idKeuangan == null) {
            return redirect()->back();
        }

        $query = keuangan::with([
            'permohonan',
            'permohonan.jenis_layanan',
            'permohonan.pelanggan',
            'permohonan.pelanggan.perusahaan',
            'permohonan.kontrak',
            'permohonan.kontrak.periode',
            'permohonan.kontrak.dokumen' => function ($q) {
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
        if ($query->permohonan->tipe_kontrak == 'adendum') {
            $periodePemakaian = $query->permohonan->kontrak->periode->filter(function ($item) use ($query) {
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
        if ($dokumen) {
            $data['catatan'] = $dokumen->catatan;
            $template = $dokumen->doc_template;

            if ($dokumen->variables) {
                $variables = $dokumen->variables ?? [];
            } else {
                $variables = $this->mappingVars($template, $query, $data);
            }
        } else {
            $template = null;
            $variables = [];
        }

        // TTD KWITANSI
        if ($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }

        if ($is_download && $type == 'original') {
            $ttd = false;
            $template->header = false;
            $template->footer = false;
        }

        $variables['TTD'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='" . $data['stempel'] . "' class='img-fluid img-stempel' style='' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $bytes = $this->generatePDF("Kwitansi", $template, $variables, ['RINCIAN', 'TTD']);
        $filename = 'kwitansi-' . now()->format('Ymd-His') . '.pdf';

        if ($is_download) {
            return $bytes->download($filename);
        }

        return $bytes->stream($filename);
    }

    private function contentKwitansi(Keuangan $data, ?array $params = null)
    {
        $tbl_rincian = "";
        $dataKeuangan = $this->calculator->calculateInvoice($data->permohonan->total_harga, $data->diskon, $data->ppn, $data->pph);

        foreach ($dataKeuangan['diskon'] as $item) {
            $tbl_rincian .= '
                <tr>
                    <td width="20%"></td>
                    <td><p class="lh-16">' . $item->name . ' ' . $item->diskon . '%</p></td>
                    <td style="text-align: right">' . formatCurrency($item->jumDiskon) . ' ,-</td>
                </tr>
            ';
        }

        if ($data->pph) {
            $tbl_rincian .= '
                <tr>
                    <td width="20%"></td>
                    <td><p class="lh-16">PPH ' . $data->pph . '%</p></td>
                    <td style="text-align: right">- ' . formatCurrency($dataKeuangan['jumPph']) . ' ,-</td>
                </tr>
            ';
        }

        if ($data->ppn) {
            $tbl_rincian .= '
                <tr>
                    <td width="20%"></td>
                    <td><p class="lh-16">PPN</p></td>
                    <td style="text-align: right">' . formatCurrency($dataKeuangan['jumPpn']) . ' ,-</td>
                </tr>
            ';
        }

        return [
            "RINCIAN" => '
                <table class="table-kwitansi" border="0">
                    ' . $tbl_rincian . '
                </table>
            ',
            "HARGA_TOTAL" => formatCurrency($dataKeuangan['subTotal']),
            "HARGA_TOTAL_HRF" => angkaKeHuruf($dataKeuangan['subTotal']) . ' rupiah',
        ];
    }

    public function tandaTerima(Request $request, String $idPermohonan)
    {
        $idPermohonan = decryptor($idPermohonan);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';

        if ($idPermohonan == null) {
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
            'dokumen' => function ($query) {
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
        $template = Documents::with(['footer', 'header'])
            ->where('id_doc', $dokumen->id_doc_template)
            ->first();
        if ($dokumen->variables) {
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
            if ($query->lhu && $query->lhu->end_date) {
                $variables['SELESAI_PENGUJIAN'] = convert_date($query->lhu->end_date, 6);
                $dokumen->update(['variables' => $variables]);
            }
        }

        // TTD Invoice
        if ($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }

        $ttd_pemohon = $query->pelanggan->ttd_image ?? "";

        if ($is_download && $type == 'original') {
            $ttd = false;
            $ttd_pemohon = false;
            $template->header = false;
            $template->footer = false;
        }

        $variables['TTD_PENERIMA'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='" . $data['stempel'] . "' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_PENERIMA_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $variables['TTD_PEMOHON'] = $ttd_pemohon ? "
            <div style='text-align: center;'>
                <img src='$ttd_pemohon' alt='TTD_PEMOHON' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_PEMOHON_BY'] = $query->pelanggan ? $query->pelanggan->name : '...........................................';

        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, ['RINCIAN', 'TTD_PENERIMA', 'TTD_PEMOHON', 'PERIODE_RINCIAN'], 'tandaterima');

        $filename = $dokumen->nama . '-' . now()->format('Ymd-His') . '.pdf';

        if ($is_download) {
            return $bytes->download($filename);
        }
        return $bytes->stream($filename);
    }

    private function contentTandaTerima(Permohonan $data, $params = null)
    {
        $tdContent = '';
        $getPertanyaan = [];
        foreach ($data->tandaterima as $key => $value) {
            array_push($getPertanyaan, $value->pertanyaan);
        }
        $half = (int) ceil(count($getPertanyaan) / 2);
        $no = 'a';
        for ($i = 0; $i < $half; $i++) {
            $tdContent .= '<tr>';
            // kolom kiri
            if (isset($getPertanyaan[$i])) {
                $question = $getPertanyaan[$i]->pertanyaan;
                $answer = $data->tandaterima[$i]->jawaban;

                if ($getPertanyaan[$i]->type == 1) {
                    $tdContent .= '
                            <td width="1%" class="text-center">' . $no++ . '.</td>
                            <td>
                                ' . $getPertanyaan[$i]->pertanyaan . ' :<br>
                                <span class="text-secondary">' . $data->tandaterima[$i]->jawaban . '</span>
                            </td>
                        ';
                } else {
                    $tdContent .= '
                            <td colspan="2">
                                ' . $getPertanyaan[$i]->pertanyaan . ' : <span class="text-secondary">' . $data->tandaterima[$i]->jawaban . '</span><br>
                                Bila cacat, sebutkan : ' . $data->tandaterima[$i]->note . '
                            </td>
                        ';
                }
            }

            // kolom kanan

            if (isset($getPertanyaan[$i + $half])) {
                $question = $getPertanyaan[$i + $half]->pertanyaan;
                $answer = $data->tandaterima[$i + $half]->jawaban;

                if ($getPertanyaan[$i + $half]->type == 1) {
                    $tdContent .= '
                            <td width="1%" class="text-center">' . $no++ . '.</td>
                            <td>
                                ' . $getPertanyaan[$i + $half]->pertanyaan . ' :<br>
                                <span class="text-secondary">' . $data->tandaterima[$i + $half]->jawaban . '</span>
                            </td>
                        ';
                } else {
                    $tdContent .= '
                            <td colspan="2">
                                ' . $getPertanyaan[$i + $half]->pertanyaan . ' : <span class="text-secondary">' . $data->tandaterima[$i + $half]->jawaban . '</span><br>
                                Bila cacat, sebutkan : ' . $data->tandaterima[$i + $half]->note . '
                            </td>
                        ';
                }
            }
            $tdContent .= '</tr>';
        }
        $jenisPengujian = $data->is_zerocek == 1 ? 'Zero Check' : 'Evaluasi TLD';

        return [
            "RINCIAN" => '
                <table class="table-tandaterima content-table ck-table-resized" border="1">
                    <tbody>
                        <tr>
                            <td colspan="4">Jenis Pengujian/Kalibrasi: <span class="text-secondary">' . $jenisPengujian . '</span></td>
                        </tr>
                        ' . $tdContent . '
                    </tbody>
                </table>
            '
        ];
    }

    public function suratTugas(Request $request, String $id)
    {
        $id = decryptor($id);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';

        if ($id == null) {
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
            'dokumen' => function ($query) {
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
        $template = Documents::with(['footer', 'header'])
            ->where('id_doc', $dokumen->id_doc_template)
            ->first();

        if ($dokumen->variables) {
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);

            if ($dokumen->ttd) {
                $dokumen->update([
                    'variables' => $variables
                ]);
            }
        }

        // TTD Surat Tugas
        if ($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }

        if ($is_download && $type == 'original') {
            $ttd = false;
            $template->header = false;
            $template->footer = false;
        }

        $variables["TTD"] = $ttd ? "
            <div style='text-align: center;'>
                <img src='" . $data['stempel'] . "' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables["TTD_BY"] = $dokumen->usersig ? $dokumen->usersig->name : ".....................";

        // generate pdf
        $bytes = $this->generatePdf($data['title'], $template, $variables, ["TTD", "RINCIAN"], 'surattugas');

        $filename = $dokumen->nama . '-' . now()->format('Ymd-His') . '.pdf';

        if ($is_download) {
            return $bytes->download($filename);
        }
        return $bytes->stream($filename);
    }

    private function contentSuratTugas(Permohonan $data, $params)
    {
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
                <td style="text-align: center;">' . $no . '</td>
                <td>' . $value[0]['name'] . '</td>
                <td>' . implode(', ', array_column($value, 'jobs')) . '</td>
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
     * @param int|null $periode_ The contract period.
     * @return \Illuminate\Http\Response The PDF stream response.
     */

    public function suratPengantar(Request $request, $id  = null, $periode_ = null)
    {
        $id = decryptor($id);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';
        $isAdendum = $request->get('adendum') ? $request->get('adendum') : null;

        if ($id == null) {
            return redirect()->back();
        }
        // Tentukan id_kontrak berdasarkan konteks (adendum vs normal)
        if ($isAdendum == 1) {
            // $id adalah id_permohonan, ambil id_kontrak dari relasi Permohonan
            $id_permohonan = $id;
            $dataPermohonan = Permohonan::find($id_permohonan);
            $id_kontrak = $dataPermohonan?->id_kontrak;
            $periode_ = $dataPermohonan?->periode ?? $periode_;
        } else {
            // $id adalah id_kontrak
            $id_kontrak = $id;
            $id_permohonan = Kontrak_periode::select('id_permohonan')
                ->where('id_kontrak', $id_kontrak)
                ->where('periode', $periode_)
                ->first()?->id_permohonan;
        }
        $periode = $periode_ == 0 ? 1 : $periode_;

        // Ambil data Kontrak — selalu berdasarkan id_kontrak
        $query = Kontrak::with([
            'jenisTld:id_jenisTld,name',
            'pelanggan',
            'pelanggan.perusahaan',
            'pelanggan.profile',
            'pelanggan.perusahaan.alamat',
            'layanan_jasa:id_layanan,nama_layanan',
            'jenis_layanan:id_jenisLayanan,name',
            'signature:id,name',
            'periode',
        ])->find($id_kontrak);

        // Ambil periode kontrak saat ini (berdasarkan id_kontrak)
        $kontrakPeriode = Kontrak_periode::where('id_kontrak', $id_kontrak)->where('periode', $periode)->first();

        // Ambil dokumen surat pengantar
        if ($isAdendum == 1) {
            // Untuk adendum, dokumen surpeng diambil berdasarkan id_permohonan
            $dokumen = Permohonan_dokumen::where('id_permohonan', $id_permohonan)
                ->where('jenis', 'surpeng')
                ->first();
        } else {
            $dokumen = Permohonan_dokumen::where('id_kontrak', $id_kontrak)
                ->when($query->periode_next, function ($q) use ($periode_) {
                    return $q->whereNull('periode');
                }, function ($q) use ($periode_) {
                    return $q->where('periode', $periode_);
                })
                ->where('jenis', 'surpeng')
                ->first();
        }

        $template = Documents::with(['footer', 'header'])
            ->where('jenis', 'body')
            ->where('name', 'SuratPengantar')
            ->where('status', '1')
            ->first();

        if ($kontrakPeriode && $kontrakPeriode->nomer_surpeng == null) {
            $noSurpeng = generateNoDokumen('surpeng');
            $kontrakPeriode->nomer_surpeng = $noSurpeng;
            $kontrakPeriode->created_surpeng_at = Carbon::now()->format('Y-m-d');
            $kontrakPeriode->save();

            if (!$dokumen) {
                // $textPeriode = $kontrakPeriode->status == 2 ? "Pengembalian" : "Periode $periode";
                // $dokumen = Permohonan_dokumen::create(array(
                //     'periode' => $periode_,
                //     'id_kontrak' => $id,
                //     'id_permohonan' => $id_permohonan ?? null,
                //     'id_doc_template' => $template->id_doc,
                //     'jenis' => "surpeng",
                //     "nama" => "Surat Pengantar",
                //     "nomer" => $noSurpeng,
                //     "created_by" => Auth::user()->id,
                //     "status" => 1
                // ));
            } else {
                $dokumen->nomer = $noSurpeng;
                $dokumen->save();
            }
        }

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Surat Pengantar';
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        $data["dokumen"] = $dokumen;
        $data["kontrak_periode"] = $kontrakPeriode ?? null;
        $data['periode'] = $periode;

        if ($id_permohonan) {
            $data['permohonan'] = Permohonan::find($id_permohonan);
        }
        if (isset($dokumen) && $dokumen->variables) {
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
        }

        $ttd = null;
        if ($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        }

        if ($is_download && $type == 'original') {
            $ttd = false;
            $template->header = false;
            $template->footer = false;
        }

        $variables["TTD"] = $ttd ? "
            <div style='text-align: center;'>
                <img src='" . $data['stempel'] . "' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_keuangan' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables["TTD_BY"] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, ["RINCIAN", "TTD", "JML_K"]);

        $filename = $dokumen->nama . '-' . now()->format('Ymd-His') . '.pdf';

        if ($is_download) {
            return $bytes->download($filename);
        }
        return $bytes->stream($filename);
    }

    private function contentSuratPengantar(mixed $data, array $params)
    {
        $html = '';
        $no = 1;
        $countKontrol = 0;
        $isAdendum = isset($params['permohonan']) && $params['permohonan']->tipe_kontrak == 'adendum';

        if ($isAdendum) {
            $kontrakDetail = \App\Models\Permohonan_detail::with([
                'divisiSelected',
                'entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['divisi']
                    ]);
                }
            ])
                ->where('id_permohonan', $params['permohonan']->id_permohonan)
                ->get();
        } else {
            $kontrakDetail = Kontrak_detail::with([
                'divisiSelected',
                'entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['divisi']
                    ]);
                }
            ])
                ->where('id_kontrak', $data->id_kontrak)
                ->where('status', 1)
                ->get();

            // jika ada dendum yang baru akan di masukkan ke kontrak detail dengan jenis kontrol, jadi untuk menampilkan di surat pengantar hanya yang jenis pengguna saja, sedangkan yang jenis kontrol akan dihitung jumlahnya saja
            $detailAdendum = Kontrak_detail::with([
                'divisiSelected',
                'penggunaLama',
                'entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['divisi']
                    ]);
                }
            ])
                ->where('id_kontrak', $data->id_kontrak)
                ->where('status', 2)
                ->where('periode', $params['periode'])
                ->get();

            foreach ($detailAdendum as $value) {
                if ($value->jenis == 'pengguna') {
                    if ($value->type == 'ganti') {
                        $index = $kontrakDetail->search(function ($item) use ($value) {
                            return $item->id_pengguna_divisi == $value->pengguna_lama;
                        });

                        if ($index !== false) {
                            $kontrakDetail[$index] = $value;
                        }
                    } else {
                        $kontrakDetail->push($value);
                    }
                }
            }
        }

        $countPengguna = 0;
        foreach ($kontrakDetail as $value) {
            if ($value->jenis == 'pengguna') {
                $htmlDesc = $value->type ?? '';
                if ($value->type == 'baru' && isset($params['periode']) && $params['periode'] > 2 && !$isAdendum && $value->status == 1) {
                    $htmlDesc = '';
                }
                if ($value->type == 'ganti') {
                    $htmlDesc = ' (Pengganti ' . $value->penggunaLama->name . ')';
                }

                $divStr = $value->divisiSelected?->name ?? ($value->entitas?->divisi?->name ?? '');
                $kodeStr = $value->kode_lencana_selected ?? ($value->entitas?->kode_lencana ?? '');
                $subInfo = [];
                if ($divStr && $divStr !== '-') $subInfo[] = "Divisi: {$divStr}";
                if ($kodeStr && $kodeStr !== '-') $subInfo[] = "Kode Lencana: {$kodeStr}";
                $infoText = count($subInfo) > 0 ? implode(' | ', $subInfo) : '';
                $infoHtml = $infoText ? '<div style="font-size: 7.5pt; color: #555; font-weight: normal;">' . $infoText . '</div>' : '';

                $html .= '
                    <tr>
                        <td class="text-center">' . $no++ . '.</td>
                        <td style="padding-left: 5px">
                            <div>' . $value->entitas->name . '</div>
                            ' . $infoHtml . '
                        </td>
                        <td style="padding-left: 5px">' . $htmlDesc . '</td>
                    </tr>
                ';
                $countPengguna++;
            } else {
                $countKontrol++;
            }
        }

        $htmlKontrol = '';
        if ($countKontrol > 0) {
            $htmlKontrol = '
                <tr>
                    <td class="text-center">' . $no . '.</td>
                    <td style="padding-left: 5px">TLD Kontrol</td>
                    <td style="padding-left: 5px" class="fw-bold">' . $countKontrol . ' Buah</td>
                </tr>
            ';
        }

        return [
            "RINCIAN" => '
                <table class="table-surattugas" style="margin-top: 15px;">
                    <tr>
                        <th width="1%">No</th>
                        <th width="30%">Nama Pemakai TLD</th>
                        <th width="30%">Keterangan</th>
                    </tr>
                    ' . $html . '
                    ' . $htmlKontrol . '
                </table>
            ',
            'JML_UNIT' => $countPengguna + $countKontrol,
            'JML_P' => $countPengguna,
            'JML_K' => $countKontrol != 0 ? "beserta <b>$countKontrol buah TLD Kontrol</b>" : '',
        ];
    }

    public function kontrak(Request $request, $id = null)
    {
        $id = decryptor($id);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';

        if ($id == null) {
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
            'periode' => function ($query) {
                return $query->whereNotNull('id_permohonan')->orWhereNotNull('nomer_surpeng')->orderBy('periode', 'desc');
            }
        ])->find($id);

        $listTld = Kontrak_detail::with([
            'divisiSelected',
            'entitas' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                ]);
            }
        ])->where('id_kontrak', $query->id_kontrak)->get();

        // Memisahkan radiasi yang digunakan
        $listRadiasi = array();
        foreach ($listTld as $key => $tld) {
            if ($tld->jenis == 'pengguna') {
                foreach ($tld->entitas->radiasi as $item) {
                    $listRadiasi[$item->id_radiasi] = $item;
                }
            }
        }

        // mengambil invoice dari permohonan pertama
        $kontrakPeriode = Kontrak_periode::with(['permohonan', 'permohonan.invoice'])->where('id_kontrak', $id)->orderBy('periode', 'asc')->first();
        $invoice = false;
        if ($kontrakPeriode) {
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
        $template = Documents::with(['footer', 'header'])
            ->where('id_doc', $dokumen->id_doc_template)
            ->first();
        if ($dokumen->variables) {
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
            // kondisi jika invoice belum dibuat
            // 1 = Pengajuan
            // 2 = TTD General Manager
            if (!in_array($invoice->status, [1, 2])) {
                $total = $query->total_harga + ($query->total_harga * ($invoice->ppn / 100));
                $variables['INVOICE'] = "
                    " . $variables['JML_UNIT'] . " buah " . $variables['LAYANAN_JASA'] . " " . $variables['JENIS_TLD'] . " x " . $variables['PERIODE_JML'] . " Periode x " . formatCurrency($query->harga_layanan) . ",- = " . formatCurrency($query->total_harga) . ",-
                    ditambah PPN " . $invoice->ppn . "% total biaya yang harus dibayar sebesar " . formatCurrency($total) . ",-
                ";
                $variables["INVOICE_HRF"] = angkaKeHuruf($total) . " rupiah";
                $dokumen->update(['variables' => $variables]);
            }
        }

        // TTD
        if ($query->pelanggan->ttd_image) {
            $ttd_1 = $query->pelanggan->ttd_image;
        } else {
            $ttd_1 = $query->pelanggan->ttd ?? "";
        }

        // TTD PIHAK 2
        if ($dokumen->ttd_image) {
            $ttd_2 = $dokumen->ttd_image;
        } else {
            $ttd_2 = $dokumen->ttd ?? "";
        }

        if ($is_download && $type == 'original') {
            $ttd_1 = false;
            $ttd_2 = false;
            $template->header = false;
            $template->footer = false;
        }

        $variables["TTD_1"] = $ttd_1 ? "
            <div style='text-align: center;'>
                <img src='$ttd_1' alt='TTD PIHAK 1' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";

        $variables["TTD_2"] = $ttd_2 ? "
            <div style='text-align: center;'>
                <img src='" . $data['stempel'] . "' class='img-fluid img-stempel' alt='Stempel-Lab'>
                <img src='$ttd_2' alt='TTD PIHAK 2' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";

        // generate pdf kontrak (portrait)
        $pdfKontrak = $this->generatePDF($data['title'], $template, $variables, ['TTD_1', 'TTD_2']);

        // generate pdf lampiran pekerja radiasi (landscape)
        $lab_lokasi = AppSettings::where('key', 'lab_lokasi')->first()->value;
        $data['kontrak'] = $query;
        $data['signature'] = $variables["TTD_2"];
        $data['nama_signature'] = $variables["NAMA_MANAGER"];
        $data['lokasi'] = $lab_lokasi;
        $data['date'] = convert_date($dokumen->created_at, 2);
        $pdfLampiran = Pdf::loadView('report.dataPekerjaRadiasi', $data)->setPaper('A4', 'landscape');

        // Gabungkan PDF menggunakan FPDI
        $tempKontrak = tempnam(sys_get_temp_dir(), 'kontrak');
        $tempLampiran = tempnam(sys_get_temp_dir(), 'lampiran');

        file_put_contents($tempKontrak, $pdfKontrak->output());
        file_put_contents($tempLampiran, $pdfLampiran->output());

        $pdf = new \setasign\Fpdi\Fpdi();

        foreach ([$tempKontrak, $tempLampiran] as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], $size);
                $pdf->useTemplate($templateId);
            }
        }

        @unlink($tempKontrak);
        @unlink($tempLampiran);

        $filename = $dokumen->nama . '-' . now()->format('Ymd-His') . '.pdf';
        $output = $pdf->Output('S');

        if ($is_download) {
            return response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        }

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    public function label(string $id, Request $request)
    {
        $id = decryptor($id);
        $is_download = $request->get('dl') ? true : false;

        if ($id == null) {
            return redirect()->back();
        }

        $query = Penyelia::with([
            'permohonan',
            'permohonan.kontrak',
            'permohonan.pelanggan.perusahaan',
        ])->where('id_penyelia', $id)->first();

        // mengambil list tld di kontrak
        $periodeNow = null;
        $alias = null;

        if ($query->permohonan->jenis_layanan_1 == 4 && $query->permohonan->jenis_layanan_2 == 6) {
            $periodeNow = $query->permohonan->periode_next[0];
            $alias = 'ZC';
        } else {
            $periode_label = $query->periode_used;
            if ($query->periode_used == 0) {
                $periode_label = 1;
            }
            $getKperiode = Kontrak_periode::where('id_kontrak', $query->id_kontrak)->where('periode', $periode_label)->first();
            $periodeNow = $getKperiode->toArray();

            $alias = substr($query->permohonan->kontrak->no_kontrak, 0, 1);
        }

        $isAdendum = $query->permohonan && $query->permohonan->tipe_kontrak == 'adendum';

        if ($isAdendum && $periodeNow) {
            $bulanMulai = $query->permohonan->bulan_mulai;
            if ($bulanMulai && $bulanMulai > 1) {
                if (is_array($periodeNow)) {
                    $periodeNow['start_date'] = \Carbon\Carbon::parse($periodeNow['start_date'])->addMonths($bulanMulai - 1)->toDateTimeString();
                } else if (is_object($periodeNow)) {
                    $periodeNow->start_date = \Carbon\Carbon::parse($periodeNow->start_date)->addMonths($bulanMulai - 1)->toDateTimeString();
                }
            }
        }

        if ($isAdendum) {
            $listTld = \App\Models\Permohonan_detail::with([
                'entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                }
            ])
                ->where('id_permohonan', $query->id_permohonan)
                ->get();
        } else {
            $listTld = Kontrak_detail::with([
                'entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'tld_1',
                'tld_2'
            ])
                ->where('id_kontrak', $query->id_kontrak)
                ->where('status', 1)
                ->get();
        }

        $data = array();

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Label';
        $data['data'] = json_decode($listTld);
        $data['penyelia'] = $query;
        $data['periode'] = $periodeNow;
        $data['alias'] = $alias;

        $pdf = PDF::loadView('report.label', $data);
        $pdf->setPaper('a4', 'landscape');
        $pdf->render();

        if ($is_download) {
            $filename = 'label-' . now()->format('Ymd-His') . '.pdf';
            return $pdf->download($filename);
        }

        return $pdf->stream();
    }

    /**
     * Mencetak persetujuan pengujian.
     *
     * @param  int  $idPermohonan  ID permohonan
     * @return \Illuminate\Http\Response
     */
    public function SuratPengujian(Request $request, $idPermohonan)
    {
        $idPermohonan = decryptor($idPermohonan);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';
        if ($idPermohonan == null) {
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
            'dokumen' => function ($q) {
                $q->where('jenis', 'SuratPengujian');
            }
        ])->where('id_permohonan', $idPermohonan)->first();

        // mengambil dokumen surat permintaan pengujian

        $data['date'] = Carbon::now()->year;
        $data['title'] = 'Persetujuan Pengujian';
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        $dokumen = $query->dokumen->first();
        $template = Documents::with(['footer', 'header'])
            ->where('id_doc', $dokumen->id_doc_template)
            ->first();

        if ($dokumen->variables) {
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
        }

        // TTD
        if ($dokumen->ttd_image) {
            $ttd = $dokumen->ttd_image;
        } else {
            $ttd = $dokumen->ttd ?? "";
        }

        if ($is_download && $type == 'original') {
            $ttd = false;
            $template->header = false;
            $template->footer = false;
        }

        $variables['TTD'] = $ttd ? "
            <div style='text-align: center;'>
                <img src='" . $data['stempel'] . "' class='img-fluid img-stempel' style='margin-left: 15%;' alt='Stempel-Lab'>
                <img src='$ttd' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY'] = $dokumen->usersig ? $dokumen->usersig->name : '';
        // generate pdf
        $bytes = $this->generatePdf($data['title'], $template, $variables, ["RINCIAN", "RINCIAN_2", "TTD"]);

        $filename = $dokumen->nama . '-' . now()->format('Ymd-His') . '.pdf';
        if ($is_download) {
            return $bytes->download($filename);
        }
        return $bytes->stream($filename);
    }

    private function contentSuratPengujian(mixed $data, array $params)
    {
        $zrcek = $data->permohonan->is_zerocek ? 'Zero Check' : '';
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
        $template = Documents::with(['footer', 'header'])
            ->where('id_doc', $dokumen->id_doc_template)
            ->first();

        $htmlPertanyaan = '';
        foreach ($template->data_pertanyaan as $pertanyaan) {
            $answer = '';
            foreach ($dokumen->content_value['alasan'] as $alasan) {
                if ($alasan['id'] == $pertanyaan->id_pertanyaan) {
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

    public function PermohonanEvaluasi(Request $request, string $idPermohonan)
    {
        $idPermohonan = decryptor($idPermohonan);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';

        if ($idPermohonan == null) {
            return redirect()->back();
        }

        $query = Permohonan::with([
            'permohonan_detail',
            'permohonan_detail.tld',
            'permohonan_detail.entitas' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                ]);
            },
            'pelanggan',
            'pelanggan.profile',
            'pelanggan.perusahaan',
            'alamat',
            'dokumen' => function ($q) {
                return $q->where('jenis', 'PermohonanEvaluasi');
            },
            'dokumen.doc_template',
        ])->find($idPermohonan);

        $data['title'] = "Permohonan Evaluasi";
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        // Mengambil template
        $dokumen = $query->dokumen->first();
        $template = $dokumen->doc_template;

        // Setup variables
        if ($dokumen->variables) {
            $variables = $dokumen->variables;
        } else {
            $variables = $this->mappingVars($template, $query, $data);
        }

        // mengambil header
        $header = Documents::where('id_perusahaan', $query->pelanggan->perusahaan->id_perusahaan)
            ->where('jenis', 'header')
            ->where('view', 1)
            ->first();

        $template->header = $header;

        if ($is_download && $type == 'original') {
            $variables['TTD'] = '<br><br><br>';
            $template->header = false;
            $template->footer = false;
        }

        // Generate PDF
        $bytes = $this->generatePDF('Permohonan Evaluasi', $template, $variables, ['TTD', 'CONTENT']);

        $filename = 'Permohonan Evaluasi - ' . date('Y-m-d') . '.pdf';

        if ($is_download) {
            return $bytes->download($filename);
        }
        return $bytes->stream($filename);
    }

    public function contentPermohonanEvaluasi(mixed $data, array $params = [])
    {
        $html = '';
        $no = 1;

        foreach ($data->permohonan_detail as $detail) {
            if ($detail->jenis == 'pengguna') {
                $html .= '
                    <tr>
                        <td>' . $no++ . '</td>
                        <td>' . $detail->entitas->name . '</td>
                        <td>' . $detail->tld->no_seri_tld . '</td>
                        <td>' . $detail->entitas->nik . '</td>
                    </tr>
                ';
            }
        }
        return [
            "CONTENT" => '
                <table class="table-surattugas" style="margin-top: 15px;">
                    <tr>
                        <th width="1%">No</th>
                        <th width="20%">Nama Pengguna TLD</th>
                        <th width="10%">Kode Lencana TLD</th>
                        <th width="20%">NIK Pengguna TLD</th>
                    </tr>
                    ' . $html . '
                </table>
            '
        ];
    }

    public function KontrakPengujian(Request $request, string $id)
    {
        $id = decryptor($id);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';

        if ($id == null) {
            return redirect()->back();
        }

        $query = Kontrak::with([
            'dokumen' => function ($q) {
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
            'kontrak_detail',
            'kontrak_detail.tld_awal',
            'kontrak_detail.tld_second',
            'kontrak_detail.entitas' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                ]);
            },
        ])->where('id_kontrak', $id)->first();

        $data['title'] = "Surat Kontrak Pengujian";
        $data['ttd_default'] = $this->global['urlTtdDefault'];
        $data['stempel'] = $this->global['urlStempel'];

        // mengambil template dokumen
        $dokumen = $query->dokumen->first();
        $template = Documents::with(['footer', 'header'])
            ->where('id_doc', $dokumen->id_doc_template)
            ->first();

        if ($dokumen->variables) {
            $variables = $dokumen->variables ?? [];
        } else {
            $variables = $this->mappingVars($template, $query, $data);
        }

        // mengambil dokumen surat permintaan pengujian
        $permintaan = Permohonan_dokumen::where('id_kontrak', $id)->where('jenis', 'SuratPengujian')->first();

        if ($permintaan) {
            $variables["HARI_PENGUJIAN"] = convert_date($permintaan->created_at, 8);
            $variables["TGL_PENGUJIAN"] = convert_date($permintaan->created_at, 2);
        }

        // TTD
        if ($dokumen->ttd_image) {
            $ttd_manajer = $dokumen->ttd_image;
        } else {
            $ttd_manajer = $dokumen->ttd ?? "";
        }

        // TTD PELANGGAN
        if ($query->pelanggan->ttd_image) {
            $ttd_pelanggan = $query->pelanggan->ttd_image;
        } else {
            $ttd_pelanggan = $query->pelanggan->ttd ?? "";
        }

        if ($is_download && $type == 'original') {
            $ttd_manajer = false;
            $ttd_pelanggan = false;
            $template->header = false;
            $template->footer = false;
        }

        $variables['TTD_MANAJER'] = $ttd_manajer ? "
            <div style='text-align: center;'>
                <img src='" . $data['stempel'] . "' class='img-fluid img-stempel' style='margin-left: 15%;' alt='Stempel-Lab'>
                <img src='$ttd_manajer' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY_MANAJER'] = $dokumen->usersig ? $dokumen->usersig->name : '...........................................';

        $variables['TTD_PELANGGAN'] = $ttd_pelanggan ? "
            <div style='text-align: center;'>
                <img src='$ttd_pelanggan' alt='TTD_PENERIMA' width='100px' height='100px'>
            </div>
        " : "<br><br><br>";
        $variables['TTD_BY_PELANGGAN'] = $query->pelanggan ? $query->pelanggan->name : '...........................................';

        // Generate PDF
        $bytes = $this->generatePDF($data['title'], $template, $variables, [
            "TTD_MANAJER",
            "TTD_BY_MANAJER",
            "TTD_PELANGGAN",
            "TTD_BY_PELANGGAN",
            "RINCIAN",
            "RINCIAN_2",
            "RINCIAN_3",
        ]);

        $filename = $dokumen->nama . '-' . now()->format('Ymd-His') . '.pdf';

        if ($is_download) {
            return $bytes->download($filename);
        }
        return $bytes->stream($filename);
    }

    private function contentKontrakPengujian(mixed $data, $params = [])
    {
        $zrcek = $data->is_zerocek ? 'Zero Check' : '';
        $lJasa = $data->layanan_jasa->satuankerja->name;
        $jTld = $data->jenisTld->name;

        $jenisPengujian = $zrcek . ' ' . $lJasa . ' ' . $jTld;
        $htmlSample = '<div>' . $lJasa . ' ' . $jTld . '</div>';


        foreach ($data->periode as $periode) {
            $startDate = convert_date($periode->start_date, 6);
            $endDate = convert_date($periode->end_date, 6);
            $htmlSample .= '<div>' . $data->jumlah_kontrol . ' + ' . $data->jumlah_pengguna . ' ' . $startDate . ' - ' . $endDate . '</div>';
        }

        $diskon = [];
        $ppn = 0;
        $pph = 0;
        if ($data->invoice) {
            $diskon = $data->invoice->diskon;
            $ppn = $data->invoice->ppn;
            $pph = $data->invoice->pph;
        }

        $dataKeuangan = $this->calculator->calculateInvoice($data->total_harga, $diskon, $ppn, $pph);

        // Mengambil personil
        // Mengambil LIST TLD yang digunakan
        $htmlListTld = '';
        $htmlPengguna = '';
        foreach ($data->kontrak_detail as $key => $value) {
            $htmlListTld .= '
                <tr>
                    <td>' . ($key + 1) . '</td>
                    <td>' . $value->tld_awal->no_seri_tld . '</td>
                    <td>' . ($value->tld_awal->merk ?? '') . '</td>
                </tr>
            ';
            if ($value->tld_second) {
                $htmlListTld .= '
                    <tr>
                        <td>' . ($key + 1) . '</td>
                        <td>' . $value->tld_second->no_seri_tld . '</td>
                        <td>' . ($value->tld_second->merk ?? '') . '</td>
                    </tr>
                ';
            }

            if ($value->jenis == 'pengguna') {
                $divStr = $value->divisiSelected?->name ?? ($value->entitas?->divisi?->name ?? '');
                $kodeStr = $value->kode_lencana_selected ?? ($value->entitas?->kode_lencana ?? '');
                $subInfo = [];
                if ($divStr && $divStr !== '-') $subInfo[] = "Divisi: {$divStr}";
                if ($kodeStr && $kodeStr !== '-') $subInfo[] = "Kode Lencana: {$kodeStr}";
                $infoText = count($subInfo) > 0 ? ' <small style="color: #555;">(' . implode(' | ', $subInfo) . ')</small>' : '';

                $htmlPengguna .= '
                    <li>' . $value->entitas->name . $infoText . '</li>
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
                    ' . $htmlPengguna . '
                </ol>
            ',
            "RINCIAN_3" => '
                <table class="table-surattugas">
                    <tr>
                        <th width="1%">No</th>
                        <th width="30%">Nama Alat</th>
                        <th width="20%">Merk/Tipe</th>
                    </tr>
                    ' . $htmlListTld . '
                </table>
            '
        ];
    }

    public function adendum(Request $request, string $idPermohonan)
    {
        $idPermohonan = decryptor($idPermohonan);
        $is_download = $request->get('dl') ? true : false;
        $type = $request->get('type') ? $request->get('type') : 'full';

        $query = Permohonan::with([
            'jenisTld:id_jenisTld,name',
            'pelanggan',
            'kontrak',
            'kontrak.periode',
            'pelanggan.perusahaan',
            'jenis_layanan:id_jenisLayanan,name',
            'layanan_jasa:id_layanan,nama_layanan',
            'dokumen' => function ($query) {
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

        if ($dokumen->variables) {
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

        if ($is_download && $type == 'original') {
            $variables['TTD'] = '<br><br><br>';
            $template->header = false;
            $template->footer = false;
        }

        // generate pdf
        $bytes = $this->generatePDF($data['title'], $template, $variables, ['TTD', 'RINCIAN_TLD']);

        $filename = $data['title'] . '-' . date('Y-m-d') . '.pdf';

        if ($is_download) {
            return $bytes->download($filename);
        }
        return $bytes->stream($filename);
    }

    private function resolveRadiasiLabel(mixed $entitas): string
    {
        $radiasi = $entitas->radiasi ? $entitas->radiasi->toArray() : [];
        return implode(', ', array_column($radiasi, 'nama_radiasi'));
    }

    private function resolveEntitasColumns(mixed $value, int $key): array
    {
        $col1 = $col2 = $col3 = '';

        if ($value->jenis !== 'pengguna') {
            $col1 = "TLD Kontrol " . ($key + 1);
            return [$col1, $col2, $col3];
        }

        $entitas = $value->entitas;
        $suffix  = ($value->type === 'baru') ? '' : ' (Pengganti ' . $value->penggunaLama->name . ')';

        $col1 = $entitas->name . $suffix;
        $col2 = $entitas->divisi->name ?? '';
        $col3 = $this->resolveRadiasiLabel($entitas);

        return [$col1, $col2, $col3];
    }

    public function contentPermohonanAdendum(mixed $data, array $params): array
    {
        $rows = '';
        foreach ($data->permohonan_detail as $key => $value) {
            [$col1, $col2, $col3] = $this->resolveEntitasColumns($value, $key);

            $rows .= '
                <tr>
                    <td>' . ($key + 1) . '</td>
                    <td>' . $col1 . '</td>
                    <td>' . $col2 . '</td>
                    <td>' . $col3 . '</td>
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
                    ' . $rows . '
                </table>
            '
        ];
    }
}
