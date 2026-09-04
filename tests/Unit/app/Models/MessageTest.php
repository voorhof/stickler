<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('a message can be created', function () {
    $user = User::factory()->create();

    $message = Message::forceCreate([
        'name' => $user->name,
        'email' => $user->email,
        'subject' => 'Test Message',
        'message' => 'This is my first message.',
    ]);

    expect($message->subject)->toBe('Test Message')
        ->and($message->message)->toBe('This is my first message.')
        ->and($message->slug)->toBe('test-message');
});

test('a message can be mass assigned fillable values', function () {
    $message = new Message([
        'slug' => 'mass-assigned-slug',
        'name' => 'Willy Wortel',
        'email' => 'willy@wortel.com',
        'phone' => '1234567890',
        'subject' => 'Test Message',
        'message' => 'This is my first message.',
        'reply' => 'This is a reply.',
        'order_column' => 5,
    ]);

    expect($message->name)->toBe('Willy Wortel')
        ->and($message->slug)->toBe('mass-assigned-slug')
        ->and($message->email)->toBe('willy@wortel.com')
        ->and($message->phone)->toBe('1234567890')
        ->and($message->subject)->toBe('Test Message')
        ->and($message->message)->toBe('This is my first message.')
        ->and($message->reply)->toBe('This is a reply.')
        ->and($message->order_column)->toBe(5);
});

test('a phone number is nullable', function () {
    $message = Message::create([
        'name' => 'Willy Wortel',
        'email' => 'willy@wortel.com',
        'subject' => 'Test Message',
        'message' => 'This is my first message.',
    ]);

    expect($message->name)->toBe('Willy Wortel')
        ->and($message->email)->toBe('willy@wortel.com')
        ->and($message->subject)->toBe('Test Message')
        ->and($message->slug)->toBe('test-message')
        ->and($message->message)->toBe('This is my first message.')
        ->and($message->phone)->toBeNull();
});

test('it generates a slug from subject', function () {
    $message = Message::forceCreate([
        'subject' => 'My Message Subject',
        'name' => 'Willy Wortel',
        'email' => 'willy@wortel.com',
        'message' => 'This is my first message.',
    ]);

    expect($message->slug)->toBe('my-message-subject');
});

test('it does not regenerate a slug when updating the subject', function () {
    $message = Message::factory()->create(['subject' => 'My Message Title', 'slug' => 'my-message-subject']);
    $message->update(['subject' => 'My Updated Message Title']);

    expect($message->slug)->toBe('my-message-subject');
});

test('it generates unique slugs for messages with the same subject', function () {
    $firstMessage = Message::factory()->create(['subject' => 'Duplicate Title', 'slug' => 'duplicate-subject']);
    $secondMessage = Message::factory()->create(['subject' => 'Duplicate Title', 'slug' => 'duplicate-subject-1']);

    expect($secondMessage->slug)
        ->not->toBe($firstMessage->slug)
        ->and(Str::startsWith($secondMessage->slug, 'duplicate-subject'))->toBeTrue();
});

test('it uses slug as route key name', function () {
    expect((new Message)->getRouteKeyName())->toBe('slug');
});

test('it has a default order_column of one', function () {
    $user = User::factory()->create();

    $message = Message::forceCreate([
        'name' => $user->name,
        'email' => $user->email,
        'subject' => 'Test Message',
        'message' => 'This is my first message.',
    ]);

    expect($message->order_column)->toBe(1);
});

