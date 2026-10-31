<?php

use App\Models\User;

describe('account dashboard', function () {
    test('unauthenticated users are redirected to login', function () {
        $response = $this->get(route('account.dashboard'));

        $response->assertRedirect(route('login'));
    });

    test('unverified users are redirected to email verification', function () {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertRedirect(route('verification.notice'));
    });

    test('verified users can view the dashboard', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSuccessful()
            ->assertViewIs('account.dashboard');
    });

    test('dashboard sets the page title', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSee('Dashboard');
    });
});
