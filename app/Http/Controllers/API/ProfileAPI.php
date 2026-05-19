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
use App\Models\Users_request;

use App\Http\Controllers\LogController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\API\SendMailAPI;

use Auth;
use DB;
use Hash;

class ProfileAPI extends Controller
{
    use RestApi;
    protected LogController $log;
    protected MediaController $media;
    protected mixed $pagination = [];
    protected SendMailAPI $mail;

    public function __construct()
    {
        $this->log = resolve(LogController::class);
        $this->media = resolve(MediaController::class);
        $this->mail = resolve(SendMailAPI::class);
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

            // pengecekan apakah nik sudah ada atau belum
            if ($nik) {
                $cekNik = Profile::where('nik', $nik)->whereNot('user_id', $idProfile)->first();
                if ($cekNik) {
                    return $this->output(array('msg' => 'NIK sudah terdaftar'), 'Fail', 200);
                }
            }

            // Pengecekan perusahaan
            if (!$idPerusahaan) {
                if ($request->idPerusahaan) {
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

            if ($params['ttd']) {
                $params['ttd'] = uploadSignatur($params['ttd'], Auth::user());
            }

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

    private function tambahAlamat(int $idPerusahaan)
    {
        // set alamat
        $arrJenisAlamat = ['tld', 'lhu', 'invoice'];

        $arrAlamat = array();
        $arrAlamat[] = array(
            'id_perusahaan' => $idPerusahaan,
            'jenis' => 'Utama',
            'status' => 1,
            'alamat' => null,
            'kota' => 'Jakarta',
            'kode_pos' => null
        );
        foreach ($arrJenisAlamat as $key => $value) {
            $arrAlamat[] = array(
                'id_perusahaan' => $idPerusahaan,
                'jenis' => $value,
                'status' => 0,
                'alamat' => null,
                'kota' => 'Jakarta',
                'kode_pos' => null
            );
        }

        Master_alamat::insert($arrAlamat);
    }

    private function send_password(string $password, string $to)
    {
        $contentMail = "
            <div style='text-align: center; margin: 20px; background-color: #f5f5f5; padding: 20px;'>
                <h1>Nuklindo Lab</h1>
                <p>
                    Harap segera login ke akun anda untuk melakukan verifikasi akun dan mengganti password<br>
                    Gunanakannya password ini untuk login: <br><br><strong>$password</strong><br><br>
                    Password ini akan digunakan untuk login ke akun anda.<br>
                    Mohon untuk tidak merespon email ini, Terimakasih.
                </p>
            </div>
        ";
        $this->mail->send_mail($contentMail, $to, "Password PIC");
    }

    public function changePIC(Request $request)
    {
        $nik = $request->nik_pic ? $request->nik_pic : false;
        $name = $request->nama_pic ? $request->nama_pic : false;
        $jabatan = $request->jabatan ? $request->jabatan : false;
        $email = $request->email_pic ? $request->email_pic : false;
        $telepon = $request->telepon ? unmask($request->telepon) : false;
        $jenis_kelamin = $request->jenis_kelamin ? $request->jenis_kelamin : false;
        $alamat = $request->has('alamat') ? $request->alamat : false;
        $password = $request->has('password') ? $request->password : false;
        $surat_kuasa = $request->has('surat_kuasa_pic') ? $request->surat_kuasa_pic : false;
        $id_perusahaan = $request->id_perusahaan ? decryptor($request->id_perusahaan) : false;

        DB::beginTransaction();
        try {
            $params = array();
            $fileUpload = $this->media->upload($surat_kuasa, 'surat_kuasa');

            $nik && $params['nik'] = $nik;
            $name && $params['name'] = $name;
            $jabatan && $params['jabatan'] = $jabatan;
            $email && $params['email'] = $email;
            $telepon && $params['telepon'] = $telepon;
            $jenis_kelamin && $params['jenis_kelamin'] = $jenis_kelamin;
            $alamat && $params['alamat'] = $alamat;

            $password && $params['password'] = Hash::make($password);

            //cek role bukan pelanggan
            if (!Auth::user()->hasRole('Pelanggan')) {
                // buat password random string 8 karakter ada capital dan huruf kecil
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                $password = '';
                for ($i = 0; $i < 8; $i++) {
                    $password .= $characters[rand(0, strlen($characters) - 1)];
                }
                $password = str_shuffle($password); // shuffle the string to make it more random

                $params['password'] = Hash::make($password);
            }

            $params['id_perusahaan'] = $id_perusahaan ? $id_perusahaan : Auth::user()->id_perusahaan;
            $params['surat_kuasa'] = $fileUpload->getIdMedia();

            $arr_update = [
                "status" => 2,
                'selesai_at' => date('Y-m-d H:i:s')
            ];

            if (Auth::user()->hasRole('Pelanggan')) {
                Auth::user()->update($arr_update);
            } else {
                // mengambil pic yang aktif
                User::where('id_perusahaan', $params['id_perusahaan'])->where('status', 1)->update($arr_update);
            }

            $user = User::create([
                'name' => $params['name'],
                'id_perusahaan' => $params['id_perusahaan'],
                'status' => 1,
                'jabatan' => $params['jabatan'],
                'email' => $params['email'],
                'password' => $params['password'],
                'realtime_notifications' => 0
            ])->assignRole('Pelanggan');

            Profile::create([
                'user_id' => $user->id,
                'nik' => $params['nik'],
                'no_hp' => $params['telepon'],
                'jenis_kelamin' => $params['jenis_kelamin'],
                'alamat' => $params['alamat'],
                'surat_kuasa' => $params['surat_kuasa'],
            ]);

            $fileUpload->store();

            $result['status'] = 'updated';
            $result['msg'] = 'PIC berhasil diganti';

            DB::commit();

            if (!Auth::user()->hasRole('Pelanggan')) {
                $this->send_password($password, $email);
            }

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
            $kota = $request->has('kota') ? $request->kota : false;
            $kode_pos = $request->has('kode_pos') ? $request->kode_pos : false;

            $status != 99 && $params['status'] = $status;
            $alamat && $params['alamat'] = $alamat;
            $kota && $params['kota'] = $kota;
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

    public function actionTambahSemuaAlamat(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPerusahaan = decryptor($request->idPerusahaan);
            
            $arrAlamat = [];
            $jenis_arr = ['utama' => 'Utama', 'tld' => 'TLD', 'lhu' => 'LHU', 'invoice' => 'Invoice'];
            
            foreach($jenis_arr as $key => $val) {
                if ($key == 'utama') {
                    $status = 1;
                    $alamat = $request->input("alamat_$key");
                    $kota = $request->input("kota_$key");
                    $kode_pos = $request->input("kode_pos_$key");
                } else {
                    $status = $request->input("status_$key") == 1 ? 1 : 0;
                    if ($status == 1) {
                        $alamat = $request->input("alamat_$key");
                        $kota = $request->input("kota_$key");
                        $kode_pos = $request->input("kode_pos_$key");
                    } else {
                        $alamat = null;
                        $kota = null;
                        $kode_pos = null;
                    }
                }
                
                $arrAlamat[] = [
                    'id_perusahaan' => $idPerusahaan,
                    'jenis' => $val,
                    'status' => $status,
                    'alamat' => $alamat,
                    'kota' => $kota,
                    'kode_pos' => $kode_pos
                ];
            }
            
            // Hapus alamat lama jika ada untuk mencegah duplikat/sisa
            Master_alamat::where('id_perusahaan', $idPerusahaan)->delete();
            
            Master_alamat::insert($arrAlamat);
            DB::commit();
            
            return $this->output(['status' => 'success', 'msg' => 'Data alamat berhasil disimpan']);
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
            $npwp = $request->has('npwp_perusahaan') ? unmask($request->npwp_perusahaan) : false;
            $email = $request->has('email') ? $request->email : false;
            $alamat = $request->has('alamat') ? $request->alamat : false;
            $kota = $request->has('kota') ? $request->kota : false;
            $kode_pos = $request->has('kode_pos') ? $request->kode_pos : false;

            $params = array();

            $kodePerusahaan && $params['kode_perusahaan'] = $kodePerusahaan;
            $nama_perusahaan && $params['nama_perusahaan'] = $nama_perusahaan;
            $npwp && $params['npwp_perusahaan'] = $npwp;
            $email && $params['email'] = $email;

            if ($idPerusahaan) {
                $perusahaan = Perusahaan::findOrFail($idPerusahaan);
                $perusahaan->update($params);
            } else {
                $perusahaan = Perusahaan::create($params);

                $arrJenisAlamat = ['tld', 'lhu', 'invoice'];
                $arrAlamat = array();
                $arrAlamat[] = array(
                    'id_perusahaan' => $perusahaan->id_perusahaan,
                    'jenis' => 'Utama',
                    'status' => 1,
                    'alamat' => $alamat,
                    'kota' => $kota,
                    'kode_pos' => $kode_pos
                );
                foreach ($arrJenisAlamat as $value) {
                    $arrAlamat[] = array(
                        'id_perusahaan' => $perusahaan->id_perusahaan,
                        'jenis' => $value,
                        'status' => 0,
                        'alamat' => null,
                        'kode_pos' => null
                    );
                }

                Master_alamat::insert($arrAlamat);
                Auth::user()->update(['id_perusahaan' => $perusahaan->id_perusahaan]);
            }

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

    public function actionAjukanInstansi(Request $request)
    {
        DB::beginTransaction();
        try {
            $tipe = $request->pelanggan_tipe;

            if ($tipe === 'lama') {
                $idPerusahaan = decryptor($request->nama_instansi_lama);
            } else {
                $perusahaan = Perusahaan::create([
                    'nama_perusahaan' => $request->nama_instansi,
                    'npwp_perusahaan' => $request->npwp ? unmask($request->npwp) : null,
                    'email' => $request->email_instansi,
                    'status' => 1
                ]);

                $arrAlamat = [
                    [
                        'id_perusahaan' => $perusahaan->id_perusahaan,
                        'jenis' => 'Utama',
                        'status' => 1,
                        'alamat' => $request->alamat_instansi,
                        'kota' => $request->kota,
                        'kode_pos' => $request->kode_pos
                    ]
                ];

                foreach (['tld', 'lhu', 'invoice'] as $jenis) {
                    $arrAlamat[] = [
                        'id_perusahaan' => $perusahaan->id_perusahaan,
                        'jenis' => $jenis,
                        'status' => 0,
                        'alamat' => null,
                        'kode_pos' => null
                    ];
                }

                Master_alamat::insert($arrAlamat);
                $idPerusahaan = $perusahaan->id_perusahaan;
            }

            $findUserRequest = Users_request::where('id_user', Auth::user()->id)->where('status', 90)->first();
            if ($findUserRequest) {
                $findUserRequest->update([
                    'status' => 1,
                    'id_perusahaan' => (int) $idPerusahaan,
                    'jenis' => $tipe
                ]);
            } else {
                Users_request::create([
                    'id_user' => Auth::user()->id,
                    'status' => 1,
                    'id_perusahaan' => (int) $idPerusahaan,
                    'jenis' => $tipe
                ]);
            }
            DB::commit();

            return $this->output([
                'status' => 'success',
                'msg' => 'Pengajuan instansi berhasil dikirim'
            ]);
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

            if ($user->password != null) {
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
        $limit = $request->has('limit') ? $request->limit : false;
        $page = $request->has('page') ? $request->page : 1;
        $filter = $request->has('filter') ? $request->filter : [];

        DB::beginTransaction();
        try {
            $query = Perusahaan::with('users:id,name,email')->select('id_perusahaan', 'nama_perusahaan', 'kode_perusahaan')
                ->when($filter, function ($q, $filter) {
                    foreach ($filter as $key => $value) {
                        if ($key == 'search') {
                            $q->where('nama_perusahaan', 'like', "%$value%")
                                ->orWhere('kode_perusahaan', 'like', "%$value%");
                        }
                    }
                })->orderBy('id_perusahaan', 'desc');

            if ($limit) {
                $data = $query->offset(($page - 1) * $limit)->limit($limit)->paginate($limit);
                $query = $data->toArray();
                $this->pagination = Arr::except($query, 'data');
            } else {
                $query = $query->get();
            }

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPerusahaanByKode(string $kode)
    {
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

    public function getPerusahaanById(string $id)
    {
        DB::beginTransaction();
        try {
            $id = decryptor($id);
            $query = Perusahaan::with(
                'users',
                'users.profile',
                'users.profile.suratkuasa',
                'alamat'
            )->where('id_perusahaan', $id)->first();

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadSuratKuasa(Request $request)
    {
        $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idUser = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'surat_kuasa');
            $dataUser = Profile::where('user_id', $idUser);

            if (isset($dataUser)) {
                $update = $dataUser->update(array('surat_kuasa' => $fileUpload->getIdMedia()));

                DB::commit();

                if ($update) {
                    $fileUpload->store();
                    // ambil media faktur
                    $mediaFaktur = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Surat kuasa berhasil diupload', 'data' => $mediaFaktur));
                }

                return $this->output(array('msg' => 'Surat kuasa gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'Surat kuasa gagal diupload'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroySuratKuasa(string $idHash, string $idMedia)
    {
        $idMedia = decryptor($idMedia);
        $idHash = decryptor($idHash);

        DB::beginTransaction();
        try {
            $idUser = $idHash;
            $dataUser = Profile::where('user_id', $idUser);

            if (isset($dataUser)) {
                $update = $dataUser->update(array('surat_kuasa' => null));
                $this->media->destroy($idMedia);
                DB::commit();

                if ($update) {
                    return $this->output(array('msg' => 'Surat kuasa berhasil dihapus'));
                }

                return $this->output(array('msg' => 'Surat kuasa gagal dihapus'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'Surat kuasa gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getHistoryPic(string $idPerusahaan)
    {
        DB::beginTransaction();
        try {
            $idPerusahaan = decryptor($idPerusahaan);
            $query = User::where('id_perusahaan', $idPerusahaan)->orderByRaw('selesai_at IS NULL DESC, selesai_at DESC')->get();

            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadStempel(Request $request)
    {
        // TODO: Implement uploadStempel() method.
        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $idPerusahaan = decryptor($request->idHash);

            $fileUpload = $this->media->upload($file, 'stempel');
            Perusahaan::where('id_perusahaan', $idPerusahaan)->update(array('stempel' => $fileUpload->getIdMedia()));
            DB::commit();

            $fileUpload->store();

            $mediaStempel = $this->media->get($fileUpload->getIdMedia());
            return $this->output(array('msg' => 'Stempel berhasil diupload', 'data' => $mediaStempel));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyStempel(string $idHash, string $idMedia)
    {
        $idMedia = decryptor($idMedia);
        $idHash = decryptor($idHash);

        DB::beginTransaction();
        try {
            $idPerusahaan = $idHash;
            $update = Perusahaan::where('id_perusahaan', $idPerusahaan)->update(array('stempel' => null));
            $this->media->destroy($idMedia);
            DB::commit();

            if ($update) {
                return $this->output(array('msg' => 'Stempel berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Stempel gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
