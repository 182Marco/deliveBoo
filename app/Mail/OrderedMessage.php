<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

// order confirmation sent to the customer (subject: "Ordered Message", from the class name)
class OrderedMessage extends Mailable
{
    use Queueable, SerializesModels;

    // the order data posted by Payment.vue, available in the view as $contact
    public function __construct(public array $contact)
    {
    }

    public function content(): Content
    {
        return new Content(markdown: 'mails.orderedMessage');
    }
}
