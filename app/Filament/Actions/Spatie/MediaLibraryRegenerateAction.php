<?php

namespace App\Filament\Actions\Spatie;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class MediaLibraryRegenerateAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'mediaGenerate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Regenerate conversions'));

        $this->icon(Heroicon::OutlinedArrowPath);

        $this->color(fn (): string => 'gray');

        $this->size('sm');

        $this->authorize(fn (): string => 'update');

        $this->rateLimit(fn (): int => 1);

        $this->requiresConfirmation();

        $this->modalHeading(fn (): string => __('Regenerate conversions'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('This will regenerate all the derived images of media.'));

        $this->action(function (): void {
            // Execute the command
            Artisan::call('media-library:regenerate --with-responsive-images --force --queue-all');

            // Capture and clean the full output
            $output = mb_trim(Artisan::output());

            // Extract the last line, falling back to a default message if output is empty
            $lines = ! empty($output) ? explode(PHP_EOL, $output) : [];
            $lastLine = ! empty($lines) ? end($lines) : __('Regenerating media queued');

            // Send a custom notification with the output
            Notification::make()
                ->title(__('Regenerating media queued'))
                ->body(__($lastLine)) // Display captured output in the body
                ->success()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}
