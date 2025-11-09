<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

use App\Models\User;
use App\Models\notifikasi;
use App\Traits\RestApi;

use DB;
use Auth;

class NotifController extends Controller
{
    use RestApi;
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

    public function latestNotification(Request $request){
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $limit = $request->limit ?? 10;
            $type = $request->type ?? 'all';
            $latestNotification = $user->notifications()->latest()
                ->when($type, function($q, $type) use ($limit) {
                    if($type == 'unread'){
                        return $q->whereNull('read_at');
                    } else {
                        return $q->limit($limit);
                    }
                })->get();
            $unreadCount = notifUnreadCount();
            DB::commit();
            return $this->output(array(
                'list' => $latestNotification,
                'unreadCount' => $unreadCount
            ));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function markAllAsRead(Request $request){
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $user->unreadNotifications->markAsRead();
            DB::commit();
            return $this->output(array('msg' => 'Notifikasi berhasil dibaca'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
