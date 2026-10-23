<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnmatchedPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALERT: Paid Flutterwave transaction with no matching record — ' . ($this->details['reference'] ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.unmatchedPaymentMail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
