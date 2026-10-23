<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdvertBannedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * @param array{name:string,ad_title:string,reason_category:string,reason_note:?string,is_resubmission_reject:bool} $details
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->details['is_resubmission_reject']
                ? 'Your resubmitted advert was not approved'
                : 'Your advert has been disabled',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.advertBannedMail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
