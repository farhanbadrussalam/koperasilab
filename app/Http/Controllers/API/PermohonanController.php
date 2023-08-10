<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permohonan;

class PermohonanController extends Controller
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
        $dataPermohonan = Permohonan::with(
                            'layananjasa:id,nama_layanan',
                            'jadwal:id,date_mulai,date_selesai',
                            'user:id,email,name',
                            'media:id,file_hash,file_ori,file_size,file_type')
                        ->where('id', $id)->first();

        return response()->json(['data' => $dataPermohonan], 200);
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
        $delete = Permohonan::findOrFail($id);
        $delete->status = '99';
        $delete->update();

        return response()->json(['message' => 'Berhasil di hapus'], 200);
    }

    public function confirm(Request $request){
        $validator = $request->validate([
            'file' => 'required',
        ]);

        dd($request);
    }
}
