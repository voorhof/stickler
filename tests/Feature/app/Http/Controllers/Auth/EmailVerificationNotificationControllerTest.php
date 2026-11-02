<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('verification email can be resent', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertSessionHas('status', 'verification-link-sent');
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('already verified users are redirected', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertRedirect(route('account.dashboard'));
    Notification::assertNotSentTo($user, VerifyEmail::class);
});
