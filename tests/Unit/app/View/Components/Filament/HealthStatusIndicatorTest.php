<?php

use App\View\Components\Filament\HealthStatusIndicator;
use Illuminate\Contracts\View\View;
use Spatie\Health\Enums\Status;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;

test('it instantiates health status indicator component and renders view', function () {
    $result = new StoredCheckResult(
        name: 'test',
        label: 'Test',
        notificationMessage: 'Msg',
        shortSummary: 'Summary',
        status: Status::ok(),
        meta: [],
    );

    $component = new HealthStatusIndicator($result);
    $view = $component->render();

    expect($component)->toBeInstanceOf(HealthStatusIndicator::class)
        ->and($view)->toBeInstanceOf(View::class)
        ->and($view->name())->toBe('components.filament.health-status-indicator')
        ->and($view->getData())->toHaveKeys(['result', 'backgroundColor', 'iconColor', 'icon']);
});

test('it returns correct background colors for statuses', function () {
    $result = new StoredCheckResult('test', 'Test', 'Msg', 'Summary', Status::ok(), []);
    $component = new class($result) extends HealthStatusIndicator
    {
        public function testGetBackgroundColor(string $status): string
        {
            return $this->getBackgroundColor($status);
        }
    };

    expect($component->testGetBackgroundColor(Status::ok()->value))->toBe('fi-color-success')
        ->and($component->testGetBackgroundColor(Status::warning()->value))->toBe('fi-color-warning')
        ->and($component->testGetBackgroundColor(Status::skipped()->value))->toBe('fi-color-info')
        ->and($component->testGetBackgroundColor(Status::failed()->value))->toBe('fi-color-danger')
        ->and($component->testGetBackgroundColor(Status::crashed()->value))->toBe('fi-color-danger')
        ->and($component->testGetBackgroundColor('unknown'))->toBe('fi-color-gray');
});

test('it returns correct icon colors for statuses', function () {
    $result = new StoredCheckResult('test', 'Test', 'Msg', 'Summary', Status::ok(), []);
    $component = new class($result) extends HealthStatusIndicator
    {
        public function testGetIconColor(string $status): string
        {
            return $this->getIconColor($status);
        }
    };

    expect($component->testGetIconColor(Status::ok()->value))->toBe('fi-color-success')
        ->and($component->testGetIconColor(Status::warning()->value))->toBe('fi-color-warning')
        ->and($component->testGetIconColor(Status::skipped()->value))->toBe('fi-color-info')
        ->and($component->testGetIconColor(Status::failed()->value))->toBe('fi-color-danger')
        ->and($component->testGetIconColor(Status::crashed()->value))->toBe('fi-color-danger')
        ->and($component->testGetIconColor('unknown'))->toBe('fi-color-gray');
});

test('it returns correct icons for statuses', function () {
    $result = new StoredCheckResult('test', 'Test', 'Msg', 'Summary', Status::ok(), []);
    $component = new class($result) extends HealthStatusIndicator
    {
        public function testGetIcon(string $status): string
        {
            return $this->getIcon($status);
        }
    };

    expect($component->testGetIcon(Status::ok()->value))->toBe('check-circle')
        ->and($component->testGetIcon(Status::warning()->value))->toBe('exclamation-circle')
        ->and($component->testGetIcon(Status::skipped()->value))->toBe('arrow-right-circle')
        ->and($component->testGetIcon(Status::failed()->value))->toBe('x-circle')
        ->and($component->testGetIcon(Status::crashed()->value))->toBe('x-circle')
        ->and($component->testGetIcon('unknown'))->toBe('');
});
