<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent right away, like the app's other emails. It used to be queued, so it
 * only went out while a queue worker ran: never with `php artisan serve`, and
 * with one database shared between copies of the site, any copy's worker
 * could send it with that copy's link.
 */
class CustomResetPassword extends Notification
{
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset your SPT Platform password')
            ->greeting("Hello {$notifiable->first_name},")
            ->line('We received a request to reset the password for your PNP SPT Platform account.')
            ->action('Reset Password', $url)
            ->line('This link expires in 60 minutes.')
            ->line('If you did not request a password reset, no action is needed. Your password will not change.')
            ->salutation('PNP SPT Platform');
    }
}