<?php

namespace App\Filament\Actions\Spatie;

use App\Models\Activity;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Spatie\Activitylog\Actions\CleanActivityLogAction;

class ActivityLogCleanupAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'activityClean';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Clear the history'));

        $this->icon(Heroicon::OutlinedTrash);

        $this->color(fn (): string => 'gray');

        $this->outlined();

        $this->size('sm');

        $this->hidden(fn (): bool => ! Activity::count());

        $this->authorize(auth()->user()?->can('delete activities') ?? false);

        $this->rateLimit(fn (): int => 3);

        $this->requiresConfirmation();

        $this->schema([
            TextInput::make('max_days')
                ->label(__('Keep this number of days'))
                ->numeric()
                ->required()
                ->placeholder(60)
                ->default(60)
                ->minValue(0)
                ->maxValue(366)
                ->step(1)
                ->extraInputAttributes([
                    'min' => 0,
                    'max' => 366,
                    'step' => 1,
                ]),
            Select::make('log_name')
                ->label(__('Log name'))
                ->placeholder(__('All'))
                ->options(fn () => Activity::distinct()->pluck('log_name', 'log_name')->toArray()),
        ]);

        $this->modalHeading(fn (): string => __('Clear the history'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('This will delete all activities older than the requested days.'));

        $this->action(function (array $data): void {
            // Get the number of to keep days from the input field
            $maxAgeInDays = $data['max_days'];

            // Get the log_name from the selected option
            $logName = $data['log_name'] ?? null;

            // Execute the cleanup
            app(CleanActivityLogAction::class)->execute($maxAgeInDays, $logName);

            // Send a custom notification with the output
            Notification::make()
                ->title(__('Activity log is cleaned!'))
                ->success()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}
