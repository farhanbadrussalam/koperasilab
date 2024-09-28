<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagerPengajuanController extends Controller
{
    // index action
    public function index()
    {
        $data = [
            'title' => 'Manager',
            'module' => 'manager-pengajuan'
        ];
        return view('pages.manager.pengajuan.index', $data);
    }
}
