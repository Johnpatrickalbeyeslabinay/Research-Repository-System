<?php

namespace App\Mail;

use App\Models\AccessRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessRequestStatusMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public AccessRequest $accessRequest,
        public string $ownerName
    ) {}

    public function envelope(): Envelope
    {
        $status = ucfirst($this->accessRequest->status);

        return new Envelope(
            subject: "Your access request was {$this->accessRequest->status}",
        );
    }

    public function content(): Content
    {
        $approvedUrl = null;

        if ($this->accessRequest->status === 'approved') {
            $approvedUrl = \Illuminate\Support\Facades\URL::signedRoute(
                'access-requests.approved',
                [
                    'accessRequest' => $this->accessRequest->id,
                ]
            );
        }

        return new Content(
            view: 'email.receivedrequest',
            with: [
                'approvedUrl' => $approvedUrl,
            ],
        );
    }
}
