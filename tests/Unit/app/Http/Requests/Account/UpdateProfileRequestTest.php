<?php

use App\Models\User;

describe('UpdateProfileRequest validation', function () {
    test('it validates valid data without password', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('account.profile'));
        $user->refresh();
        expect($user->name)->toBe('New Name')
            ->and($user->email)->toBe('new@example.com');
    });

    test('it fails validation when required fields are missing', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'));

        $response->assertSessionHasErrors(['name', 'email']);
    });

    test('it validates name max length', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    test('it validates email format', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'Test',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('it validates email is unique', function () {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'Test',
            'email' => 'other@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('it allows own email', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('account.profile'));
    });

    test('it requires current password when password is provided', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    });

    test('it validates current password is correct', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'Test',
            'email' => 'test@example.com',
            'current_password' => 'wrongpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    });

    test('it validates password confirmation matches', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'Test',
            'email' => 'test@example.com',
            'current_password' => 'password',
            'password' => 'newpassword',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    test('it validates valid data with password change', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'current_password' => 'password',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertRedirect(route('account.profile'));
        $user->refresh();
        expect($user->name)->toBe('New Name')
            ->and($user->email)->toBe('new@example.com');
    });
});
