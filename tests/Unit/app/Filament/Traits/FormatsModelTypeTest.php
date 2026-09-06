<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Resources\Activities\Schemas\Sections\InfoSection;

function formatModelTypeForTest(?string $type): string
{
    $method = new ReflectionMethod(InfoSection::class, 'formatModelType');

    return $method->invoke(null, $type);
}

beforeEach(function () {
    app()->setLocale('en_US');
});

it('returns a dash when type is blank', function () {
    expect(formatModelTypeForTest(null))->toBe('-')
        ->and(formatModelTypeForTest(''))->toBe('-');
});

it('formats a standard model name correctly', function () {
    // App\Models\User -> User -> user -> User
    expect(formatModelTypeForTest('App\Models\User'))->toBe('User')
        ->and(formatModelTypeForTest('App\Models\Post'))->toBe('Post');
});
