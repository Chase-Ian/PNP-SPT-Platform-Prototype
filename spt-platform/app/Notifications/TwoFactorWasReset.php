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
            ->subject('Two-factor authentication was reset on your SPT Platform account')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Two-factor authentication on your account was reset by {$this->by}.")
            ->line('Your old authenticator codes and recovery codes no longer work. You will be asked to set up two-factor authentication again at your next sign-in.')
            ->line('If you did not ask for this, contact your administrator immediately.')
            ->salutation('PNP SPT Platform');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'url' => route('profile.edit', absolute: false),
            'message' => "Your two-factor authentication was reset by {$this->by}.",
        ];
    }
}
