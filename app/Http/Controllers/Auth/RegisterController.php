<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Profile;
use App\Models\Perusahaan;
use App\Models\Master_alamat;
use App\Models\Users_request;

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

    protected MediaController $mediaController;

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
            // 'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        if (env('APP_ENV') == 'production') {
            $arrValidation = array_merge($arrValidation, [
                'g-recaptcha-response' => ['required', 'captcha'],
            ]);
        }

        $arrMessage = messageSanity($arrValidation);

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
        DB::beginTransaction();
        try {
            // upload Surat Kuasa
            $suratKuasa = $this->mediaController->upload($data['uploadFile'], 'surat_kuasa');

            // Ambil ID Perusahaan (buat baru jika tipe pelanggan baru)
            $idPerusahaan = $this->resolvePerusahaan($data);

            // Buat User beserta Profile-nya
            $user = $this->createUserWithProfile($data, $suratKuasa->getIdMedia());

            $suratKuasa->store();

            // add to users_request
            Users_request::create([
                'id_perusahaan' => (int) $idPerusahaan,
                'id_user' => $user->id,
                'status' => 1,
                'jenis' => $data['pelanggan_tipe']
            ]);

            DB::commit();

            return $user;
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }

    /**
     * Resolve Perusahaan (Ambil ID jika lama, atau buat baru jika tipe pelanggan baru)
     */
    private function resolvePerusahaan(array $data)
    {
        if ($data['pelanggan_tipe'] === 'lama') {
            return decryptor($data['nama_instansi']);
        }

        $perusahaan = Perusahaan::create([
            'nama_perusahaan' => $data['nama_instansi'],
            'npwp_perusahaan' => $data['npwp'],
            'email' => $data['email_instansi'],
            'status' => 1
        ]);

        $arrAlamat = [
            [
                'id_perusahaan' => $perusahaan->id_perusahaan,
                'jenis' => 'Utama',
                'status' => 1,
                'alamat' => $data['alamat_instansi'],
                'kota' => $data['kota'],
                'kode_pos' => $data['kode_pos']
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

        foreach ($arrAlamat as $alamat) {
            Master_alamat::create($alamat);
        }

        return $perusahaan->id_perusahaan;
    }

    /**
     * Buat User beserta data Profile-nya
     */
    private function createUserWithProfile(array $data, $suratKuasaId)
    {
        $user = User::create([
            'name' => $data['nama_pic'],
            'status' => 1,
            'jabatan' => $data['jabatan_pic'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'realtime_notifications' => 0
        ])->assignRole('Pelanggan');

        $avatarId = null;
        if (request()->file('avatar')) {
            $avatarMedia = $this->mediaController->upload(request()->file('avatar'), 'avatar');
            $avatarMedia->store();
            $avatarId = $avatarMedia->getIdMedia();
        }

        Profile::create([
            'user_id' => $user->id,
            'nik' => $data['nik'],
            'no_hp' => $data['telepon'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'alamat' => $data['alamat'],
            'surat_kuasa' => $suratKuasaId,
            'avatar' => $avatarId
        ]);

        return $user;
    }
}
