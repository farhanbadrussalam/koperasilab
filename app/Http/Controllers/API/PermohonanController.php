<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\tbl_media;

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
                            'jadwal:id,petugas_id,date_mulai,date_selesai',
                            'user:id,email,name',
                            'suratTerbit:id,file_hash,file_ori,file_size,file_type')
                        ->where('id', $id)->first();

        // Mengambil data media
        $dokumen = json_decode($dataPermohonan->dokumen);
        $media = tbl_media::select('id','file_hash','file_ori','file_size','file_type')
                            ->whereIn('id', $dokumen)
                            ->get();
        $dataPermohonan->media = $media;

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
            'id' => 'required',
            'note' => 'required'
        ]);

        $data_permohonan = Permohonan::findOrFail($request->id);

        $data_permohonan->status = $request->status;
        $data_permohonan->note = $request->note;

        // upload Surat
        $dokumen = $request->file('file');
        if($dokumen){
            $realname =  pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = 'surat_'.md5($realname).'.'.$dokumen->getClientOriginalExtension();
            $path = $dokumen->storeAs('public/dokumen/permohonan', $filename);

            $media = tbl_media::create([
                'file_hash' => $filename,
                'file_ori' => $dokumen->getClientOriginalName(),
                'file_size' => $dokumen->getSize(),
                'file_type' => $dokumen->getClientMimeType(),
                'status' => 1
            ]);

            $data_permohonan->surat_terbitan = $media->id;
        }

        $data_permohonan->update();

        $request->status == 2 ? $text = 'setujui' : $text = 'tolak';

        // Notifikasi
        $notif = notifikasi(array(
            'to_user' => $data_permohonan->created_by,
            'type' => 'Permohonan'
        ), "Permohonan ".$data_permohonan->layananjasa->nama_layanan." di $text");

        return response()->json(['message' => 'Permohonan di'.$text], 200);
    }
}
