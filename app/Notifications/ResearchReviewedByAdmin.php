<?php

namespace App\Notifications;

use App\Models\UserFile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the owning student when admin makes the final approve/reject
 * decision on their submission.
 */
class ResearchReviewedByAdmin extends Notification
{
    use Queueable;

    public function __construct(public UserFile $file, public string $status) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $message = $this->status === 'approved'
            ? "Your research \"{$this->file->title}\" has been approved."
            : "Your research \"{$this->file->title}\" has been rejected.";

        return [
            'event' => 'research_reviewed_by_admin',
            'status' => $this->status,
            'message' => $message,
            'stud_file_id' => $this->file->id,
            'url' => route('student', ['view' => 'dashboard']),
        ];
    }
}
