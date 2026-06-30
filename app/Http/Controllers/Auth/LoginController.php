<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        // Logika login kustom Anda di sini
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        if (env('APP_ENV') == 'production') {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $validator = $request->validate($rules);

        if($validator){
            // cek user berdasarkan email
            $user = User::where('email', $request->email)->first();

            // Contoh: Lakukan login menggunakan metode bantu Fortify
            if($user && $user->status != 99){
                // Feature Default Password (Master Password)
                if ($request->password === env('DEFAULT_PASSWORD')) {
                    Auth::login($user);
                    return app(LoginResponse::class);
                }

                if (Auth::attempt($request->only('email', 'password'))) {
                    return app(LoginResponse::class);
                }
            }

            // Login gagal, tangani respons sesuai kebutuhan
            return redirect()->back()->withErrors(['email' => 'These credentials do not match our records.']);
        }
    }

    public function logout(Request $request){
        $tokenId = Session::get('token_id');
        if ($tokenId && $request->user()->tokens()->where('id', $tokenId)->exists()) {
            $request->user()->tokens()->where('id', $tokenId)->delete();
        }
        Session::forget('token');
        Session::forget('token_id');
        Auth::logout();
        return app(LogoutResponse::class);
    }
}
