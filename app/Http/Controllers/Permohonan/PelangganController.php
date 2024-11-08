<?php

namespace App\Http\Controllers\Permohonan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master_radiasi;
use App\Models\Master_jenisLayanan;
use App\Models\Permohonan;
use App\Models\Master_layanan_jasa;
use App\Models\Keuangan;

use Auth;
use DataTables;

class PelangganController extends Controller
{
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

    public function editPengajuan()
    {
        $idPermohonan = decryptor($id_permohonan);
        $dataPermohonan = Permohonan::where('id_permohonan', $idPermohonan)->first();
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
                       'media_bayar',
                       'media_bayar_pph',
                       'permohonan',
                       'permohonan.layanan_jasa:id_layanan,nama_layanan',
                       'permohonan.jenisTld:id_jenisTld,name', 
                       'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                       'permohonan.jenis_layanan_parent',
                       'permohonan.pelanggan:id,name'
                   )->where('id_keuangan', $idKeuangan)->first();
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
