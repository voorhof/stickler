<?php

namespace App\Actions\Contact;

use App\Mail\ContactMessageReceived;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class StoreContactMessage
{
    private const int DUPLICATE_WINDOW_MINUTES = 10;

    /**
     * Store a contact message and queue the admin notification.
     *
     * Returns null when an identical message was already received within the duplicate window.
     *
     * @param  array{email: string, subject: string, message: string}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): ?Message
    {
        $isDuplicate = Message::query()
            ->where('email', $data['email'])
            ->where('subject', $data['subject'])
            ->where('message', $data['message'])
            ->where('created_at', '>=', now()->subMinutes(self::DUPLICATE_WINDOW_MINUTES))
            ->exists();

        if ($isDuplicate) {
            return null;
        }

        return DB::transaction(function () use ($data): Message {
            $message = Message::create($data);

            // Send mail to the admin
            Mail::queue(new ContactMessageReceived($message));

            return $message;
        });
    }
}
