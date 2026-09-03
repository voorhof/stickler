<?php

namespace App\Filament\Actions\Laravel;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class OptimizeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'optimize';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Optimize app'));

        $this->icon(Heroicon::OutlinedRocketLaunch);

        $this->color(fn (): string => 'success');

        $this->outlined();

        $this->size('sm');

        $this->authorize(auth()->user()?->can('access health page') ?? false);

        $this->rateLimit(fn (): int => 3);

        $this->requiresConfirmation();

        $this->modalHeading(fn (): string => __('Optimize app'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('This will cache and optimize the application.'));

        $this->action(function (): void {
            $user = auth()->user();

            // Defer execution until after the HTTP response is sent to avoid
            // gateway timeouts (the `optimize` command reruns config:cache,
            // which can break the current request's DB connection).
            dispatch(function () use ($user): void {
                Artisan::call('optimize');

                if ($user !== null) {
                    Notification::make()
                        ->title(__('App optimized!'))
                        ->info()
                        ->sendToDatabase($user, isEventDispatched: true);
                }
            })->afterResponse();

            Notification::make()
                ->title(__('Optimize app'))
                ->body(__('Optimization is running in the background.'))
                ->info()
                ->send();
        });
    }
}
