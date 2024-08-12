<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Detail_permohonan;
use App\Models\tbl_media;
use App\Models\tbl_lhu;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\DetailPermohonanController;

use Auth;
use DB;

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
    public function listPermohonan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $status = $request->has('status') ? $request->status : 1;

        $query = Permohonan::with(['layananjasa', 'tbl_lhu', 'tbl_kip'])
                    ->where('status', $status)
                    ->where('created_by', Auth::user()->id)
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->paginate($limit);
        $arr = $query->toArray();
        $this->pagination = Arr::except($arr, 'data');
        return $this->output($query);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addPermohonan(Request $request)
    {
        DB::beginTransaction();
        try {
            $layanan_id = $request->layanan_hash ? decryptor($request->layanan_hash) : false;
            $desc_biaya = $request->desc_biaya ? $request->desc_biaya : false;
            $biaya = $request->biaya ? unmask($request->biaya) : false;
            $no_bapeten = $request->no_bapeten ? $request->no_bapeten : false;
            $jenis_limbah = $request->jenis_limbah ? $request->jenis_limbah : false;
            $sumber_radioaktif = $request->sumber_radioaktif ? $request->sumber_radioaktif : false;
            $jumlah = $request->jumlah ? $request->jumlah : false;
            $documents = $request->file('documents');

            $dokumen_pendukung = "";
            if($documents){
                $arrMedia = array();
                foreach ($documents as $key => $document) {
                    $idMedia = $this->media->upload($document, 'permohonan');
                    array_push($arrMedia, $idMedia);
                }

                $dokumen_pendukung = json_encode($arrMedia);
            }
            $data = array(
                'layananjasa_id' => $layanan_id,
                'jenis_layanan' => $desc_biaya,
                'tarif' => $biaya,
                'no_bapeten' => $no_bapeten,
                'jenis_limbah' => $jenis_limbah,
                'sumber_radioaktif' => $sumber_radioaktif,
                'jumlah' => $jumlah,
                'dokumen' => $dokumen_pendukung,
                'status' => 1,
                'flag' => 1,
                'tag' => 'pengajuan',
                'created_by' => Auth::user()->id
            );

            $permohonan = Permohonan::create($data);

            // save to detail permohonan
            if(isset($permohonan)){
                // reset status detail to 99
                $reset = Detail_permohonan::where('permohonan_id', $permohonan->id)->update(['status' => '99']);

                Detail_permohonan::create(array(
                    'permohonan_id' => $permohonan->id,
                    'status' => 1,
                    'flag' => 1,
                    'created_by' => Auth::user()->id
                ));
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan!'
            ], 200);

        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $idHash = decryptor($id);
        $dataPermohonan = Permohonan::with(
                            'layananjasa:id,nama_layanan',
                            'jadwal:id,permohonan_id,date_mulai,date_selesai',
                            'user:id,email,name',
                            'jadwal.tbl_lhu', 'jadwal.tbl_lhu.jawaban', 'jadwal.tbl_lhu.jawaban.pertanyaan:id,title',
                            'tbl_kip', 'tbl_kip.bukti',
                            'signature_1:id,name', 'signature_2:id,name')
                        ->where('id', $idHash)
                        ->orWhere('no_kontrak', $idHash)
                        ->first();

        // Mengambil data media
        $dokumen = isset($dataPermohonan->dokumen) ? json_decode($dataPermohonan->dokumen) : array();
        $media = tbl_media::select('id','file_hash','file_ori','file_size','file_type','file_path','created_at')
                            ->whereIn('id', $dokumen)
                            ->get();
        $dataPermohonan->media = count($media) != 0 ? $media : false;

        // Mengambil data media petugas
        $detailPermohonan = Detail_permohonan::with('media')->where('permohonan_id', $idHash)->where('status', 1)->first();
        $dataPermohonan->detailPermohonan = $detailPermohonan;

        return $this->output($dataPermohonan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $idHash)
    {
        $idPermohonan = decryptor($idHash);
        $status = isset($request->status) ? $request->status : null;
        $tag = isset($request->tag) ? $request->tag : null;
        $flag = isset($request->flag) ? $request->flag : null;
        $jenis_limbah = isset($request->jenis_limbah) ? $request->jenis_limbah : null;
        $sumber_radioaktif = isset($request->sumber_radioaktif) ? $request->sumber_radioaktif : null;
        $jumlah = isset($request->jumlah) ? $request->jumlah : null;
        $nomor_antrian = isset($request->nomor_antrian) ? $request->nomor_antrian : null;
        $jadwal_id = isset($request->jadwal_id) ? decryptor($request->jadwal_id) : null;
        $no_bapeten = isset($request->no_bapeten) ? $request->no_bapeten : null;
        $desc_biaya = isset($request->desc_biaya) ? $request->desc_biaya : null;
        $biaya = isset($request->biaya) ? unmask($request->biaya) : null;
        $ttd_1 = isset($request->ttd_1) ? $request->ttd_1 : null;
        $ttd_1_by = isset($request->ttd_1_by) ? $request->ttd_1_by : null;
        $ttd_2 = isset($request->ttd_2) ? $request->ttd_2 : null;
        $ttd_2_by = isset($request->ttd_2_by) ? $request->ttd_2_by : null;
        $note = isset($request->note) ? $request->note : null;

        DB::beginTransaction();
        try {
            $permohonan = Permohonan::findOrFail($idPermohonan);

            $status && $permohonan->status = $status;
            $tag && $permohonan->tag = $tag;
            $flag && $permohonan->flag = $flag;
            $jenis_limbah && $permohonan->jenis_limbah = $jenis_limbah;
            $sumber_radioaktif && $permohonan->sumber_radioaktif = $sumber_radioaktif;
            $jumlah && $permohonan->jumlah = $jumlah;
            $nomor_antrian && $permohonan->nomor_antrian = $nomor_antrian;
            $jadwal_id && $permohonan->jadwal_id = $jadwal_id;
            $no_bapeten && $permohonan->no_bapeten = $no_bapeten;
            $desc_biaya && $permohonan->jenis_layanan = $desc_biaya;
            $biaya && $permohonan->tarif = $biaya;
            isset($ttd_1) && ($ttd_1 == 'false' ? $permohonan->ttd_1 = null : $permohonan->ttd_1 = $ttd_1);
            isset($ttd_1_by) && ($ttd_1_by == 'false' ? $permohonan->ttd_1_by = null : $permohonan->ttd_1_by = decryptor($ttd_1_by));
            isset($ttd_2) && ($ttd_2 == 'false' ? $permohonan->ttd_2 = null : $permohonan->ttd_2 = $ttd_2);
            isset($ttd_2_by) && ($ttd_2_by == 'false' ? $permohonan->ttd_2_by = null : $permohonan->ttd_2_by = decryptor($ttd_2_by));

            $permohonan->update();

            // Add log permohonan Front desk
            if($ttd_1 && $ttd_1 != 'false') {
                $tmp_log = array(
                    'permohonan_id' => $idPermohonan,
                    'note' => 'Berkas permohonan lengkap',
                    'status' => 1,
                    'flag' => 1, // Front desk
                    'created_by' => Auth::user()->id
                );

                Detail_permohonan::create($tmp_log);
            } else if($note){
                $tmp_log = array(
                    'permohonan_id' => $idPermohonan,
                    'note' => $note,
                    'status' => $status ? $status : $permohonan->status,
                    'flag' => $permohonan->flag,
                    'created_by' => Auth::user()->id
                );

                Detail_permohonan::create($tmp_log);
            }


            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate!'
            ], 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
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

        // add to log
        $tmp_log = array(
            'permohonan_id' => $idPermohonan,
            'note' => $request->note,
            'status' => 9,
            'flag' => 1, // Front desk
            'created_by' => Auth::user()->id
        );

        Detail_permohonan::create($tmp_log);

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

        $arr = array(
            'no_kontrak' => $request->no_kontrak,
            'level' => 1,
            'active' => 9,
            'surat_tugas' => $surat_tugas,
            'created_by' => Auth::user()->id
        );

        $create = tbl_lhu::create($arr);

        if($create){
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
