<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email', ['email' => $user->email]));

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password link requires email', function () {
    $response = $this->post(route('password.email'), [
        'email' => '',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('reset password link handles invalid email format', function () {
    $response = $this->post(route('password.email'), [
        'email' => 'invalid-email',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('reset password link handles non-existent email', function () {
    Notification::fake();

    $response = $this->post(route('password.email'), [
        'email' => 'nonexistent@example.com',
    ]);

    Notification::assertNothingSent();
    $response->assertSessionHasErrors(['email']);
});

test('reset password link does not crash when email does not exist', function () {
    Notification::fake();

    $response = $this->post(route('password.email'), [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertSessionHasErrors(['email']);

    expect(Activity::where('log_name', 'authentication')->count())->toBe(0);
});

test('reset password link logs activity when email exists', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post(route('password.email'), [
        'email' => $user->email,
    ]);

    $response->assertSessionHasNoErrors();

    $activity = Activity::where('log_name', 'authentication')
        ->where('causer_id', $user->id)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('password reset link');
});
