<?php

namespace App\Notifications;

use App\Models\EmailChangeRequest;
use Illuminate\Notifications\Notification;

/** Tells the user (in-app) whether their email change request was approved or rejected. */
class EmailChangeDecision extends Notification
{
    public function __construct(private EmailChangeRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $r = $this->request;

        return ['message' => $r->status === 'approved'
            ? "Your email change to {$r->new_email} was approved. Please verify the new address."
            : 'Your email change request was rejected.'.($r->admin_note ? " Note: {$r->admin_note}" : ''),
        ];
    }
}
