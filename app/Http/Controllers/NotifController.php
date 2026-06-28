<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Traits\RestApi;

use DB;
use Auth;

class NotifController extends Controller
{
    use RestApi;
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
            $page = intval($request->page ?? 1);
            $limit = intval($request->limit ?? 10);
            $offset = ($page - 1) * $limit;
            $type = $request->type ?? 'all';

            $latestNotification = $user->notifications()->latest()
                ->when($type == 'unread', function($q) {
                    return $q->whereNull('read_at');
                })
                ->skip($offset)
                ->take($limit)
                ->get();

            $unreadCount = notifUnreadCount();
            DB::commit();
            return $this->output(array(
                'list' => $latestNotification,
                'unreadCount' => $unreadCount,
                'hasMore' => count($latestNotification) === $limit,
                'page' => $page
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
            $user->unreadNotifications()->update(['read_at' => now()]);
            DB::commit();
            return $this->output(array('msg' => 'Notifikasi berhasil dibaca'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function deleteNotification(Request $request){
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id_event' => 'required',
                'event' => 'required',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return $this->output(array('msg' => $validator->messages()->first()), "Fail", 422);
            }

            $idEvent = $request->id_event;
            $event = $request->event;

            $notifications = DatabaseNotification::where('data->id_event', encryptor($idEvent))
                ->where('data->event', $event)
                ->delete();

            DB::commit();
            info("Notifikasi berhasil dihapus, id_event: $idEvent, event: $event");
            return $this->output(array('msg' => 'Notifikasi berhasil dihapus'));

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
