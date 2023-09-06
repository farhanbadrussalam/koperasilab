<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Support\Facades\Session;

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
        $validator = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        if($validator){
            // Contoh: Lakukan login menggunakan metode bantu Fortify
            if (auth()->attempt($request->only('email', 'password'))) {
                return app(LoginResponse::class);
            }

            // Login gagal, tangani respons sesuai kebutuhan
            return redirect()->back()->withErrors(['email' => 'These credentials do not match our records.']);
        }
    }

    public function logout(Request $request){
        if ($request->user()->tokens()->where('id', Session::get('token_id'))) {
            $request->user()->tokens()->where('id', Session::get('token_id'))->delete();
        }
        Session::forget('token');
        Session::forget('token_id');
        auth()->logout();
        return app(LogoutResponse::class);
    }
}
