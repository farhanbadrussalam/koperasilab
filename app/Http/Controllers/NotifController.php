<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\NotifikasiEvent;

class NotifController extends Controller
{
    public function notif() {
        $data = array(
            'to_user' => 7,
            'type' => 'jadwal'
        );
        return notifikasi($data, "Jadwal ditambahkan");
    }
}
