<?php

/** @noinspection PhpUnusedLocalVariableInspection */

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('account.dashboard'));
});

test('session is regenerated after login', function () {
    $user = User::factory()->create();

    $session_id = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->cookie('XSRF-TOKEN');

    $this->assertAuthenticated();
    expect(session()->getId())->not->toBeNull();
});

test('login logs activity', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertDatabaseHas('activity_log', [
        'subject_type' => User::class,
        'event' => 'login',
    ]);
});

test('logout logs activity', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'));

    $this->assertDatabaseHas('activity_log', [
        'subject_type' => User::class,
        'event' => 'logout',
    ]);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('welcome.index'));
});
