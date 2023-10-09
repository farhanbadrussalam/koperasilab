<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;

use Auth;
use DataTables;

class FrontdeskController extends Controller
{
    public function index(){
        $data['token'] = generateToken();
        return view('pages.frontdesk.index', $data);
    }

    public function getData(){
        $user = Auth::user();

        $informasi = Permohonan::with(['layananjasa', 'jadwal'])
                        ->where('status', '!=', 99)
                        ->where('flag', 1)->get();
        dd($informasi);
    }
}
