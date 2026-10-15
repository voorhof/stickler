<?php

/** @noinspection LaravelUnknownRouteNameInspection */

use App\Mail\ContactMessageReply;
use App\Models\Message;

beforeEach(function () {
    config(['logging.channels.single.path' => storage_path('logs/testing.log')]);
});

test('mailable content', function () {
    $message = Message::factory()->create([
        'subject' => 'test subject',
        'message' => 'test message',
        'reply' => 'test reply',
    ]);

    $mailable = new ContactMessageReply($message);

    $mailable
        ->assertFrom(config('mail.from.address'))
        ->assertTo($message->email)
        ->assertHasSubject(__('mail/contact-message-reply.subject'))

        ->assertSeeInHtml(__('Hello!'))
        ->assertSeeInHtml(__('mail/contact-message-reply.intro'))
        ->assertSeeInHtml($message->reply)
        ->assertSeeInHtml(__('Subject'))
        ->assertSeeInHtml($message->subject)
        ->assertSeeInHtml(__('Your original message'))
        ->assertSeeInHtml($message->message)
        ->assertSeeInHtml(__('mail/contact-message-reply.button'))
        ->assertSeeInHtml(route('welcome.index'))

        ->assertSeeInText(__('Hello!'))
        ->assertSeeInText(__('mail/contact-message-reply.intro'))
        ->assertSeeInText($message->reply)
        ->assertSeeInText(__('Subject'))
        ->assertSeeInText($message->subject)
        ->assertSeeInText(__('Your original message'))
        ->assertSeeInText($message->message)
        ->assertSeeInText(__('mail/contact-message-reply.button'))
        ->assertSeeInText(route('welcome.index'));
});
