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
            'title' => 'Invoice',
            'module' => 'manager-pengajuan'
        ];
        return view('pages.manager.pengajuan.index', $data);
    }

    public function indexSuratTugas()
    {
        $data = [
            'title' => 'Surat tugas',
            'module' => 'manager-suratTugas'
        ];

        return view('pages.manager.suratTugas', $data);
    }
}
