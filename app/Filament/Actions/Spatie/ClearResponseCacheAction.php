<?php

namespace App\Filament\Actions\Spatie;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class ClearResponseCacheAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'clearResponseCache';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Clear response cache'));

        $this->icon(Heroicon::OutlinedArrowPathRoundedSquare);

        $this->color(fn (): string => 'gray');

        $this->outlined();

        $this->size('sm');

        $this->authorize(fn (): bool => auth()->user()?->can('access health page') ?? false);

        $this->rateLimit(fn (): int => 3);

        $this->requiresConfirmation();

        $this->modalHeading(fn (): string => __('Clear cache'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('This will clear the response cache.'));

        $this->action(function (): void {
            // Execute the command
            Artisan::call('responsecache:clear');

            // Capture and clean the full output
            $output = mb_trim(Artisan::output());

            // Extract the last line, falling back to a default message if output is empty
            $lines = ! empty($output) ? explode(PHP_EOL, $output) : [];
            $lastLine = ! empty($lines) ? end($lines) : __('All done!');

            // Send a custom notification with the output
            Notification::make()
                ->title(__('Cache cleared!'))
                ->body(__($lastLine)) // Display captured output in the body
                ->success()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}
