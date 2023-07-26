<?php

namespace App\Listeners;

use App\Events\NotifikasiEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifikasiListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotifikasiEvent $event): void
    {
        broadcast(new NotifikasiEvent($event->data))->toOthers();
    }
}
