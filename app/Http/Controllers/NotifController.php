<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

use App\Models\User;
use App\Models\notifikasi;

class NotifController extends Controller
{
    public function notif() {
        $data = array(
            'to_user' => 7,
            'type' => 'jadwal'
        );
        return notifikasi($data, "Jadwal ditambahkan");
    }

    public function read(Request $request){
        $id = $request->id ?? null;
        $event = $request->event ?? null;
        $event_id = $request->event_id ?? null;
        $user_id = $request->user_id ?? null;

        // cari notifikasi
        $notif = DatabaseNotification::when($id, function($query, $id){
            return $query->where('id', $id);
        })->when($event, function($query, $event){
            return $query->where('data->event', $event);
        })->when($event_id, function($query, $event_id){
            return $query->where('data->id_event', $event_id);
        })->when($user_id, function($query, $user_id){
            return $query->where('notifiable_id', $user_id);
        })->whereNull('read_at')
        ->update(['read_at' => now()]);

        return $notif;
    }
}
