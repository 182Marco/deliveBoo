<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// order confirmation sent to the customer: a plain HTML view with inline styles (mail clients ignore stylesheets),
// in the language of the order request (app/Http/Middleware/SetLocale.php)
class OrderedMessage extends Mailable
{
    use Queueable, SerializesModels;

    // the order data posted by Payment.vue, available in the view as $contact
    public function __construct(public array $contact)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('mail.order.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'mails.orderedMessage');
    }
}
