<?php

use App\Models\Message;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on message create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $message = Message::create([
        'name' => $creator->name,
        'email' => $creator->email,
        'subject' => 'Observer Test Message',
        'message' => 'Testing that the observer sets the creator and updater.',
    ]);

    expect($message->created_by_user_id)->toBe($creator->id)
        ->and($message->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $message->update(['subject' => 'Updated Subject']);

    expect($message->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($message->fresh()->updated_by_user_id)->toBe($updater->id);
});
