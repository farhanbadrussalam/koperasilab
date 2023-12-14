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
        $data['token'] = generateToken();
        if(Auth::user()->hasRole('pelanggan')){
            return redirect('userProfile', $data);
        }else{
            return view('home', $data);
        }
    }

    public function login()
    {
        if(Auth::check()){
            $data['token'] = generateToken();
            if(Auth::user()->hasRole('pelanggan')){
                return redirect('userProfile', $data);
            }else{
                return view('home', $data);
            }
        }else{
            return view('auth.login');
        }
    }
}
