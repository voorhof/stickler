<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new mail instance.
     */
    public function __construct(
        public Order $order,
    ) {}

    /**
     * Get the mail envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: __('Payment confirmation'),
        );
    }

    /**
     * Get the mail content definition.
     */
    public function content(): Content
    {
        return new Content(
            text: 'mail.payment-confirmation-text',
            markdown: 'mail.payment-confirmation',
            with: [
                'order' => $this->order,
            ],
        );
    }
}
