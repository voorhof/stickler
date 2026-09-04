<?php

use App\Console\Commands\GenerateSitemap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

test('it extends command', function () {
    $command = new GenerateSitemap;

    expect($command)->toBeInstanceOf(Command::class);
});

test('it has correct signature and description', function () {
    $command = new GenerateSitemap;

    expect($command->getName())->toBe('sitemap:generate')
        ->and($command->getDescription())->toBe('Generate the sitemap.');
});

test('it executes the command successfully and generates sitemap file', function () {
    Storage::fake('public');

    $this->artisan('sitemap:generate')
        ->expectsOutput('Sitemap generated!')
        ->assertSuccessful();

    Storage::disk('public')->assertExists('sitemap.xml');
});
