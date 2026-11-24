<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the OLD address so an unwanted change doesn't go unnoticed. */
class EmailAddressChanged extends Notification
{
    public function __construct(private string $name, private string $newEmail) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('The email address on your SPT Platform account was changed')
            ->greeting("Hello {$this->name},")
            ->line("An administrator approved a request to change your account email to {$this->newEmail}.")
            ->line('This address will no longer receive account emails.')
            ->line('If you did not request this change, contact your administrator immediately.')
            ->salutation('PNP SPT Platform');
    }
}