test('it can be reordered', function () {
    $message1 = Message::factory()->create(['order_column' => 1]);
    $message2 = Message::factory()->create(['order_column' => 2]);

    Message::swapOrder($message1, $message2);

    expect($message1->fresh()->order_column)->toBe(2)
        ->and($message2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    $message1 = Message::factory()->create(['order_column' => 1]);
    $message2 = Message::factory()->create(['order_column' => 2]);
    $message3 = Message::factory()->create(['order_column' => 3]);

    $message1->update(['order_column' => 3]);

    expect($message1->fresh()->order_column)->toBe(3)
        ->and($message2->fresh()->order_column)->toBe(1)
        ->and($message3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $message1 = Message::factory()->create(['order_column' => 1]);
    $message2 = Message::factory()->create(['order_column' => 2]);

    $newMessage = Message::forceCreate([
        'subject' => 'My Message Subject',
        'name' => 'Willy Wortel',
        'email' => 'willy@wortel.com',
        'message' => 'This is my third message.',
        'order_column' => 1,
    ]);

    expect($newMessage->fresh()->order_column)->toBe(1)
        ->and($message1->fresh()->order_column)->toBe(2)
        ->and($message2->fresh()->order_column)->toBe(3);
});

test('it does reorder when creating a new model without specific order_column', function () {
    $message1 = Message::factory()->create(['order_column' => 1]);
    $message2 = Message::factory()->create(['order_column' => 2]);

    $newMessage = Message::forceCreate([
        'subject' => 'My Message Subject',
        'name' => 'Willy Wortel',
        'email' => 'willy@wortel.com',
        'message' => 'This is my third message.',
        // order_column will be 1 (because of SortableOnUpdate)
    ]);

    expect($newMessage->fresh()->order_column)->toBe(1)
        ->and($message1->fresh()->order_column)->toBe(2)
        ->and($message2->fresh()->order_column)->toBe(3);
});

test('it is not archived when archived_at is null', function () {
    $message = Message::factory()->create(['archived_at' => null]);

    expect($message->archived)->toBeFalse();
});

test('it is not archived when archived_at is in the future', function () {
    $message = Message::factory()->create(['archived_at' => now()->addDays(7)]);

    expect($message->archived)->toBeFalse();
});

test('it is archived when archived_at is in the past', function () {
    $message = Message::factory()->create(['archived_at' => now()->subDay()]);

    expect($message->archived)->toBeTrue();
});

test('it can be soft deleted', function () {
    $message = Message::factory()->create();

    $message->delete();

    $this->assertSoftDeleted(Message::class, ['id' => $message->id]);
});

test('it can be restored after soft delete', function () {
    $message = Message::factory()->softDeleted()->create();

    $message->restore();

    $this->assertNotSoftDeleted(Message::class, ['id' => $message->id]);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $message = Message::factory()->create(['created_by_user_id' => $user->id]);

    expect($message->creator)->toBeInstanceOf(User::class)
        ->and($message->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $message = Message::factory()->create(['updated_by_user_id' => $user->id]);

    expect($message->updater)->toBeInstanceOf(User::class)
        ->and($message->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $message = Message::factory()->create();

    // withDefault() ensures the relationship always returns a User instance
    expect($message->creator)->toBeInstanceOf(User::class)
        ->and($message->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $message = Message::factory()->create();

    // withDefault() ensures the relationship always returns a User instance
    expect($message->updater)->toBeInstanceOf(User::class)
        ->and($message->updater->name)->toBe('Guest User');
});

test('replied_at is cast to a datetime instance', function () {
    $message = Message::factory()->create(['replied_at' => '2024-01-15 12:00:00']);

    expect($message->replied_at)->toBeInstanceOf(Illuminate\Support\Carbon::class);
});

test('archived_at is cast to a datetime instance', function () {
    $message = Message::factory()->create(['archived_at' => '2024-01-15 12:00:00']);

    expect($message->archived_at)->toBeInstanceOf(Illuminate\Support\Carbon::class);
});

test('read is cast to a boolean', function () {
    $messageTrue = Message::factory()->create(['read' => true]);
    $messageFalse = Message::factory()->create(['read' => false]);

    expect($messageTrue->read)->toBeTrue()
        ->and($messageFalse->read)->toBeFalse();
});

test('replied is cast to a boolean', function () {
    $messageTrue = Message::factory()->create(['replied' => true]);
    $messageFalse = Message::factory()->create(['replied' => false]);

    expect($messageTrue->replied)->toBeTrue()
        ->and($messageFalse->replied)->toBeFalse();
});

test('it determines unread status from read attribute', function () {
    $unreadMessage = Message::factory()->create(['read' => false]);
    $readMessage = Message::factory()->create(['read' => true]);

    expect($unreadMessage->unread)->toBeTrue()
        ->and($readMessage->unread)->toBeFalse();
});

test('it returns subject as title attribute', function () {
    $message = Message::factory()->create(['subject' => 'Contact Inquiry']);

    expect($message->title)->toBe('Contact Inquiry');
});

test('it configures activity log options correctly', function () {
    $message = new Message;
    $options = $message->getActivitylogOptions();

    expect($options)->toBeInstanceOf(Spatie\Activitylog\Support\LogOptions::class);
});
