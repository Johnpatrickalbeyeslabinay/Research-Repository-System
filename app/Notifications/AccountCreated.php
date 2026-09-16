<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to a newly-created student/instructor account. Separate from the
 * welcome email AdminController::store() already sends (Mail::to(...)) —
 * this is the in-app equivalent, visible once they actually log in.
 */
class AccountCreated extends Notification
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'event' => 'account_created',
            'message' => 'Welcome! Your account has been created.',
            'url' => route($this->user->role, ['view' => 'dashboard']),
        ];
    }
}
