<?php

namespace App\Filament\Actions\Laravel;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class OptimizeClearAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'optimizeClear';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Clear cache'));

        $this->icon(Heroicon::OutlinedTrash);

        $this->color(fn (): string => 'warning');

        $this->outlined();

        $this->size('sm');

        $this->authorize(auth()->user()?->can('access health page') ?? false);

        $this->rateLimit(fn (): int => 3);

        $this->requiresConfirmation();

        $this->modalHeading(fn (): string => __('Clear cache'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('This will remove the cached bootstrap files and application optimisation.'));

        $this->action(function (): void {
            $user = auth()->user();

            // Defer execution until after the HTTP response is sent to avoid
            // gateway timeouts (the `optimize` command reruns config:cache,
            // which can break the current request's DB connection).
            dispatch(function () use ($user): void {
                Artisan::call('optimize:clear');

                if ($user !== null) {
                    Notification::make()
                        ->title(__('Cache cleared!'))
                        ->info()
                        ->sendToDatabase($user, isEventDispatched: true);
                }
            })->afterResponse();

            Notification::make()
                ->title(__('Clear cache'))
                ->body(__('Cache clearing is running in the background.'))
                ->info()
                ->send();
        });
    }
}
