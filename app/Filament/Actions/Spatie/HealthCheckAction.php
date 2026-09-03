<?php

namespace App\Filament\Actions\Spatie;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class HealthCheckAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'healthCheck';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Run checks now'));

        $this->icon(Heroicon::OutlinedArrowPath);

        $this->color(fn (): string => 'primary');

        $this->outlined();

        $this->size('sm');

        $this->authorize(auth()->user()?->can('access health page') ?? false);

        $this->rateLimit(fn (): int => 3);

        $this->requiresConfirmation();

        $this->modalHeading(fn (): string => __('Run checks now'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('This will run all the health checks.'));

        $this->action(function (): void {
            // Execute the commands
            Artisan::call('health:queue-check-heartbeat');

            Artisan::call('health:check');

            // Capture and clean the full output
            $output = mb_trim(Artisan::output());

            // Extract the last line, falling back to a default message if output is empty
            $lines = ! empty($output) ? explode(PHP_EOL, $output) : [];
            $lastLine = ! empty($lines) ? end($lines) : __('All done!');

            // Send a custom notification with the output
            Notification::make()
                ->title(__('Website health checked!'))
                ->body(__($lastLine)) // Display captured output in the body
                ->success()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}
