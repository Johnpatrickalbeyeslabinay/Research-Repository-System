<?php

namespace App\Notifications;

use App\Models\UserFile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the owning student when their adviser approves (forwards to
 * admin) or rejects their submission.
 */
class ResearchReviewedByInstructor extends Notification
{
    use Queueable;

    public function __construct(public UserFile $file, public string $status) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        // Previously the rejection message never included why — the
        // reason was already sitting in instructor_feedback (posted via
        // the same review action) but nothing in this notification read
        // it, so a rejected student got "Your adviser rejected X" and
        // had to go dig through the dashboard to find out why.
        $message = $this->status === 'approved'
            ? "Your adviser approved \"{$this->file->title}\" — it's now with the admin for final review."
            : "Your adviser rejected \"{$this->file->title}\"."
                .($this->file->instructor_feedback ? " Reason: {$this->file->instructor_feedback}" : '');

        return [
            'event' => 'research_reviewed_by_instructor',
            'status' => $this->status,
            'message' => $message,
            'stud_file_id' => $this->file->id,
            'url' => route('student', ['view' => 'dashboard']),
        ];
    }
}
