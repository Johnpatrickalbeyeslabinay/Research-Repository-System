<?php

namespace App\Notifications;

use App\Models\UserFile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the owning student when their adviser posts feedback
 * (InstructorController::feedback) — previously silent: the feedback was
 * saved to instructor_feedback and the student had no way to know it
 * existed unless they happened to reopen the submission card.
 */
class ResearchFeedbackPosted extends Notification
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
            'event' => 'research_feedback_posted',
            'message' => "Your adviser left feedback on \"{$this->file->title}\": {$this->file->instructor_feedback}",
            'stud_file_id' => $this->file->id,
            'url' => route('student', ['view' => 'dashboard']),
        ];
    }
}
