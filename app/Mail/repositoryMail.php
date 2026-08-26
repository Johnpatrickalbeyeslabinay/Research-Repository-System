<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class repositoryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;

    public $user_id;

    public $email;

    public $password;

    public $role;

    public function __construct(
        $name,
        $user_id,
        $email,
        $password,
        $role
    ) {
        $this->name = $name;
        $this->user_id = $user_id;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Research Repository Account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.repositoryMail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
