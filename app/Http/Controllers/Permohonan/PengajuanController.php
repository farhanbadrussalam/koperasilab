<?php

namespace App\Http\Controllers\Permohonan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Master_radiasi;
use App\Models\Master_jenisLayanan;
use App\Models\Permohonan;
use App\Models\Master_layanan_jasa;

use Auth;
use DataTables;

class PengajuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $data = [
            'title' => 'Pengajuan',
            'module' => 'permohonan-pengajuan',
            'type' => 'list'
        ];
        return view('pages.permohonan.pengajuan.index', $data);
    }

    public function tambah()
    {
        // create pengajuan
        $dataPermohonan = Permohonan::create(array(
            'created_by' => Auth::user()->id,
            'status' => 80,
        ));
        return redirect(Route('permohonan.pengajuan.edit', $dataPermohonan->permohonan_hash));
    }

    public function edit($id_permohonan)
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
}
