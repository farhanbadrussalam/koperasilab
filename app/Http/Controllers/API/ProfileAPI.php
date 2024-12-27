<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\User;
use App\Models\Master_alamat;
use App\Models\Perusahaan;

use App\Http\Controllers\LogController;
use App\Http\Controllers\MediaController;

use Auth;
use DB;

class ProfileAPI extends Controller
{
    use RestApi;

    public function __construct()
    {
        $this->log = resolve(LogController::class);
        $this->media = resolve(MediaController::class);
    }

    public function actionProfile(Request $request)
    {
        DB::beginTransaction();
        try {
            $idProfile = $request->idProfile ? decryptor($request->idProfile) : false;

            $name = $request->nama_pic ? $request->nama_pic : false;
            $jabatan = $request->jabatan_pic ? $request->jabatan_pic : false;
            $email = $request->email ? $request->email : false;
            $telepon = $request->telepon ? unmask($request->telepon) : false;
            $npwp = $request->npwp ? unmask($request->npwp) : false;

            // $ktp = $request->file('ktp');
            // $npwp = $request->file('npwp');
            // $file_ktp = false;
            // $file_npwp = false;

            // if($ktp){
            //     $file_ktp = $this->media->upload($ktp, 'profile');
            // }

            // if($npwp){
            //     $file_npwp = $this->media->upload($npwp, 'profile');
            // }

            $params = array();
            $result = array();

            $name && $params['name'] = $name;
            $jabatan && $params['jabatan'] = $jabatan;
            $email && $params['email'] = $email;
            $telepon && $params['telepon'] = $telepon;
            $npwp && $params['npwp'] = $npwp;
            // $file_ktp && $params['ktp'] = $file_ktp->getIdMedia();
            // $file_npwp && $params['npwp'] = $file_npwp->getIdMedia();

            $user = User::where('id', $idProfile)->first();
            if($user){
                $user->update($params);
                $result['status'] = 'updated';
                $result['msg'] = 'Profile berhasil diupdate';
            }else{
                $result['status'] = 'fail';
                $result['msg'] = 'User tidak ditemukan';
            }

            // if($result['status'] == 'updated'){
            //     if($file_ktp){
            //         $file_ktp->store();
            //     }

            //     if($file_npwp){
            //         $file_npwp->store();
            //     }
            // }

            DB::commit();

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function actionAlamat(Request $request)
    {
        DB::beginTransaction();
        try {
            $idAlamat = $request->idAlamat ? decryptor($request->idAlamat) : false;

            $profile = Master_alamat::findOrFail($idAlamat);
    
            $params = array();
    
            $status = $request->has('status') ? $request->status : 99;
            $alamat = $request->has('alamat') ? $request->alamat : false;
            $kode_pos = $request->has('kode_pos') ? $request->kode_pos : false;
    
            $status != 99 && $params['status'] = $status;
            $alamat && $params['alamat'] = $alamat;
            $kode_pos && $params['kode_pos'] = $kode_pos;
    
            $profile->update($params);

            DB::commit();
    
            $result = array(
                'status' => 'change'
            );
            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function actionPerusahaan(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPerusahaan = $request->idPerusahaan ? decryptor($request->idPerusahaan) : false;
            $kodePerusahaan = $request->kodePerusahaan ? $request->kodePerusahaan : false;
            $nama_perusahaan = $request->has('nama_perusahaan') ? $request->nama_perusahaan : false;
            $npwp = $request->has('npwp_perusahaan') ? unmask($request->npwp) : false;
            $email = $request->has('email') ? $request->email : false;

            $perusahaan = Perusahaan::findOrFail($idPerusahaan);

            $params = array();

            $kodePerusahaan && $params['kode_perusahaan'] = $kodePerusahaan;
            $nama_perusahaan && $params['nama_perusahaan'] = $nama_perusahaan;
            $npwp && $params['npwp'] = $npwp;
            $email && $params['email'] = $email;

            $perusahaan->update($params);

            DB::commit();

            $result = array(
                'status' => 'change'
            );
            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    public function getListPerusahaan(Request $request)
    {
        $search = $request->has('search') ? $request->search : '';

        DB::beginTransaction();
        try {
            $query = Perusahaan::with('users')->when($search, function($q, $search){
                            return $q->where('nama_perusahaan', 'like', "%$search%");
                        })
                        ->get();

            return $this->output($query, 200);

        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPerusahaanByKode($kode){
        DB::beginTransaction();
        try {
            $query = Perusahaan::where('kode_perusahaan', $kode)->first();

            return $this->output($query, 200);

        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
