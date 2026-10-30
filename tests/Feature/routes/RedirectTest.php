<?php

it('redirects the legacy path to the home page', function (string $legacyPath) {
    $this->get($legacyPath)
        ->assertRedirect('/');
})->with([
    'home' => ['/home'],
    'welcome' => ['/welcome'],
]);
