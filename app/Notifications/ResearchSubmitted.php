<?php

namespace App\Notifications;

use App\Models\UserFile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the assigned adviser when a student submits (or resubmits)
 * research for review. Database-only — no mail, this just needs to show
 * up in the instructor's notification list/bell.
 */
class ResearchSubmitted extends Notification
{
    use Queueable;

    public function __construct(public UserFile $file) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'event' => 'research_submitted',
            'message' => "{$this->file->name} submitted \"{$this->file->title}\" for your review.",
            'stud_file_id' => $this->file->id,
            'url' => route('instructor', ['view' => 'dashboard']),
        ];
    }
}
