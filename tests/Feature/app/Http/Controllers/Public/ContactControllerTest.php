<?php

use App\Mail\ContactMessageReceived;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Spatie\Honeypot\Events\SpamDetectedEvent;

uses(RefreshDatabase::class);

test('it can view the contact page', function () {
    $response = $this->get(route('contact.index'));

    $response->assertSuccessful()
        ->assertViewIs('public.contact');
});

test('it can store a contact message successfully', function () {
    Mail::fake();

    config()->set('honeypot.enabled', false);

    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '123456789',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ];

    $response = $this->followingRedirects()->post(route('contact.store'), $data);

    $response->assertSuccessful()
        ->assertSee(__('Thank you for your message! We’ll get back to you as soon as possible.'));

    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '123456789',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ]);

    Mail::assertQueued(ContactMessageReceived::class);
});

test('it can store a contact message with empty phone field successfully', function () {
    Mail::fake();

    config()->set('honeypot.enabled', false);

    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ];

    $response = $this->followingRedirects()->post(route('contact.store'), $data);

    $response->assertSuccessful()
        ->assertSee(__('Thank you for your message! We’ll get back to you as soon as possible.'));

    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => null,
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ]);

    Mail::assertQueued(ContactMessageReceived::class);
});

test('it fails validation when storing invalid contact message', function () {
    Mail::fake();

    config()->set('honeypot.enabled', false);

    $response = $this->post(route('contact.store'));

    $response->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'message']);
    expect(Message::count())->toBe(0);

    Mail::assertNothingQueued();
});

test('rejects request with honeypot', function () {
    Event::fake();
    Mail::fake();

    config()->set('honeypot.enabled', true);

    $data = [
        'first_name' => 'Jane', // This is the honeypot field and triggers spam detection when filled
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ];

    $response = $this->post(route('contact.store'), $data);

    expect($response->content())->toBeEmpty();

    Event::assertDispatched(SpamDetectedEvent::class);

    $this->assertDatabaseMissing('messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ]);

    Mail::assertNothingQueued();
});

test('it limits too many requests', function () {
    Mail::fake();

    config()->set('honeypot.enabled', true);

    // First 3 requests will succeed
    $response = $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Jane Doe1',
        'email' => 'jane1@example.com',
        'phone' => '',
        'subject' => 'Inquiry1',
        'message' => 'Hello, I have a question1.',
    ]);
    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe1',
        'email' => 'jane1@example.com',
        'subject' => 'Inquiry1',
        'message' => 'Hello, I have a question1.',
    ]);

    $response = $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Jane Doe2',
        'email' => 'jane2@example.com',
        'phone' => '',
        'subject' => 'Inquiry2',
        'message' => 'Hello, I have a question2.',
    ]);
    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe2',
        'email' => 'jane2@example.com',
        'subject' => 'Inquiry2',
        'message' => 'Hello, I have a question2.',
    ]);

    $response = $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Jane Doe3',
        'email' => 'jane3@example.com',
        'phone' => '',
        'subject' => 'Inquiry3',
        'message' => 'Hello, I have a question3.',
    ]);
    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe3',
        'email' => 'jane3@example.com',
        'subject' => 'Inquiry3',
        'message' => 'Hello, I have a question3.',
    ]);

    // 4th request will fail
    $response = $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Jane Doe4',
        'email' => 'jane4@example.com',
        'phone' => '',
        'subject' => 'Inquiry4',
        'message' => 'Hello, I have a question4.',
    ]);
    $response->assertTooManyRequests();
    $this->assertDatabaseMissing('messages', [
        'name' => 'Jane Doe4',
        'email' => 'jan43@example.com',
        'subject' => 'Inquiry4',
        'message' => 'Hello, I have a question4.',
    ]);

    Mail::assertQueued(ContactMessageReceived::class, 3);
});
