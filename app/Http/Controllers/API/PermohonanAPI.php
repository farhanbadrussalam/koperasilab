<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\Detail_permohonan;
use App\Models\tbl_media;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\DetailPermohonanController;

use Auth;

class PermohonanAPI extends Controller
{
    public function __construct(){
        $this->media = resolve(MediaController::class);
        $this->detail = resolve(DetailPermohonanController::class);
    }
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
        $idHash = decryptor($id);
        $dataPermohonan = Permohonan::with(
                            'layananjasa:id,nama_layanan',
                            'jadwal:id,date_mulai,date_selesai',
                            'user:id,email,name',
                            'suratTerbit:id,file_hash,file_ori,file_size,file_type')
                        ->where('id', $idHash)->first();

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
    public function update(Request $request, string $idHash)
    {
        $idPermohonan = decryptor($idHash);
        $status = isset($request->status) ? $request->status : null;
        $tag = isset($request->tag) ? $request->tag : null;

        $permohonan = Permohonan::findOrFail($idPermohonan);

        if($status){
            $permohonan->status = $status;
        }

        if($tag){
            $permohonan->tag = $tag;
        }

        $permohonan->update();

        return response()->json(['message' => 'Berhasil di update'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $idHash = decryptor($id);

        Detail_permohonan::where('permohonan_id', $idHash)->update([
            'status' => '99'
        ]);

        $delete = Permohonan::findOrFail($idHash);
        $delete->status = '99';
        $delete->update();

        return response()->json(['message' => 'Berhasil di hapus'], 200);
    }

    public function verifikasi_fd(Request $request){
        $validator = $request->validate([
            'file' => 'required',
            'id' => 'required',
            'note' => 'required'
        ]);

        $idPermohonan = decryptor($request->id);

        $this->detail->reset($idPermohonan);

        $tmp_arr = array(
            'permohonan_id' => $idPermohonan,
            'note' => $request->note,
            'flag' => 2,
            'status' => 1,
            'created_by' => Auth::user()->id
        );

        // upload Surat
        $dokumen = $request->file('file');
        if($dokumen){
            $tmp_arr['surat_terbitan'] = $this->media->upload($dokumen, 'frontdesk');
        }

        $data_permohonan = Permohonan::findOrFail($idPermohonan);
        $data_permohonan->flag = 2;
        $data_permohonan->update();

        Detail_permohonan::create($tmp_arr);

        // $request->status == 2 ? $text = 'setujui' : $text = 'tolak';

        // // Notifikasi
        // $notif = notifikasi(array(
        //     'to_user' => $data_permohonan->created_by,
        //     'type' => 'Permohonan'
        // ), "Permohonan ".$data_permohonan->layananjasa->nama_layanan." di $text");

        return response()->json(['message' => 'success'], 200);
    }
}
