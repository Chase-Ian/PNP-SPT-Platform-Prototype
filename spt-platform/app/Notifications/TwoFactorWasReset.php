<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorWasReset extends Notification
{
    public function __construct(private string $by) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Multi-factor authentication (MFA) was reset on your SPT Platform account')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Multi-factor authentication (MFA) on your account was reset by {$this->by}.")
            ->line('Codes from your old authenticator app no longer work. You will be asked to set up MFA again at your next sign-in.')
            ->line('If you did not ask for this, contact your administrator immediately.')
            ->salutation('PNP SPT Platform');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'url' => route('profile.edit', absolute: false),
            'message' => "Your MFA was reset by {$this->by}.",
        ];
    }
}
