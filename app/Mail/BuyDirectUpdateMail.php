<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Generic Buy Direct order update (shipped, released, refunded, disputes,
 * admin alerts). $details: subject, heading, greeting?, lines[], cta_url?, cta_label?, tone?
 */
class BuyDirectUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->details['subject']);
    }

    public function content(): Content
    {
        return new Content(view: 'email.buyDirectUpdateMail');
    }
}
