<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;

class ManagerPengajuanController extends Controller
{
    // index action
    public function index()
    {
        $data = [
            'title' => 'Invoice',
            'module' => 'manager-pengajuan'
        ];
        $cekNotifikasi = Auth::user()->unreadNotifications()->where('data->event', 'Keuangan')->first();

        if($cekNotifikasi) {
            $cekNotifikasi->markAsRead();
        }
        return view('pages.manager.pengajuan.index', $data);
    }

    public function indexSuratTugas()
    {
        $data = [
            'title' => 'Surat tugas',
            'module' => 'manager-suratTugas'
        ];

        $cekNotifikasi = Auth::user()->unreadNotifications()->where('data->event', 'SuratTugas')->first();

        if($cekNotifikasi) {
            $cekNotifikasi->markAsRead();
        }

        return view('pages.manager.suratTugas', $data);
    }

    public function indexSurpeng()
    {
        $data = [
            'title' => 'Surat Pengantar',
            'module' => 'manager-surpeng'
        ];

        $cekNotifikasi = Auth::user()->unreadNotifications()->where('data->event', 'Surpeng')->first();

        if($cekNotifikasi) {
            $cekNotifikasi->markAsRead();
        }

        return view('pages.manager.surpeng', $data);
    }
}
