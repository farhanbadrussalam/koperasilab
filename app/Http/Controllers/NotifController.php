<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\NotifikasiEvent;

class NotifController extends Controller
{
    public function notif() {
        event(new NotifikasiEvent("hallo kawanku"));
    }
}
