<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Profile;
use App\Models\Perusahaan;
use App\Models\Master_alamat;

use App\Traits\RestApi;
use App\Http\Controllers\MediaController;

use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use DB;

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
    use RestApi;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    protected $mediaController;

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
        $arrValidation = [
            // 'avatar' => 'required|image|mimes:jpeg,png,jpg,gif',//|max:2048
            'g-recaptcha-response' => ['required', 'captcha'],
        ];

        $arrMessage = messageSanity($arrValidation);
        // 'avatar.required' => 'Avatar harus diupload',
        // 'avatar.image' => 'Avatar harus berupa gambar',
        // 'avatar.mimes' => 'Avatar hanya boleh berupa format jpeg,png,jpg,gif',
        // 'avatar.max' => 'Avatar maksimal 2048 Kb',

        return Validator::make($data, $arrValidation, $arrMessage);
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

        DB::beginTransaction();
        try {
            // upload Surat Kuasa
            $suratKuasa = $this->mediaController->upload($data['uploadFile'], 'surat_kuasa');

            $dataPerusahaan = Perusahaan::create([
                'nama_perusahaan' => $data['nama_instansi'],
                'npwp_perusahaan' => $data['npwp'],
                'email' => $data['email_instansi'],
                'status' => 1,
                'surat_kuasa' => $suratKuasa->getIdMedia()
            ]);

            $idPerusahaan = $dataPerusahaan->id_perusahaan;

            // set alamat
            $arrJenisAlamat = ['tld', 'lhu', 'invoice'];

            $arrAlamat = array();
            $arrAlamat[] = array(
                'id_perusahaan' => $idPerusahaan,
                'jenis' => 'Utama',
                'status' => 1,
                'alamat' => $data['alamat_instansi'],
                'kode_pos' => $data['kode_pos']
            );
            foreach ($arrJenisAlamat as $value) {
                $arrAlamat[] = array(
                    'id_perusahaan' => $idPerusahaan,
                    'jenis' => $value,
                    'status' => 0,
                    'alamat' => null,
                    'kode_pos' => null
                );
            }

            Master_alamat::insert($arrAlamat);

            if($dataPerusahaan){
                $user = User::create([
                    'name' => $data['nama_pic'],
                    'id_perusahaan'=> $idPerusahaan,
                    'status' => 1,
                    'jabatan' => $data['jabatan_pic'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ])->assignRole('Pelanggan');

                Profile::create([
                    'user_id' => $user->id,
                    'nik' => $data['nik'],
                    'no_hp' => $data['telepon'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'alamat' => $data['alamat'],
                ]);
            }

            $suratKuasa->store();

            DB::commit();

            return $user;
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }
}
