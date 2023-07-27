<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

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
}
