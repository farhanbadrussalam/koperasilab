<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use App\Models\Log_activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
{
    // Kita butuh Request untuk ambil IP dan Browser user
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
    public function handle(Login $event)
    {
        $user = $event->user;

        session(['app_settings' => \App\Models\AppSettings::pluck('value', 'key')->toArray()]);

        Log_activity::create([
            'log_name'  => 'AUTH',
            'log_type'  => 'signin',
            'description' => 'User berhasil login',
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
