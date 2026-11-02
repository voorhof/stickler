<?php

use App\Models\User;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertStatus(200);
});

test('verified users are redirected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertRedirect(route('account.dashboard'));
});
