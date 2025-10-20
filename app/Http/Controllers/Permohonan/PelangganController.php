<?php

namespace App\Http\Controllers\Permohonan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master_radiasi;
use App\Models\Master_jenisLayanan;
use App\Models\Master_tld;
use App\Models\Master_jenisTLD;
use App\Models\Master_layanan_jasa;
use App\Models\Master_divisi;

use App\Models\Perusahaan;
use App\Models\Permohonan;
use App\Models\Keuangan;
use App\Models\Kontrak;
use App\Models\Kontrak_periode;
use App\Models\Kontrak_tld;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use Auth;
use DataTables;
use Log;

class PelangganController extends Controller
{
    protected $media, $log, $global;
    public function __construct()
    {
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
        $this->global = config('customvariabel');
    }

    // FEATURE KONTRAK
    public function indexKontrak()
    {
        $data = [
            'title' => 'Kontrak',
            'module' => 'permohonan-kontrak'
        ];
        return view('pages.permohonan.kontrak.index', $data);
    }

    public function evaluasiKontrak($idKontrak, $idPeriode)
    {
        $periodeNow = Kontrak_periode::where('id_periode', decryptor($idPeriode))->first();
        if($periodeNow){
            $idKontrak = decryptor($idKontrak);
            $periodeBefore = Kontrak_periode::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode-1)->first();
            $periodeNext = Kontrak_periode::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode+1)->first();
            $periode2Next = Kontrak_periode::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode+2)->first();
            // pengecekan periode sekarang
            // $countTld = $periodeNow->periode % 2 == 1 ? 1 : 2;
            $kontrakTld = Kontrak_tld::where('id_kontrak', $idKontrak)->where('count_tld', $periodeNow->count_tld)->get();
            if(count($kontrakTld) == 0){
                $dataKontrakTldSebelum = Kontrak_tld::where('id_kontrak', $idKontrak)->where('count_tld', 1)->get();
                foreach($dataKontrakTldSebelum as $val){
                    $arr = array(
                        'id_kontrak' => $idKontrak,
                        'id_pengguna' => $val->id_pengguna,
                        'id_divisi' => $val->id_divisi,
                        'periode' => $periodeNow->periode,
                        'count_tld' => 2,
                        'status' => 2,
                        'count' => $val->count,
                        'created_by' => Auth::user()->id
                    );
                    Kontrak_tld::create($arr);
                }
            }
            $tldUsed = $periodeNow->periode % 2 == 1 ? 1 : 2;
            // Mengambil Kontrak
            $queryKontrak = Kontrak::with([
                'layanan_jasa',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent:id_jenisLayanan,name,parent',
                'periode',
                'pelanggan',
                'pelanggan.perusahaan',
                'pelanggan.perusahaan.alamat',
                'rincian_list_tld' => function($q) use ($tldUsed){
                    return $q->where('status', 2)->where('count_tld', $tldUsed); // TLD ada di pelanggan untuk evaluasi kontrak
                },
                'rincian_list_tld.pengguna',
                'rincian_list_tld.pengguna.media_ktp',
                'rincian_list_tld.pengguna.divisi'
            ])->where('id_kontrak', $idKontrak)->first();

            $layanan = jenislayanan($queryKontrak->jenis_layanan_parent, $queryKontrak->jenis_layanan);
            $isSewa = in_array($layanan, $this->global['arr_sewa']);

            if($queryKontrak && $queryKontrak->rincian_list_tld){
                foreach($queryKontrak->rincian_list_tld as $key => $value){
                    if($value->pengguna && $value->pengguna->id_radiasi){
                        $value->pengguna->radiasi = Master_radiasi::whereIn('id_radiasi', $value->pengguna->id_radiasi)->get();
                    }
                }
            }

            if($queryKontrak->jenis_layanan_parent->id_jenisLayanan == 7){
                $jenisLayanan = Master_jenisLayanan::where('id_jenisLayanan', 9)->first();
            } else {
                // Mengambil jenis layanan Evaluasi - Dengan kontrak
                $jenisLayanan = Master_jenisLayanan::where('id_jenisLayanan', 5)->first();
            }

            // cek apakah permohonan sudah ada atau belum
            $permohonan = Permohonan::select('id_permohonan')
                ->with(
                    'rincian_list_tld.pengguna',
                    'rincian_list_tld.pengguna.media_ktp',
                    )
                ->where('status', 11)
                ->where('id_kontrak', decryptor($idKontrak))
                ->where('periode', $periodeNow->periode)
                ->first();

            $data = [
                'title' => 'Evaluasi - '. $queryKontrak->layanan_jasa->nama_layanan .' '. $queryKontrak->jenisTld->name,
                'module' => 'permohonan-kontrak',
                'kontrak' => $queryKontrak,
                'periodeBefore' => $periodeBefore,
                'periodeNow' => $periodeNow,
                'periodeNext' => $periodeNext,
                'periode2Next' => $periode2Next,
                'jenisLayanan' => $jenisLayanan,
                'permohonan' => $permohonan,
                'isSewa' => $isSewa
            ];


            return view('pages.permohonan.kontrak.evaluasi', $data);
        }
    }

    // FEATURE PENGAJUAN
    public function indexPengajuan()
    {
        $data = [
            'title' => 'Pengajuan',
            'module' => 'permohonan-pengajuan',
            'type' => 'list'
        ];
        return view('pages.permohonan.pengajuan.index', $data);
    }

    public function tambahPengajuan()
    {
        // mengambil perusahaan dari user
        $perusahaan = Perusahaan::with("alamat")->where('id_perusahaan', Auth::user()->id_perusahaan)->first();
        if(!$perusahaan){
            // redirect ke halaman pengajuan dan memberikan pesan error
            return redirect(Route('permohonan.pengajuan'))->with('warning', 'Anda belum masuk ke perusahaan manapun.');
        }

        if(!$perusahaan->kode_perusahaan){
            // redirect ke halaman pengajuan dan memberikan pesan warning
            return redirect(Route('permohonan.pengajuan'))->with('warning', $perusahaan->nama_perusahaan .' belum memiliki kode perusahaan.');
        }

        // pengecekan alamat kosong
        $alamatUtama = $perusahaan->alamat->firstWhere('jenis', 'Utama');
        if(!$alamatUtama->alamat){
            // redirect ke halaman pengajuan dan memberikan pesan warning
            return redirect(Route('permohonan.pengajuan'))->with('warning', 'Data perusahaan belum lengkap.');
        }

        $dataPermohonan = Permohonan::create(array(
            'created_by' => Auth::user()->id,
            'status' => 80,
        ));

        return redirect(Route('permohonan.pengajuan.edit', $dataPermohonan->permohonan_hash));
    }

    public function editPengajuan($id_permohonan)
    {
        $idPermohonan = decryptor($id_permohonan);
        $dataPermohonan = Permohonan::with([
                            'pelanggan',
                            'jenisTld',
                            'pelanggan.perusahaan',
                            'pelanggan.perusahaan.alamat' => function($q){
                                $q->where('status', 1);
                            },
                            'layanan_jasa:id_layanan,nama_layanan',
                            'jenis_layanan:id_jenisLayanan,name',
                            'jenis_layanan_parent:id_jenisLayanan,name',
                        ])
                        ->where('id_permohonan', $idPermohonan)->first();
        $data = [
            'title' => 'Buat pengajuan',
            'module' => 'permohonan-pengajuan',
            'radiasi' => Master_radiasi::where('status', 1)->get(),
            'jenisLayanan' => Master_jenisLayanan::where('status', 1)->whereNull('parent')->get(),
            'layanan_jasa' => Master_layanan_jasa::where('status', 1)->get(),
            'divisi' => Master_divisi::where('status', 1)->where('id_perusahaan', Auth::user()->id_perusahaan)->get(),
            'permohonan' => $dataPermohonan,
        ];

        return view('pages.permohonan.pengajuan.tambah', $data);
    }

    // FEATURE PENGEMBALIAN
    public function indexPengembalian()
    {
        $data = [
            'title' => 'Dikembalikan',
            'module' => 'permohonan-dikembalikan'
        ];
        return view('pages.permohonan.dikembalikan.index', $data);
    }

    // FEATURE PEMBAYARAN
    public function indexPembayaran()
    {
        $data = [
            'title' => 'Pembayaran',
            'module' => 'permohonan-pembayaran'
        ];
        return view('pages.permohonan.pembayaran.index', $data);
    }

    public function bayarInvoicePembayaran($idKeuangan){
        $data = [
            'title' => 'Invoice',
            'module' => 'permohonan-pembayaran'
        ];

        $idKeuangan = decryptor($idKeuangan);

        $keuangan = Keuangan::with(
                       'diskon',
                       'usersig:id,name',
                       'permohonan',
                       'permohonan.layanan_jasa:id_layanan,nama_layanan',
                       'permohonan.jenisTld:id_jenisTld,name',
                       'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                       'permohonan.jenis_layanan_parent',
                       'permohonan.pelanggan',
                       'permohonan.pelanggan.perusahaan',
                       'permohonan.kontrak',
                       'metode_pembayaran'
                   )->where('id_keuangan', $idKeuangan)->first();

        if($keuangan->metode_pembayaran){
            $keuangan->metode_pembayaran->content = contenMetodePembayaran($keuangan->metode_pembayaran->content, $keuangan->variabel_jenis_pembayaran);
        }

        $data['keuangan'] = $keuangan;

        return view('pages.permohonan.pembayaran.bayar', $data);
    }

    // FEATURE PENGIRIMAN
    public function indexPengiriman()
    {
        $data = [
            'title' => 'Pengiriman',
            'module' => 'permohonan-pengiriman'
        ];
        return view('pages.permohonan.pengiriman.index', $data);
    }
}
