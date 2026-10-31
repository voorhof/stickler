<?php

/** @noinspection PhpUnusedLocalVariableInspection */

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    Mail::fake();

    $response = $this->post(route('register'), [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('account.dashboard'));
});

test('registration requires valid data', function () {
    $response = $this->post(route('register'), [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
        'terms' => false,
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'terms']);
});

test('registration rejects duplicate email', function () {
    $existingUser = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post(route('register'), [
        'name' => 'Another User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ]);

    $response->assertSessionHasErrors(['email']);
    expect(User::count())->toBe(1);
});

test('registration logs activity', function () {
    Mail::fake();

    $response = $this->post(route('register'), [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ]);

    $response->assertRedirect(route('account.dashboard'));
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => User::class,
        'event' => 'register',
    ]);
});
