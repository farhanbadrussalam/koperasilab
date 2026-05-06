<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

use App\Models\User;

class NotifikasiBaru extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $pesan,
        public ?string $url = null,
        public ?string $id_event = null,
        public ?string $event = null,
        public ?string $user_id = null,
        public ?string $perusahaan_id = null
    ){

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable)
    {
        $channels = ['database'];

        $user = User::select('realtime_notifications', 'id', 'name')->where('id',$notifiable->id)->first();
        if(isset($user->realtime_notifications) && $user->realtime_notifications){
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    public function toDatabase(mixed $notifiable)
    {
        return [
            'pesan' => $this->pesan,
            'url' => $this->url,
            'id_event' => $this->id_event,
            'event' => $this->event,
            'user_id' => $this->user_id,
            'perusahaan_id' => $this->perusahaan_id,
        ];
    }

    public function toBroadcast(mixed $notifiable)
    {
        $user = User::select('realtime_notifications', 'id', 'name')->where('id',$notifiable->id)->first();
        info("send broadcast to : {$user->name}");
        return new BroadcastMessage([
            'pesan' => $this->pesan,
            'url' => $this->url,
            'event' => $this->event,
            'id_event' => $this->id_event,
            'user_id' => $this->user_id,
            'perusahaan_id' => $this->perusahaan_id,
        ]);
    }
}
