<?php

namespace App\Filament\Actions;

use App\Models\PageView;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class PageViewCleanupAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'pageViewClean';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Clear page views'));

        $this->icon(Heroicon::OutlinedTrash);

        $this->color(fn (): string => 'gray');

        $this->outlined();

        $this->size('sm');

        $this->hidden(fn (): bool => ! PageView::count());

        $this->authorize(fn (): bool => auth()->user()?->can('delete pageviews') ?? false);

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
        ]);

        $this->modalHeading(fn (): string => __('Clear page views'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('This will delete all page views older than the requested days.'));

        $this->action(function (array $data): void {
            $maxAgeInDays = (int) $data['max_days'];

            $deletedCount = PageView::where('viewed_on', '<', Carbon::today()->subDays($maxAgeInDays)->toDateString())->delete();

            Notification::make()
                ->title(__('Page views are cleaned!'))
                ->body(__(':count page views deleted.', ['count' => $deletedCount]))
                ->success()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}
