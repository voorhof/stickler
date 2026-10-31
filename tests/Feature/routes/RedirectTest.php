<?php

it('redirects the legacy path to the home page', function (string $legacyPath) {
    $this->get($legacyPath)
        ->assertRedirect('/');
})->with([
    'home' => ['/home'],
    'welcome' => ['/welcome'],
]);

it('redirects the account path to the dashboard page', function (string $legacyPath) {
    $this->get($legacyPath)
        ->assertRedirect('/account/dashboard');
})->with([
    'account' => ['/account'],
    'dashboard' => ['/dashboard'],
]);
