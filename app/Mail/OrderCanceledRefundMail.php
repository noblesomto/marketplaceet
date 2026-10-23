<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCanceledRefundMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        $canceledBy = $this->details['canceled_by'] ?? 'Seller';

        return new Envelope(
            subject: "Order Canceled by {$canceledBy} – Refund Required",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.orderCanceledRefundMail',
        );
    }
}
