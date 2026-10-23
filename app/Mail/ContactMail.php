<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $details;

    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contact Form: ' . ($this->details['subject'] ?? 'New Message'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.contactMail',
            with: ['details' => $this->details],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
