<?php

use App\Actions\Contact\StoreContactMessage;
use App\Mail\ContactMessageReceived;
use App\Models\Message;
use Illuminate\Support\Facades\Mail;

test('it stores a new contact message and queues the admin notification', function () {
    Mail::fake();

    $data = [
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'subject' => fake()->sentence(),
        'message' => fake()->paragraph(),
    ];

    $message = app(StoreContactMessage::class)->handle($data);

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->email)->toBe($data['email'])
        ->and(Message::count())->toBe(1);

    Mail::assertQueued(ContactMessageReceived::class);
});

test('it skips storing an identical message received within the duplicate window', function () {
    Mail::fake();

    $data = [
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'subject' => fake()->sentence(),
        'message' => fake()->paragraph(),
    ];

    Message::factory()->create([...$data, 'created_at' => now()->subMinutes(5)]);

    $result = app(StoreContactMessage::class)->handle($data);

    expect($result)->toBeNull()
        ->and(Message::count())->toBe(1);

    Mail::assertNothingQueued();
});

test('it stores an identical message once the duplicate window has passed', function () {
    Mail::fake();

    $data = [
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'subject' => fake()->sentence(),
        'message' => fake()->paragraph(),
    ];

    Message::factory()->create([...$data, 'created_at' => now()->subMinutes(30)]);

    $result = app(StoreContactMessage::class)->handle($data);

    expect($result)->toBeInstanceOf(Message::class)
        ->and(Message::count())->toBe(2);
});
