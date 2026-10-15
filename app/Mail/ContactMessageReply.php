<?php

/** @noinspection LaravelUnknownRouteNameInspection */

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReply extends Mailable
{
    use Queueable, SerializesModels;

    // This mail is used to send the user the contact message reply

    /**
     * Create a new mail instance.
     */
    public function __construct(
        protected Message $message,
    ) {}

    /**
     * Get the mail envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            to: $this->message->email,
            subject: __('mail/contact-message-reply.subject'),
        );
    }

    /**
     * Get the mail content definition.
     *
     * text: contains the plain text version blade file
     * markdown: contains the markdown HTML version blade file
     * with: data to be passed to the blade files
     */
    public function content(): Content
    {
        $url = route('welcome.index');

        return new Content(
            text: 'mail.contact-message-reply-text',
            markdown: 'mail.contact-message-reply',
            with: [
                'url' => $url,
                'subject' => $this->message->title,
                'messageText' => $this->message->message,
                'replyText' => $this->message->reply,
            ],
        );
    }

    /**
     * Get the attachments for the mail.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
