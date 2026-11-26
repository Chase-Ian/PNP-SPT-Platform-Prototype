<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorRecoveryLink extends Notification
{
    public function __construct(private string $url, private int $minutes) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset multi-factor authentication (MFA) on your SPT Platform account')
            ->greeting("Hello {$notifiable->first_name},")
            ->line('Someone signed in with your password and asked to reset multi-factor authentication (MFA) because the authenticator device is unavailable.')
            ->action('Reset MFA', $this->url)
            ->line("This link expires in {$this->minutes} minutes and only works in the browser where the sign-in was started.")
            ->line('After the reset you will be asked to set up MFA again with your new device.')
            ->line('If this was not you, do not click the link and change your password right away.')
            ->salutation('PNP SPT Platform');
    }
}
