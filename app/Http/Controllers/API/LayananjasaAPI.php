<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;
use App\Models\User;
use App\Models\Layanan_jasa;
use Auth;
use DB;

class LayananjasaAPI extends Controller
{
    use RestApi;

    public function listLayananjasa(Request $request) {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';

        $layanan = Layanan_jasa::select([
            'id',
            'user_id',
            'nama_layanan',
            'biaya_layanan',
            'created_at',
        ])->when($search, function ($query) use ($search){
            return $query->where('nama_layanan', 'like', '%'. $search .'%');
        })
        ->with('user:id,name')
        ->where('status', 1)
        ->orderBy('created_at', 'DESC')
        ->offset(($page - 1) * $limit)
        ->limit($limit)
        ->paginate($limit);

        if(!Auth::user()->hasRole('Pelanggan')){
            $layanan->where('created_by', Auth::user()->id);
        }

        $arr = $layanan->toArray();
        $this->pagination = Arr::except($arr, 'data');
        return $this->output($layanan);
    }

    public function getLayananjasa(string $id){
        $id_layanan = decryptor($id);
        $layanan = Layanan_jasa::select([
            'id',
            'user_id',
            'nama_layanan',
            'biaya_layanan',
            'created_at',
            'satuankerja_id'
        ])->with('user:id,name', 'satuanKerja:id,name')
        ->where('id', $id_layanan)
        ->where('status', 1)
        ->first();

        return $this->output($layanan);
    }

    public function addLayananjasa(Request $request){
        DB::beginTransaction();
        try {
            $arrBiaya = array();
            $desc_biaya = json_decode($request->desc_biaya);
            $tarif = json_decode($request->tarif);
            foreach ($desc_biaya as $key => $desc) {
                $arrBiaya[$key] = array(
                    'desc' => $desc,
                    'tarif' => $tarif[$key]
                );
            }

            $data = array(
                'satuankerja_id' => decryptor($request->satuankerja),
                'user_id' => decryptor($request->pj),
                'nama_layanan' => $request->nama_layanan,
                'biaya_layanan' => json_encode($arrBiaya),
                'status' => 1,
                'created_by' => Auth::user()->id
            );

            $layanan = new Layanan_jasa();
            $layanan->fill($data);
            $layanan->save();
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

    public function updateLayananjasa(Request $request){
        DB::beginTransaction();
        try {
            $id = $request->layanan_hash ? decryptor($request->layanan_hash) : false;

            if($id){
                $arrBiaya = array();
                $desc_biaya = json_decode($request->desc_biaya);
                $tarif = json_decode($request->tarif);
                foreach ($desc_biaya as $key => $desc) {
                    $arrBiaya[$key] = array(
                        'desc' => $desc,
                        'tarif' => $tarif[$key]
                    );
                }

                $layanan = Layanan_jasa::findOrFail($id);

                $layanan->user_id = decryptor($request->pj);
                $layanan->nama_layanan = $request->nama_layanan;
                $layanan->biaya_layanan = json_encode($arrBiaya);

                $layanan->update();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Data updated successfully'
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'please check id to update data'
                ], 500);
            }
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function deleteLayananjasa($id){
        DB::beginTransaction();
        try {
            $id = decryptor($id);
            Layanan_jasa::findOrFail($id)->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!'
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

    public function getPegawai(Request $request){

        $credential = $request->validate([
            'role' => ['required']
        ]);

        if($credential){
            $satuanKerja = isset($request->satuankerja) ? decryptor($request->satuankerja) : null;
            $role = isset($request->role) ? $request->role : null;
            $pegawai = User::role($role);

            if($satuanKerja){
                $pegawai->where('satuankerja_id', $satuanKerja);
            }

            $dataPegawai = $pegawai->get();

            foreach ($dataPegawai as $key => $value) {
                $value->getDirectPermissions();
            }

            return response()->json(['data' => $dataPegawai], 200);
        }else{
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

    }

    public function delete(Request $request){
        $credential = $request->validate([
            'id' => ['required']
        ]);

        if($credential){
            $idHash = decryptor($request->id);
            $delete = Layanan_jasa::findOrFail($idHash);
            $delete->status = '99';
            $delete->update();

            return response()->json(['message' => 'Berhasil di hapus'], 200);
        }else{
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }
}
