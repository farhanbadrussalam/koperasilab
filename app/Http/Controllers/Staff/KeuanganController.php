<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;

class KeuanganController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Keuangan',
            'module' => 'staff-keuangan'
        ];
        return view('pages.staff.keuangan.index', $data);
    }
}
