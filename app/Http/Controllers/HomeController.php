<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Spatie\Permission\Models\Role;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [
            'title' => 'Home',
            'module' => 'home'
        ];
        if(Auth::user()->hasRole('pelanggan')){
            return redirect('userProfile', $data);
        }else{
            return view('home', $data);
        }
    }

    public function login()
    {
        if(Auth::check()){
            $data = [
                'title' => 'layanan jasa'
            ];
            if(Auth::user()->hasRole('pelanggan')){
                return redirect('userProfile', $data);
            }else{
                $data['module'] = 'home';
                return redirect('home');
            }
        }else{
            return view('auth.login');
        }
    }
}
