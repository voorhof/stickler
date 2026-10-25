<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\CompleteBulkAction;
use App\Filament\Actions\StatusToggleBulkAction;
use Carbon\CarbonInterface;

test('complete bulk action has the completeBulk default name', function () {
    expect(CompleteBulkAction::getDefaultName())->toBe('completeBulk');
});

test('complete bulk action extends the status toggle bulk action', function () {
    expect(is_subclass_of(CompleteBulkAction::class, StatusToggleBulkAction::class))->toBeTrue();
});

test('complete bulk action stores completion in the completed_at column', function () {
    $action = CompleteBulkAction::make();

    $method = new ReflectionMethod($action, 'getStatusColumn');

    expect($method->invoke($action))->toBe('completed_at');
});

test('complete bulk action uses the current time as the status value', function () {
    $action = CompleteBulkAction::make();

    $method = new ReflectionMethod($action, 'getStatusValue');
    $value = $method->invoke($action);

    expect($value)->toBeInstanceOf(CarbonInterface::class)
        ->and($value->diffInSeconds(now(), absolute: true))->toBeLessThan(5);
});

test('complete bulk action skips records that are already completed', function () {
    $action = CompleteBulkAction::make();

    $method = new ReflectionMethod($action, 'skipsRecordsAlreadyInState');

    expect($method->invoke($action))->toBeTrue();
});

test('complete bulk action requires confirmation', function () {
    expect(CompleteBulkAction::make()->isConfirmationRequired())->toBeTrue();
});
