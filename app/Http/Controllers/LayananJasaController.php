<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Layanan_jasa;
use App\Models\Satuan_kerja;
use Auth;

class LayananJasaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.layananJasa.index');
    }

    public function getData() {
        $layanan = Layanan_jasa::where('created_by', Auth::user()->id)
                    ->where('status', 1)                
                    ->get();

        debug($layanan);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['satuankerja'] = Satuan_kerja::all();
        return view('pages.layananJasa.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
        //
    }
}
