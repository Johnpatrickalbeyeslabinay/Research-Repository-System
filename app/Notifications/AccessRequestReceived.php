<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestReceived extends Notification
{
    use Queueable;

    public function __construct(
        public AccessRequest $accessRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $file = $this->accessRequest->file;

        return (new MailMessage)
            ->subject('New Research Access Request')
            ->view('email.access_request', [
                'name' => $notifiable->name,
                'research_title' => $file->title,
                'requester_email' => $this->accessRequest->email,
                'reason_access' => $this->accessRequest->reason_access,
                'access_request_id' => $this->accessRequest->id,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $file = $this->accessRequest->file;

        return [
            'type' => 'access_request',
            'access_request_id' => $this->accessRequest->id,
            'stud_file_id' => $file->id,
            'research_title' => $file->title,
            'requester_email' => $this->accessRequest->email,
            'reason_access' => $this->accessRequest->reason_access,
            'message' => 'Someone requested access to your research.',
        ];
    }
}
