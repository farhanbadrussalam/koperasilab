<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\jadwal;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dataJadwal = jadwal::with('layananjasa','media')->where('id', $id)->first();

        return response()->json(['data' => $dataJadwal], 200);
    }

    public function getJadwal(Request $request)
    {
        $id_layanan = isset($request->idLayanan) ? $request->idLayanan : null;
        $jenis_layanan = isset($request->jenisLayanan) ? $request->jenisLayanan : null;

        $jadwal = jadwal::where('status', 2)->where('kuota', '!=', 0);
        if($id_layanan){
            $jadwal->where('layananjasa_id', $id_layanan);
        }
        if($jenis_layanan){
            $jadwal->where('jenislayanan', $jenis_layanan);
        }
        $data = $jadwal->get();
        return response()->json(['data' => $data], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // if($credential){
            $delete = jadwal::findOrFail($id);
            $delete->status = '99';
            $delete->update();

            return response()->json(['message' => 'Berhasil di hapus'], 200);
        // }else{
        //     return response()->json(['message' => 'Invalid credentials'], 401);
        // }
    }

    public function confirm(Request $request){
        $validator = $request->validate([
            'idJadwal' => ['required'],
            'answer' => ['required']
        ]);
    }
}
