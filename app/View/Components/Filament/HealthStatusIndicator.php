<?php

namespace App\View\Components\Filament;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Spatie\Health\Enums\Status;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;

class HealthStatusIndicator extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public StoredCheckResult $result,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.filament.health-status-indicator', [
            'result' => $this->result,
            'backgroundColor' => fn (string $status) => $this->getBackgroundColor($status),
            'iconColor' => fn (string $status) => $this->getIconColor($status),
            'icon' => fn (string $status) => $this->getIcon($status),
        ]);
    }

    protected function getBackgroundColor(string $status): string
    {
        return match ($status) {
            Status::ok()->value => 'fi-color-success',
            Status::warning()->value => 'fi-color-warning',
            Status::skipped()->value => 'fi-color-info',
            Status::failed()->value, Status::crashed()->value => 'fi-color-danger',
            default => 'fi-color-gray'
        };
    }

    protected function getIconColor(string $status): string
    {
        return match ($status) {
            Status::ok()->value => 'fi-color-success',
            Status::warning()->value => 'fi-color-warning',
            Status::skipped()->value => 'fi-color-info',
            Status::failed()->value, Status::crashed()->value => 'fi-color-danger',
            default => 'fi-color-gray'
        };
    }

    protected function getIcon(string $status): string
    {
        return match ($status) {
            Status::ok()->value => 'check-circle',
            Status::warning()->value => 'exclamation-circle',
            Status::skipped()->value => 'arrow-right-circle',
            Status::failed()->value, Status::crashed()->value => 'x-circle',
            default => ''
        };
    }
}
