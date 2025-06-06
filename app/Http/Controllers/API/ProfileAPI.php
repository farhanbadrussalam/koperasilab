<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\User;
use App\Models\Master_alamat;
use App\Models\Perusahaan;
use App\Models\Profile;

use App\Http\Controllers\LogController;
use App\Http\Controllers\MediaController;

use Auth;
use DB;
use Hash;

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

            $idPerusahaan = $request->idPerusahaan ? decryptor($request->idPerusahaan) : false;

            $nik = $request->nik_pic ? $request->nik_pic : false;
            $name = $request->nama_pic ? $request->nama_pic : false;
            $jabatan = $request->jabatan_pic ? $request->jabatan_pic : false;
            $email = $request->email_pic ? $request->email_pic : false;
            $telepon = $request->telepon ? unmask($request->telepon) : false;
            $jenis_kelamin = $request->jenis_kelamin ? $request->jenis_kelamin : false;
            $alamat = $request->has('alamat_pic') ? $request->alamat_pic : false;
            $ttd = $request->has('ttd') ? $request->ttd : false;

            $params = array();
            $paramsProfile = array();
            $result = array();

            // Pengecekan perusahaan
            if (!$idPerusahaan) {
                if($request->idPerusahaan){
                    $perusahaan = Perusahaan::create([
                        'nama_perusahaan' => $request->idPerusahaan,
                    ]);

                    $this->tambahAlamat($perusahaan->id_perusahaan);

                    $idPerusahaan = $perusahaan->id_perusahaan;
                }
            } else {
                // pengecekan alamat
                $cekalamat = Master_alamat::where('id_perusahaan', $idPerusahaan)->get();

                if (count($cekalamat) == 0) {
                    $this->tambahAlamat($idPerusahaan);
                }
            }

            // User
            $name && $params['name'] = $name;
            $jabatan && $params['jabatan'] = $jabatan;
            $request->has('ttd') && $params['ttd'] = $ttd;
            $email && $params['email'] = $email;
            $name && $params['name'] = $name;
            $jabatan && $params['jabatan'] = $jabatan;
            $idPerusahaan && $params['id_perusahaan'] = $idPerusahaan;

            // profile
            $nik && $paramsProfile['nik'] = $nik;
            $jenis_kelamin && $paramsProfile['jenis_kelamin'] = $jenis_kelamin;
            $alamat && $paramsProfile['alamat'] = $alamat;
            $telepon && $paramsProfile['no_hp'] = $telepon;

            User::where('id', $idProfile)->update($params);
            Profile::where('user_id', $idProfile)->update($paramsProfile);

            $result['status'] = 'updated';
            $result['msg'] = 'Profile berhasil diupdate';
            $result['data'] = User::with('profile', 'perusahaan', 'perusahaan.alamat')->where('id', $idProfile)->first();

            DB::commit();

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    private function tambahAlamat($idPerusahaan) {
        // set alamat
        $arrJenisAlamat = ['tld', 'lhu', 'invoice'];

        $arrAlamat = array();
        $arrAlamat[] = array(
            'id_perusahaan' => $idPerusahaan,
            'jenis' => 'Utama',
            'status' => 1,
            'alamat' => null,
            'kode_pos' => null
        );
        foreach ($arrJenisAlamat as $key => $value) {
            $arrAlamat[] = array(
                'id_perusahaan' => $idPerusahaan,
                'jenis' => $value,
                'status' => 0,
                'alamat' => null,
                'kode_pos' => null
            );
        }

        Master_alamat::insert($arrAlamat);
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

    public function changePassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $idProfile = decryptor($request->idProfile);
            $oldPassword = $request->old_password ? $request->old_password : false;
            $newPassword = $request->new_password ? $request->new_password : false;

            $user = User::findOrFail($idProfile);

            if($user->password != null){
                if (!Hash::check($oldPassword, $user->password)) {
                    return $this->output(array('msg' => 'Password lama salah', 'status' => 'fail'));
                }
            }

            $params = [
                'password' => Hash::make($newPassword)
            ];

            $user->update($params);

            DB::commit();

            $result = array(
                'status' => 'updated',
                'msg' => 'Password berhasil diubah'
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
        $limit = $request->has('limit') ? $request->limit : false;
        $page = $request->has('page') ? $request->page : 1;

        DB::beginTransaction();
        try {
            $query = Perusahaan::with('users')->when($search, function($q, $search){
                return $q->where('nama_perusahaan', 'like', "%$search%");
            });

            if($limit){
                $data = $query->offset(($page - 1) * $limit)->limit($limit)->paginate($limit);
                $query = $data->toArray();
                $this->pagination = Arr::except($query, 'data');
            }else{
                $query = $query->get();
            }

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
