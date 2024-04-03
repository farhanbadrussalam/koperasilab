<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\jadwal;
use App\Models\Jadwal_petugas;
use App\Models\Detail_permohonan;
use App\Traits\RestApi;
use Auth;
use DB;

class JadwalAPI extends Controller
{
    use RestApi;

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
        DB::beginTransaction();
        try {
            $petugas = $request->idPetugas;
            $jobsPetugas = $request->jobsPetugas;

            $dataJadwal = array(
                'permohonan_id' => decryptor($request->idPermohonan),
                'date_mulai' => $request->date_mulai,
                'date_selesai' => $request->date_end,
                'ttd_1' => $request->ttd_1,
                'ttd_1_by' => decryptor($request->ttd_1_by),
                'created_by' => Auth::user()->id,
                'status' => 1
            );

            $dataJadwal = jadwal::create($dataJadwal);

            foreach ($petugas as $key => $value) {
                $detailPetugas = array(
                    'jadwal_id' => $dataJadwal->id,
                    'petugas_id' => $value,
                    'jobs' => $jobsPetugas[$key],
                    'status' => 1
                );
                jadwal_petugas::create($detailPetugas);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Surat tugas berhasil dikirim!'
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
        $data['jadwal'] = jadwal::with('layananjasa','media')->where('id', $idHash)->first();
        $data['petugas'] = Jadwal_petugas::where('jadwal_id', $data['jadwal']->id)->where('petugas_id', Auth::user()->id)->first();

        return response()->json(['data' => $data], 200);
    }

    public function getJadwal(Request $request)
    {
        $id_layanan = isset($request->idLayanan) ? decryptor($request->idLayanan) : null;
        $jenis_layanan = isset($request->jenisLayanan) ? $request->jenisLayanan : null;

        $jadwal = jadwal::where('kuota', '!=', 0);
        // Pengecekan petugas ready

        if($id_layanan){
            $jadwal->where('layananjasa_id', $id_layanan);
        }
        if($jenis_layanan){
            $jadwal->where('jenislayanan', $jenis_layanan);
        }
        $data = $jadwal->get();
        return response()->json(['data' => $data], 200);
    }

    public function getJadwalPetugas(Request $request)
    {
        $idJadwal = $request->idJadwal ? decryptor($request->idJadwal) : null;

        if($idJadwal){
            $jadwal = jadwal::findOrFail($idJadwal);
            $data['petugas'] = Jadwal_petugas::with('petugas')->where('jadwal_id', $idJadwal)->get();
            $data['pj'] = encryptor($jadwal->layananjasa->user_id);

            return response()->json(['data' => $data], 200);
        }

        return response()->json(['message' => 'Id jadwal not null'], 500);
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
        $idjadwal = decryptor($id);
        $delete = jadwal::findOrFail($idjadwal);
        $delete->status = '99';
        $delete->update();

        return response()->json(['message' => 'Berhasil di hapus'], 200);
    }

    public function confirm(Request $request){
        $validator = $request->validate([
            'idJadwal' => ['required'],
            'answer' => ['required']
        ]);
    }
}
