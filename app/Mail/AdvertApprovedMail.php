<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdvertApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * @param array{name:string,ad_title:string,ad_link:string} $details
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your advert is live again',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.advertApprovedMail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
