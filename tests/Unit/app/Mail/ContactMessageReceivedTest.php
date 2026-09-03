<?php

use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['logging.channels.single.path' => storage_path('logs/testing.log')]);
});

test('mailable content', function () {
    $message = Message::factory()->create();

    $mailable = new App\Mail\ContactMessageReceived($message);

    $mailable
        ->assertFrom(config('mail.from.address'))
        ->assertTo(config('pietjeprecies.contact_details.email'))
        ->assertHasSubject(__('mail/contact-message-received.subject'))

        ->assertSeeInHtml(__('Hello!'))
        ->assertSeeInHtml(__('mail/contact-message-received.intro'))
        ->assertSeeInHtml(__('Sender'))
        ->assertSeeInHtml($message->name)
        ->assertSeeInHtml(__('Subject'))
        ->assertSeeInHtml($message->subject)
        ->assertSeeInHtml($message->message)
        ->assertSeeInHtml(__('mail/contact-message-received.button'))
        ->assertSeeInHtml(route('filament.admin.resources.messages.edit', $message))

        ->assertSeeInText(__('Hello!'))
        ->assertSeeInText(__('mail/contact-message-received.intro'))
        ->assertSeeInText(__('Sender'))
        ->assertSeeInText($message->name)
        ->assertSeeInText(__('Subject'))
        ->assertSeeInText($message->subject)
        ->assertSeeInText($message->message)
        ->assertSeeInText(__('mail/contact-message-received.button'))
        ->assertSeeInText(route('filament.admin.resources.messages.edit', $message));
});
