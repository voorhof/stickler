<?php

use App\Filament\Traits\FormatsModelType;

beforeEach(function () {
    app()->setLocale('en_US');

    $this->formatter = new class
    {
        use FormatsModelType;

        public function format(mixed $state): ?string
        {
            return self::formatModelType(type: $state);
        }
    };
});

it('returns a dash when type is blank', function () {
    expect($this->formatter->format(null))->toBe('-')
        ->and($this->formatter->format(''))->toBe('-');
});

it('formats a standard model name correctly', function () {
    // App\Models\User -> User -> user -> User
    expect($this->formatter->format('App\Models\User'))->toBe('User')
        ->and($this->formatter->format('App\Models\Post'))->toBe('Post');

});
