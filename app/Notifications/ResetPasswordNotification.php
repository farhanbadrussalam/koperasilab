<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotificationBase;

class ResetPasswordNotification extends ResetPasswordNotificationBase
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi - ' . config('app.name'))
            ->view('emails.reset_password', [
                'url' => $url,
                'name' => $notifiable->name ?? 'User',
            ]);
    }
}
