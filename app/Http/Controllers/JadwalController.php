<?php

namespace App\Http\Controllers;

use App\Models\jadwal;
use App\Models\Layanan_jasa;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['jadwal'] = jadwal::with('petugas','layananjasa','user')->get();
        return view('pages.jadwal.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $data['layanan'] = Layanan_jasa::where('satuankerja_id', Auth::user()->satuankerja_id)
                                ->where('status', 1)
                                ->get();
        $data['petugas'] = User::where('satuankerja_id', $user->satuankerja_id)->role('staff')->get();
        return view('pages.jadwal.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'layanan_jasa' => ['required'],
            'jenis_layanan' => ['required'],
            'tanggal_mulai' => ['required'],
            'tanggal_selesai' => ['required'],
            'kuota' => ['required'],
            'petugas' => ['required'],
            'dokumen' => 'mimes:pdf,doc,docx|max:2048'
        ]);

        $dataJadwal = array(
            'layananjasa_id' => $request->layanan_jasa,
            'jenislayanan' => explode('|', $request->jenis_layanan)[0],
            'tarif' => $request->tarif,
            'date_mulai' => $request->tanggal_mulai,
            'date_selesai' => $request->tanggal_selesai,
            'kuota' => $request->kuota,
            'petugas_id' => $request->petugas,
            'dokumen' => '',
            'status' => 1,
            'created_by' => Auth::user()->id
        );

        jadwal::create($dataJadwal);

        return redirect()->route('jadwal.index')->with('success', 'Berhasil di tambah');
    }

    /**
     * Display the specified resource.
     */
    public function show(jadwal $jadwal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(jadwal $jadwal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, jadwal $jadwal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(jadwal $jadwal)
    {
        //
    }
}
