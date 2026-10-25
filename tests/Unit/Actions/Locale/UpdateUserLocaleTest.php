<?php

use App\Actions\Locale\UpdateUserLocale;
use App\Models\User;

test('it stores the locale on the user and applies the app locale', function () {
    $user = User::factory()->create(['locale' => 'en_US']);

    app(UpdateUserLocale::class)->handle($user, 'nl_BE');

    expect($user->fresh()->locale)->toBe('nl_BE')
        ->and(app()->getLocale())->toBe('nl');
});

test('it keeps the app locale unchanged when the language is not supported', function () {
    app()->setLocale('en');
    $user = User::factory()->create(['locale' => 'en_US']);

    app(UpdateUserLocale::class)->handle($user, 'fr_FR');

    expect($user->fresh()->locale)->toBe('fr_FR')
        ->and(app()->getLocale())->toBe('en');
});
