<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Detail_permohonan;
use App\Models\tbl_media;
use App\Models\tbl_lhu;
use App\Models\jadwal;
use App\Models\Jadwal_petugas;
use Spatie\Permission\Models\Role;

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
                            'user:id,email,name', 'tbl_lhu', 'tbl_lhu.media', 'tbl_kip', 'tbl_kip.bukti')
                        ->where('id', $idHash)
                        ->orWhere('no_kontrak', $idHash)
                        ->first();

        // Mengambil data media
        $dokumen = json_decode($dataPermohonan->dokumen);
        $media = tbl_media::select('id','file_hash','file_ori','file_size','file_type','created_at')
                            ->whereIn('id', $dokumen)
                            ->get();
        $dataPermohonan->media = $media;

        // Mengambil data media petugas
        $detailPermohonan = Detail_permohonan::with('media')->where('permohonan_id', $idHash)->where('status', 1)->first();
        $dataPermohonan->detailPermohonan = $detailPermohonan;

        // Mengambil petugas
        $petugas = Jadwal_petugas::where('permohonan_id', $idHash)->where('status', 1)->where('petugas_id', Auth::user()->id)->first();
        $dataPermohonan->petugas = $petugas;

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

    public function updatePermohonan(Request $request){
        $validator = $request->validate([
            'id' => 'required',
            'status' => 'required',
            'note' => 'required',
            'file' => 'required'
        ]);

        $idPermohonan = decryptor($request->id);

        // upload file
        $dokumen = $request->file('file');
        $file = false;
        if($dokumen){
            $file = $this->media->upload($dokumen, 'permohonan');
        }

        // reset status detail to 99
        Detail_permohonan::where('permohonan_id', $idPermohonan)->update(['status' => '99']);
        // save to detail permohonan
        $flag = $request->status == 'setuju' ? 2 : 9;
        $tmpDetail = array(
            'permohonan_id' => $idPermohonan,
            'status' => 1,
            'flag' => $flag,
            'note' => $request->note,
            'surat_terbitan' => $file,
            'created_by' => Auth::user()->id
        );
        $createDetail = Detail_permohonan::create($tmpDetail);

        if($createDetail){
            $update = Permohonan::where('id', $idPermohonan)->update(array(
                'flag' => $flag
            ));

            $data = Permohonan::where('id', $idPermohonan)->select('created_by')->first();
            // Send notif
            $sendNotifPelanggan = notifikasi(array(
                'to_user' => $data->created_by,
                'type' => 'Permohonan'
            ), "Permohonan anda ". ($flag == 2 ? 'Disetujui' : 'Ditolak') . " oleh " . Auth::user()->name);

            // set payload
            $payload = array(
                'message' => 'Berhasil ' . ($flag == 2 ? 'Menyetujui' : 'Menolak') . 'permohonan ini'
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Gagal menyimpan'
            ], 400);
        }
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

    public function createJadwalPermohonan(Request $request)
    {
        $validator = $request->validate([
            'idPermohonan' => 'required'
        ]);

        $idPermohonan = decryptor($request->idPermohonan);

        $data_permohonan = Permohonan::with('jadwal', 'layananjasa')->where('id', $idPermohonan)->first();

        if($data_permohonan){
            // Mengurangi kuota jadwal
            $idJadwal = decryptor($data_permohonan->jadwal->jadwal_hash);
            $dataJadwal = jadwal::where('id', $idJadwal)->first();
            $dataJadwal->kuota = $dataJadwal->kuota-1;
            $dataJadwal->update();

            // Menerbitkan nomor antrian
            $ambilAntrian = Permohonan::where('jadwal_id', $idJadwal)
                ->where('status', '!=', '99')
                ->select('nomor_antrian')
                ->orderBy('nomor_antrian', 'DESC')
                ->first();

            if(!$ambilAntrian){
                $ambilAntrian = 1;
            }else{
                $ambilAntrian = (int)$ambilAntrian->nomor_antrian + 1;
            }

            $data_permohonan->nomor_antrian = $ambilAntrian;
            $data_permohonan->flag = 5;
            $data_permohonan->update();

            // save to detail permohonan
            // reset status detail to 99
            $reset = Detail_permohonan::where('permohonan_id', $idPermohonan)->update(['status' => '99']);

            Detail_permohonan::create(array(
                'permohonan_id' => $idPermohonan,
                'status' => 1,
                'flag' => 4,
                'note' => 'Jadwal berhasil dibuat, permohonan sedang dalam proses',
                'created_by' => Auth::user()->id
            ));

            // Send Notif ke manager
            $userManager = Role::whereIn('name', ['Manager'])->first();
            foreach ($userManager->users as $key => $user) {
                if($user->satuankerja_id == $data_permohonan->layananjasa->satuankerja_id){
                    $sendNotif = notifikasi(array(
                        'to_user' => $user->id,
                        'type' => 'JadwalPermohonan'
                    ), "Jadwal permohonan untuk Pelayanan ".$dataJadwal->layananjasa->nama_layanan." sudah dibuat");
                }
            }

            $payload = array(
                'message' => 'Berhasil membuat jadwal'
            );

            return $this->output($payload);
        }else{
            return response()->json([
                'message' => 'Gagal mengirim surat tugas'
            ], 400);
        }
    }
}
