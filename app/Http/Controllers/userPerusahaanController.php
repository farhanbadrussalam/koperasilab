<?php

namespace App\Http\Controllers;

use App\Models\perusahaan;
use App\Models\tbl_media;
use Illuminate\Http\Request;
use Auth;

class userPerusahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.perusahaan.index', $data);
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
            'npwp' => ['required'],
            'dokumen' => ['required', 'mimes:pdf']
        ]);

        $perusahaan = perusahaan::findOrFail($id);

        // upload dokumen kuasa
        $dokumen = $request->file('dokumen');
        $realname =  pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = 'surat_kuasa_'.$perusahaan->user_id.'_'.md5($realname).'.'.$dokumen->getClientOriginalExtension();
        $path = $dokumen->storeAs('public/dokumen/surat_kuasa', $filename);

        $media = tbl_media::create([
            'file_hash' => $filename,
            'file_ori' => $dokumen->getClientOriginalName(),
            'file_size' => $dokumen->getSize(),
            'file_type' => $dokumen->getClientMimeType(),
            'status' => 1
        ]);

        $dataPerusahaan = array(
            'name'      => $request->name,
            'npwp'      => unmask($request->npwp),
            'email'     => $request->email,
            'alamat'    => $request->alamat,
            'surat_kuasa' => $media->id
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
