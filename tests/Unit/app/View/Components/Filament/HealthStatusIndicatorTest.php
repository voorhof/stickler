<?php

use App\View\Components\Filament\HealthStatusIndicator;
use Illuminate\Contracts\View\View;
use Spatie\Health\Enums\Status;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;

function healthStatusIndicatorForTest(): HealthStatusIndicator
{
    return new HealthStatusIndicator(new StoredCheckResult(
        name: 'test',
        label: 'Test',
        notificationMessage: 'Msg',
        shortSummary: 'Summary',
        status: Status::ok(),
        meta: [],
    ));
}

function callHealthStatusIndicatorMethod(string $method, string $status): string
{
    $reflection = new ReflectionMethod(HealthStatusIndicator::class, $method);

    return $reflection->invoke(healthStatusIndicatorForTest(), $status);
}

test('it instantiates health status indicator component and renders view', function () {
    $component = healthStatusIndicatorForTest();
    $view = $component->render();

    expect($component)->toBeInstanceOf(HealthStatusIndicator::class)
        ->and($view)->toBeInstanceOf(View::class)
        ->and($view->name())->toBe('components.filament.health-status-indicator')
        ->and($view->getData())->toHaveKeys(['result', 'backgroundColor', 'iconColor', 'icon']);
});

test('it returns correct background colors for statuses', function () {
    expect(callHealthStatusIndicatorMethod('getBackgroundColor', Status::ok()->value))->toBe('fi-color-success')
        ->and(callHealthStatusIndicatorMethod('getBackgroundColor', Status::warning()->value))->toBe('fi-color-warning')
        ->and(callHealthStatusIndicatorMethod('getBackgroundColor', Status::skipped()->value))->toBe('fi-color-info')
        ->and(callHealthStatusIndicatorMethod('getBackgroundColor', Status::failed()->value))->toBe('fi-color-danger')
        ->and(callHealthStatusIndicatorMethod('getBackgroundColor', Status::crashed()->value))->toBe('fi-color-danger')
        ->and(callHealthStatusIndicatorMethod('getBackgroundColor', 'unknown'))->toBe('fi-color-gray');
});

test('it returns correct icon colors for statuses', function () {
    expect(callHealthStatusIndicatorMethod('getIconColor', Status::ok()->value))->toBe('fi-color-success')
        ->and(callHealthStatusIndicatorMethod('getIconColor', Status::warning()->value))->toBe('fi-color-warning')
        ->and(callHealthStatusIndicatorMethod('getIconColor', Status::skipped()->value))->toBe('fi-color-info')
        ->and(callHealthStatusIndicatorMethod('getIconColor', Status::failed()->value))->toBe('fi-color-danger')
        ->and(callHealthStatusIndicatorMethod('getIconColor', Status::crashed()->value))->toBe('fi-color-danger')
        ->and(callHealthStatusIndicatorMethod('getIconColor', 'unknown'))->toBe('fi-color-gray');
});

test('it returns correct icons for statuses', function () {
    expect(callHealthStatusIndicatorMethod('getIcon', Status::ok()->value))->toBe('check-circle')
        ->and(callHealthStatusIndicatorMethod('getIcon', Status::warning()->value))->toBe('exclamation-circle')
        ->and(callHealthStatusIndicatorMethod('getIcon', Status::skipped()->value))->toBe('arrow-right-circle')
        ->and(callHealthStatusIndicatorMethod('getIcon', Status::failed()->value))->toBe('x-circle')
        ->and(callHealthStatusIndicatorMethod('getIcon', Status::crashed()->value))->toBe('x-circle')
        ->and(callHealthStatusIndicatorMethod('getIcon', 'unknown'))->toBe('');
});
