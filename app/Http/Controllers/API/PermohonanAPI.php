<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Detail_permohonan;
use App\Models\tbl_media;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\DetailPermohonanController;

use Auth;

class PermohonanAPI extends Controller
{
    use RestApi;

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
                            'user:id,email,name')
                        ->where('id', $idHash)->first();

        // Mengambil data media
        $dokumen = json_decode($dataPermohonan->dokumen);
        $media = tbl_media::select('id','file_hash','file_ori','file_size','file_type','created_at')
                            ->whereIn('id', $dokumen)
                            ->get();
        $dataPermohonan->media = $media;

        // Mengambil data media petugas
        $detailPermohonan = Detail_permohonan::with('media')->where('permohonan_id', $idHash)->where('status', 1)->first();
        $dataPermohonan->detailPermohonan = $detailPermohonan;

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

    public function verifikasi_kontrak(Request $request)
    {
        $validator = $request->validate([
            'file' => 'required',
            'id' => 'required',
            'note' => 'required',
            'status' => 'required'
        ]);

        $idPermohonan = decryptor($request->id);

        $this->detail->reset($idPermohonan);

        $tmp_arr = array(
            'permohonan_id' => $idPermohonan,
            'note' => $request->note,
            'status' => 1,
            'flag' => $request->status == 3 ? 3 : 2,
            'created_by' => Auth::user()->id
        );

        // upload Surat
        $dokumen = $request->file('file');
        if($dokumen){
            $tmp_arr['surat_terbitan'] = $this->media->upload($dokumen, 'pelaksana');
        }

        $noKontrak = 'K'.generate();

        $data_permohonan = Permohonan::findOrFail($idPermohonan);
        $data_permohonan->flag = $request->status == 3 ? 3 : 2;

        $data_permohonan->status = $request->status;

        $request->status != 9 ? $data_permohonan->no_kontrak = $noKontrak : false;

        $data_permohonan->update();

        Detail_permohonan::create($tmp_arr);

        return response()->json(['message' => 'success'], 200);
    }


    public function verifikasi_fd(Request $request){
        $validator = $request->validate([
            'id' => 'required',
            'status' => 'required'
        ]);

        $idPermohonan = decryptor($request->id);
        $type = isset($request->type) ? $request->type : null;

        $data_permohonan = Permohonan::findOrFail($idPermohonan);
        $data_permohonan->flag = $request->status == 2 ? 2 : 1;
        if($request->status == 9) {
            $data_permohonan->status = 9;
        }
        $data_permohonan->update();

        if($request->type != 'return'){
            $this->detail->reset($idPermohonan);

            $tmp_arr = array(
                'permohonan_id' => $idPermohonan,
                'note' => $request->note,
                'status' => 1,
                'flag' => $request->status == 2 ? 2 : 1,
                'created_by' => Auth::user()->id
            );

            // upload Surat
            $dokumen = $request->file('file');
            if($dokumen){
                $tmp_arr['surat_terbitan'] = $this->media->upload($dokumen, 'frontdesk');
            }

            Detail_permohonan::create($tmp_arr);
        }

        // $request->status == 2 ? $text = 'setujui' : $text = 'tolak';

        // // Notifikasi
        // $notif = notifikasi(array(
        //     'to_user' => $data_permohonan->created_by,
        //     'type' => 'Permohonan'
        // ), "Permohonan ".$data_permohonan->layananjasa->nama_layanan." di $text");

        return response()->json(['message' => 'success'], 200);
    }

    public function sendSuratTugas(Request $request)
    {
        $validator = $request->validate([
            'file' => 'required',
            'no_kontrak' => 'required'
        ]);

        $lampiran = $request->file('file');
        $surat_tugas = null;
        if($lampiran){
            $surat_tugas = $this->media->upload($lampiran, 'surat_tugas');
        }

        $data_permohonan = Permohonan::where('no_kontrak', $request->no_kontrak)->first();

        $data_permohonan->surat_tugas = $surat_tugas;
        $data_permohonan->update();

        if($data_permohonan){
            $payload = array(
                'message' => 'Berhasil di kirim'
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Gagal mengirim surat tugas'
            ], 400);
        }

    }
}
