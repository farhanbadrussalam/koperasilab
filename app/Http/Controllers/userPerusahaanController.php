<?php

namespace App\Http\Controllers;

use App\Models\perusahaan;
use Illuminate\Http\Request;
use Auth;

class userPerusahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.perusahaan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
    public function show(perusahaan $perusahaan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(perusahaan $perusahaan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => ['required','email'],
            'npwp' => ['required', 'numeric']
        ]);

        $perusahaan = perusahaan::findOrFail($id);

        $dataPerusahaan = array(
            'name' => $request->name,
            'npwp' => $request->npwp,
            'email'=> $request->email,
            'alamat'=> $request->alamat
        );

        $perusahaan->update($dataPerusahaan);

        return redirect()->route('userPerusahaan.index')->with('success', 'Berhasil di update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(perusahaan $perusahaan)
    {
        //
    }
}
