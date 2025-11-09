<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NotifikasiBaru extends Notification // implements ShouldQueue
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
    public function via(object $notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable)
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

    public function toBroadcast(object $notifiable)
    {
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
