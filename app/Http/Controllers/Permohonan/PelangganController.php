<?php

namespace App\Http\Controllers\Permohonan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master_radiasi;
use App\Models\Master_jenisLayanan;
use App\Models\Master_tld;
use App\Models\Master_jenisTLD;
use App\Models\Permohonan;
use App\Models\Master_layanan_jasa;
use App\Models\Keuangan;
use App\Models\Kontrak;
use App\Models\Kontrak_periode;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use Auth;
use DataTables;

class PelangganController extends Controller
{
    public function __construct()
    {
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
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
            $periodeNext = Kontrak_periode::where('id_kontrak', decryptor($idKontrak))->where('periode', $periodeNow->periode+1)->first();
            // Mengambil Kontrak
            $queryKontrak = Kontrak::with([
                'layanan_jasa',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent:id_jenisLayanan,name,parent',
                'periode',
                'pengguna_map',
                'pengguna_map.pengguna.media_ktp',
                'pengguna_map.pengguna.divisi',
                'pelanggan',
                'pelanggan.perusahaan',
                'pelanggan.perusahaan.alamat',
                'rincian_list_tld' => function($q) use ($periodeNow){
                    return $q->where('periode', $periodeNow->periode-1);
                },
                'rincian_list_tld.tld',
                'rincian_list_tld.pengguna_map',
                'rincian_list_tld.pengguna_map.pengguna'
            ])->where('id_kontrak', decryptor($idKontrak))->first();

            if($queryKontrak && $queryKontrak->pengguna_map){
                foreach($queryKontrak->pengguna_map as $key => $value){
                    $queryKontrak->pengguna_map[$key]->pengguna->radiasi = Master_radiasi::whereIn('id_radiasi', $value->pengguna->id_radiasi)->get();
                }
            }

            // if($queryKontrak->list_tld && count($queryKontrak->list_tld) > 0){
            //     $queryKontrak->tldKontrol = Master_tld::whereIn('id_tld', $queryKontrak->list_tld)->get();
            // }

            // Mengambil jenis layanan Evaluasi - Dengan kontrak
            $jenisLayanan= Master_jenisLayanan::where('id_jenisLayanan', 5)->first();

            // cek apakah permohonan sudah ada atau belum
            $permohonan = Permohonan::select('id_permohonan')
                ->with(
                    'pengguna',
                    'pengguna.media',
                    'pengguna.tld_pengguna',
                    )
                ->where('status', 11)
                ->where('id_kontrak', decryptor($idKontrak))
                ->where('periode', $periodeNow->periode)
                ->first();

            $data = [
                'title' => 'Evaluasi - '. $queryKontrak->layanan_jasa->nama_layanan .' '. $queryKontrak->jenisTld->name,
                'module' => 'permohonan-kontrak',
                'kontrak' => $queryKontrak,
                'periodeNow' => $periodeNow,
                'periodeNext' => $periodeNext,
                'jenisLayanan' => $jenisLayanan,
                'permohonan' => $permohonan
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
        $dataPermohonan = Permohonan::create(array(
            'created_by' => Auth::user()->id,
            'status' => 80,
        ));
        return redirect(Route('permohonan.pengajuan.edit', $dataPermohonan->permohonan_hash));
    }

    public function editPengajuan($id_permohonan)
    {
        $idPermohonan = decryptor($id_permohonan);
        $dataPermohonan = Permohonan::with(
                            'pelanggan',
                            'pelanggan.perusahaan',
                            'pelanggan.perusahaan.alamat',
                            'layanan_jasa:id_layanan,nama_layanan',
                            'jenis_layanan:id_jenisLayanan,name',
                            'jenis_layanan_parent:id_jenisLayanan,name',
                        )
                        ->where('id_permohonan', $idPermohonan)->first();
        $data = [
            'title' => 'Buat pengajuan',
            'module' => 'permohonan-pengajuan',
            'dataRadiasi' => Master_radiasi::where('status', 1)->get(),
            'jenisLayanan' => Master_jenisLayanan::where('status', 1)->whereNull('parent')->get(),
            'layanan_jasa' => Master_layanan_jasa::all(),
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
                       'permohonan.kontrak'
                   )->where('id_keuangan', $idKeuangan)->first();
        // get bukti bayar
        if(isset($keuangan->bukti_bayar)){
            $buktiBayar = $keuangan->bukti_bayar;
            $arrBukti = array();
            foreach ($buktiBayar as $key => $idMedia) {
                array_push($arrBukti, $this->media->get($idMedia));
            }
            $keuangan->media_bukti_bayar = $arrBukti;
        }else{
            $keuangan->media_bukti_bayar = array();
        }

        // get bukti bayar pph
        if(isset($keuangan->bukti_bayar_pph)){
            $buktiBayarPph = $keuangan->bukti_bayar_pph;
            $arrBuktiPph = array();
            foreach ($buktiBayarPph as $key => $idMedia) {
                array_push($arrBuktiPph, $this->media->get($idMedia));
            }
            $keuangan->media_bukti_bayar_pph = $arrBuktiPph;
        }else{
            $keuangan->media_bukti_bayar_pph = array();
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
