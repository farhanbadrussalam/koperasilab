<?php

namespace App\Http\Controllers\Permohonan;

use App\Http\Controllers\API\TldAPI;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master_jenisLayanan;
use App\Models\Master_layanan_jasa;

use App\Models\Perusahaan;
use App\Models\Permohonan;
use App\Models\Keuangan;
use App\Models\Kontrak;
use App\Models\Kontrak_periode;

use App\Http\Controllers\MediaController;
use App\Models\Master_pengguna;
use App\Models\Pengiriman;
use Auth;
use DataTables;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Log;

class PelangganController extends Controller
{
    protected $media, $log, $global, $tld;
    public function __construct()
    {
        $this->media = resolve(MediaController::class);
        $this->global = config('customvariabel');
        $this->tld = resolve(TldAPI::class);
    }

    // FEATURE KONTRAK
    public function indexKontrak()
    {
        $data = [
            'title' => 'Transaksi',
            'module' => 'permohonan-kontrak'
        ];
        return view('pages.permohonan.kontrak.index', $data);
    }

    public function evaluasiKontrak(string $idKontrak, string $idPeriode)
    {
        $periodeNow = Kontrak_periode::with([
            'permohonan',
            'permohonan.permohonan_detail',
            'permohonan.permohonan_detail.entitas' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                ]);
            },
            'permohonan.permohonan_detail.tld',
        ])->where('id_periode', decryptor($idPeriode))->first();
        if ($periodeNow) {
            $idKontrak = decryptor($idKontrak);

            // Pengecekan adendum
            setKontrakAdendum($idKontrak, $periodeNow->periode);

            $periodeBefore = Kontrak_periode::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode - 1)->first();
            $periodeNext = Kontrak_periode::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode + 1)->first();
            $periode2Next = Kontrak_periode::where('id_kontrak', $idKontrak)->where('periode', $periodeNow->periode + 2)->first();

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
                'kontrak_detail',
                'kontrak_detail.tld_1',
                'kontrak_detail.tld_2',
                'kontrak_detail.entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'kontrak_map' => function ($q) use ($periodeNow) {
                    $q->where('periode', $periodeNow->periode);
                },
                'kontrak_map.tld',
                'kontrak_map.entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
            ])->where('id_kontrak', $idKontrak)->first();

            $layanan = jenislayanan($queryKontrak->jenis_layanan_parent, $queryKontrak->jenis_layanan);
            $isSewa = in_array($layanan, $this->global['arr_sewa']);

            if ($queryKontrak->jenis_layanan_parent->id_jenisLayanan == 7) {
                $jenisLayanan = Master_jenisLayanan::where('id_jenisLayanan', 9)->first();
            } else {
                // Mengambil jenis layanan Evaluasi - Dengan kontrak
                $jenisLayanan = Master_jenisLayanan::where('id_jenisLayanan', 5)->first();
            }

            $data = [
                'title' => 'Evaluasi - ' . $queryKontrak->layanan_jasa->nama_layanan . ' ' . $queryKontrak->jenisTld->name,
                'module' => 'permohonan-kontrak',
                'kontrak' => $queryKontrak,
                'periodeBefore' => $periodeBefore,
                'periodeNow' => $periodeNow,
                'periodeNext' => $periodeNext,
                'periode2Next' => $periode2Next,
                'jenisLayanan' => $jenisLayanan,
                'isSewa' => $isSewa
            ];

            return view('pages.permohonan.kontrak.evaluasi', $data);
        } else {
            abort(404);
        }
    }

    private function cariTldDiPengiriman($id_kontrak, $periode)
    {
        $pengiriman = Pengiriman::with('detail')
            ->where('id_kontrak', $id_kontrak)
            ->where('periode', $periode)
            ->whereHas('detail', function ($query) {
                $query->where('jenis', 'tld');
            })
            ->first();

        return $pengiriman ? $pengiriman->detail->where('jenis', 'tld')->first() : null;
    }

    public function adendumKontrak($idKontrak)
    {
        $idKontrak = decryptor($idKontrak);

        if ($idKontrak) {
            $data = [
                'title' => 'Adendum Kontrak',
                'module' => 'permohonan-kontrak',
            ];

            $kontrak = Kontrak::with([
                'pelanggan',
                'pelanggan.perusahaan',
                'layanan_jasa',
                'jenis_layanan',
                'jenis_layanan_parent',
                'jenisTld',
                'periode'
            ])->where('id_kontrak', $idKontrak)->first();

            if ($kontrak) {
                $tldSentStatus = [];
                foreach ($kontrak->periode as $p) {
                    $tldSentStatus[$p->periode] = Pengiriman::where('id_kontrak', $idKontrak)
                        ->where('periode', $p->periode)
                        ->whereHas('detail', function ($q) {
                            $q->where('jenis', 'tld');
                        })
                        ->whereIn('status', [1, 2, 3])
                        ->exists();
                }
                $kontrak->tld_sent_status = $tldSentStatus;

                $evaluasiCreated = [];
                foreach ($kontrak->periode as $p) {
                    $evaluasiCreated[$p->periode] = Permohonan::where('id_kontrak', $idKontrak)
                        ->where('periode', $p->periode)
                        ->whereNot('tipe_kontrak', 'adendum')
                        ->exists();
                }
                $kontrak->evaluasi_created = $evaluasiCreated;
            }

            $data['kontrak'] = $kontrak;

            return view('pages.permohonan.kontrak.adendum', $data);
        } else {
            abort(404);
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
        if (!$perusahaan) {
            // redirect ke halaman pengajuan dan memberikan pesan error
            return redirect(Route('permohonan.pengajuan'))->with('warning', 'Anda belum masuk ke perusahaan manapun.');
        }

        if (!$perusahaan->kode_perusahaan) {
            // redirect ke halaman pengajuan dan memberikan pesan warning
            return redirect(Route('permohonan.pengajuan'))->with('warning', $perusahaan->nama_perusahaan . ' belum memiliki kode perusahaan.');
        }

        // pengecekan alamat kosong
        $alamatUtama = $perusahaan->alamat->firstWhere('jenis', 'Utama');
        if (!$alamatUtama?->alamat) {
            // redirect ke halaman pengajuan dan memberikan pesan warning
            return redirect(Route('permohonan.pengajuan'))->with('warning', 'Data perusahaan belum lengkap.');
        }

        // pengecekan ttd user pelanggan
        $ttd_user = Auth::user()->ttd;
        if (!$ttd_user) {
            // redirect ke halaman pengajuan dan memberikan pesan warning
            return redirect(Route('userProfile.index'))->with('warning', 'Anda belum Menambahkan TTD.');
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
            'pelanggan.perusahaan.alamat' => function ($q) {
                $q->where('status', 1);
            },
            'layanan_jasa:id_layanan,nama_layanan',
            'jenis_layanan:id_jenisLayanan,name',
            'jenis_layanan_parent:id_jenisLayanan,name',
        ])
            ->where('id_permohonan', $idPermohonan)->first();
        if (!$dataPermohonan) {
            abort(404);
        }
        $data = [
            'title' => 'Buat pengajuan',
            'module' => 'permohonan-pengajuan',
            'jenisLayanan' => Master_jenisLayanan::where('status', 1)->whereNull('parent')->get(),
            'layanan_jasa' => Master_layanan_jasa::where('status', 1)->get(),
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
        notifRead(['Dikembalikan']);
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

    public function bayarInvoicePembayaran($idKeuangan)
    {
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
            'permohonan.kontrak.periode',
            'metode_pembayaran'
        )->where('id_keuangan', $idKeuangan)->first();

        if (!$keuangan)
            abort(404);

        if ($keuangan->metode_pembayaran) {
            $keuangan->metode_pembayaran->content = contenMetodePembayaran($keuangan->metode_pembayaran->content, $keuangan->variabel_jenis_pembayaran);
        }

        $data['keuangan'] = $keuangan;

        // cek notifikasi read
        notifRead('Keuangan', $keuangan->keuangan_hash);

        return view('pages.permohonan.pembayaran.bayar', $data);
    }

    // FEATURE PENGIRIMAN
    public function indexPengiriman()
    {
        $data = [
            'title' => 'Pengiriman',
            'module' => 'permohonan-pengiriman'
        ];
        notifRead(['pengiriman']);
        return view('pages.permohonan.pengiriman.index', $data);
    }
}
