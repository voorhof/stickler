<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

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
            to: config('stickler.contact_details.email'),
            subject: __('mail/contact-message-received.subject'),
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
        $url = route('filament.admin.resources.messages.edit', $this->message);

        return new Content(
            text: 'mail.contact-message-received-text',
            markdown: 'mail.contact-message-received',
            with: [
                'url' => $url,
                'sender' => $this->message->name,
                'subject' => $this->message->subject,
                'messageText' => $this->message->message,
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
