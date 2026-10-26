<?php

use App\Filament\Actions\CompleteAction;

test('complete action has the complete default name', function () {
    expect(CompleteAction::getDefaultName())->toBe('complete');
});

test('complete action requires confirmation', function () {
    expect(CompleteAction::make()->isConfirmationRequired())->toBeTrue();
});

test('complete action is limited to three runs per rate limit window', function () {
    $action = CompleteAction::make();

    $method = new ReflectionMethod($action, 'getRateLimit');

    expect($method->invoke($action))->toBe(3);
});
