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
        $dataJadwal = jadwal::with('layananjasa')->where('id', $id)->first();

        return response()->json(['data' => $dataJadwal], 200);
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
}
