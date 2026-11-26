<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountReinstated extends Notification
{
    public function __construct(private string $by) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your SPT Platform account was reinstated')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your account was reinstated by {$this->by}. Your training records and certificates are back.")
            ->line('You can sign in again with your existing password.')
            ->action('Sign in', route('login'))
            ->line('If you did not expect this, contact your administrator.')
            ->salutation('PNP SPT Platform');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'url' => route('profile.edit', absolute: false),
            'message' => "Your account was reinstated by {$this->by}.",
        ];
    }
}
