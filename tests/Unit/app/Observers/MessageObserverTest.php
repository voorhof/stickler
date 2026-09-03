<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('sets created_by_user_id and updated_by_user_id when a message is created', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $message = Message::create([
        'name' => $user->name,
        'email' => $user->email,
        'subject' => 'Observer Test Message',
        'message' => 'Testing that the observer sets the creator and updater on create.',
    ]);

    expect($message->created_by_user_id)->toBe($user->id)
        ->and($message->updated_by_user_id)->toBe($user->id);
});

it('updates updated_by_user_id but not created_by_user_id when a message is updated', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $message = Message::create([
        'name' => $creator->name,
        'email' => $creator->email,
        'subject' => 'Observer Test Message',
        'message' => 'Testing that the observer sets the creator and updater on create.',
    ]);

    expect($message->created_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $message->update(['subject' => 'Updated Title']);

    expect($message->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($message->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated during update', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $message = Message::create([
        'name' => $creator->name,
        'email' => $creator->email,
        'subject' => 'Observer Test Message',
        'message' => 'Testing that the observer sets the creator and updater on create.',
    ]);

    $originalCreatorId = $message->created_by_user_id;
    $originalUpdaterId = $message->updated_by_user_id;

    auth()->logout();

    $message->update(['subject' => 'Updated Without Auth']);

    expect($message->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($message->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});
