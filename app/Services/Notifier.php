<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NotifikasiBaru;
use App\Models\User;
use Auth;

class Notifier
{
    public static function send(mixed$targets, array $data =[])
    {
        $pesan = $data['pesan'] ?? null;
        $url = $data['url'] ?? null;
        $event = $data['event'] ?? null;
        $user_id = $data['user_id'] ?? null;
        $perusahaan_id = $data['perusahaan_id'] ?? null;
        $event_id = $data['event_id'] ?? null;
        if (is_string($targets) || is_numeric($targets)) {
            $targets = [$targets];
        }

        // Normalisasi: dari array ID → Builder
        if (is_array($targets) && isset($targets[0]) && !is_object($targets[0])) {
            $query = User::whereIn('id', $targets);
            return self::sendQuery($query, $pesan, $url, $event_id, $event, $user_id, $perusahaan_id);
        }

        if ($targets instanceof Builder) {
            return self::sendQuery($targets, $pesan, $url, $event_id, $event , $user_id, $perusahaan_id);
        }

        // Collection<User> atau array<User>
        if ($targets instanceof Collection) {
            $targets = $targets->unique('id');
        } else {
            $targets = collect($targets)->unique('id');
        }

        Notification::send($targets, new NotifikasiBaru($pesan, $url, $event_id, $event));
    }

    public static function read($event = null, $id = null){
        $listUser = Auth::user()->unreadNotifications()->when($event, function ($query, $event) {
            if(is_array($event)){
                return $query->whereIn('data->event', $event);
            }

            return $query->where('data->event', $event);
        })->when($id, function ($query, $id) {
            return $query->where('data->id_event', $id);
        })->get();

        if (empty($listUser)) {
            return;
        }

        foreach ($listUser as $key => $value) {
            $value->markAsRead();
        }
    }

    protected static function sendQuery(Builder $query, string $pesan, ?string $url,?string $event_id, ?string $event, ?string $user_id, ?string $perusahaan_id)
    {
        $query->select('id') // minimal kolom
              ->chunkById(200, function($chunk) use ($pesan, $url, $event_id, $event, $user_id, $perusahaan_id) {
                  Notification::send($chunk, new NotifikasiBaru($pesan, $url, $event_id, $event, $user_id, $perusahaan_id));
              });
    }
}
