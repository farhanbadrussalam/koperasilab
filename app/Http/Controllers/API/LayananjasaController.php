<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class LayananjasaController extends Controller
{
    public function getPegawai(){
        $pegawai = User::role('Staff')->get();
        
        
    }
}
