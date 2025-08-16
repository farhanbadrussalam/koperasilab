<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

use PDF;
use Auth;
use Log;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->global = config('customvariabel');
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

        $data['data'] = $query;
        $data['date'] = Carbon::now();
        $data['title'] = "Invoice";
        $data['ttd_default'] = public_path('icons/default/white.png');
        $data['stempel'] = public_path('icons/Stempel-Lab.png');
        $data['is_catatan'] = !in_array($JL, $this->global['catatan_invoice']);

        $periodePemakaian = $query->permohonan->periode_pemakaian;

        if($query->permohonan && count($periodePemakaian) > 0){
            $data['periode_start'] = $periodePemakaian[0];
            $data['periode_end'] = $periodePemakaian[count($periodePemakaian) - 1] ?? null;
        }

        $pdf = PDF::loadView('report.invoice', $data);
        $pdf->render();
        return $pdf->stream();
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

        $pdf = PDF::loadView('report.tandaTerima', $data);

        $pdf->render();

        return $pdf->stream();;
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

    public function perjanjian($id = null){
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

        $pdf = PDF::loadView('report.perjanjian', $data);
        $pdf->render();

        // Dapatkan canvas dari DomPDF
        // $canvas = $pdf->getDomPDF()->get_canvas();

        // Tentukan posisi dan sudut rotasi
        // $canvas->save(); // Simpan state awal canvas
        // $canvas->rotate(-45, $canvas->get_width() / 2, $canvas->get_height() / 2); // Rotasi -45 derajat di tengah halaman

        // Tambahkan teks "DRAFT" di latar belakang
        // $canvas->set_opacity(0.1); // Transparansi teks
        // $canvas->text(150, 350, 'DRAFT', null, 100, [0, 0, 0]);

        // $canvas->restore(); // Kembali ke state awal setelah rotasi

        return $pdf->stream();
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
