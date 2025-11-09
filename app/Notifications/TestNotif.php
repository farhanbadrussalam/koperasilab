<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TestNotif extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable) {
        return ['broadcast'];
    }

    public function toBroadcast($notifiable) {
        return new BroadcastMessage([
            'pesan' => 'Halo from test',
            'event_id' => 'test',
            'event' => 'test',
            'url' => 'https://google.com',
            'user_id' => 'test',
            'perusahaan_id' => 'test'
        ]);
    }
}
