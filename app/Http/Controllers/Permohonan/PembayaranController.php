<?php

namespace App\Http\Controllers\Permohonan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Keuangan;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index()
     {
         $data = [
             'title' => 'Pembayaran',
             'module' => 'permohonan-pembayaran'
         ];
         return view('pages.permohonan.pembayaran.index', $data);
     }

     public function bayarInvoice($idKeuangan){
         $data = [
             'title' => 'Invoice',
             'module' => 'permohonan-pembayaran'
         ];

         $idKeuangan = decryptor($idKeuangan);
         
         $keuangan = Keuangan::with(
                        'diskon',
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
}
