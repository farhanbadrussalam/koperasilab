<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Profile;
use App\Models\Perusahaan;
use App\Models\Master_alamat;

use App\Http\Controllers\MediaController;

use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->mediaController = resolve(MediaController::class);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nama_instansi' => ['required', 'string', 'max:255'],
            'nama_pic' => ['required', 'string', 'max:255'],
            'nik' => ['required'],
            'telepon' => ['required'],
            'jenis_kelamin' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // 'avatar' => 'required|image|mimes:jpeg,png,jpg,gif',//|max:2048
            'g-recaptcha-response' => 'required|captcha',
            'kode_pos' => ['required'],
            'alamat' => ['required']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Pengecekan Instansi
        $dataPerusahaan = false;
        $idPerusahaan = decryptor($data['nama_instansi']);
        
        if(!$idPerusahaan) {
            $dataPerusahaan = Perusahaan::create([
                'nama_perusahaan' => $data['nama_instansi'],
                'npwp_perusahaan' => $data['npwp'],
                'email' => $data['email_instansi'],
                'status' => 1
            ]);

            $idPerusahaan = decryptor($dataPerusahaan->perusahaan_hash);

            // set alamat
            $arrJenisAlamat = ['tld', 'lhu', 'invoice'];

            $arrAlamat = array();
            $arrAlamat[] = array(
                'id_perusahaan' => $idPerusahaan,
                'jenis' => 'Utama',
                'status' => 1,
                'alamat' => $data['alamat'],
                'kode_pos' => $data['kode_pos']
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
        } else {
            $dataPerusahaan = Perusahaan::where('id_perusahaan', $idPerusahaan)->first();
        }

        if($dataPerusahaan){
            $user = User::create([
                'name' => $data['nama_pic'],
                'id_perusahaan'=> $idPerusahaan,
                'nik' => $data['nik'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'status' => 1,
                'jabatan' => $data['jabatan_pic'],
                'telepon' => $data['telepon'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ])->assignRole('Pelanggan');
        }

        return $user;
    }
}
