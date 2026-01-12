<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use App\Models\Log_activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogout
{
    protected $request;
    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event)
    {
        $user = $event->user;

        Log_activity::create([
            'log_name'  => 'AUTH',
            'log_type'  => 'signout',
            'description' => 'User berhasil logout',
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'causer_type' => get_class($user),
            'causer_id' => $user->id,
            'ip_address' => $this->request->getClientIp(),
            'user_agent' => $this->request->header('User-Agent'),
            'properties'    => [
                'email' => $user->email,
                'time'  => now()->toDateTimeString()
            ]
        ]);
    }
}
