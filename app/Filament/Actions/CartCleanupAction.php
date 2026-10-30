<?php

namespace App\Filament\Actions;

use App\Models\Cart;
use App\Services\CartService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CartCleanupAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'cartClean';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Clear carts'));

        $this->icon(Heroicon::OutlinedTrash);

        $this->color(fn (): string => 'gray');

        $this->outlined();

        $this->size('sm');

        $this->hidden(fn (): bool => ! Cart::count());

        $this->authorize(fn (): bool => auth()->user()?->can('delete carts') ?? false);

        $this->rateLimit(fn (): int => 3);

        $this->requiresConfirmation();

        $this->schema([
            TextInput::make('max_days')
                ->label(__('Keep this number of days'))
                ->numeric()
                ->required()
                ->placeholder(30)
                ->default(30)
                ->minValue(0)
                ->maxValue(366)
                ->step(1)
                ->extraInputAttributes([
                    'min' => 0,
                    'max' => 366,
                    'step' => 1,
                ]),
        ]);

        $this->modalHeading(fn (): string => __('Clear carts'));

        $this->modalDescription(fn (): string => __('filament-actions::modal.confirmation').' '.__('This will delete all carts not updated within the requested days.'));

        $this->action(function (array $data): void {
            $maxAgeInDays = (int) $data['max_days'];

            $deletedCount = app(CartService::class)->cleanupOlderThan($maxAgeInDays);

            Notification::make()
                ->title(__('Carts are cleaned!'))
                ->body(__(':count carts deleted.', ['count' => $deletedCount]))
                ->success()
                ->send()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);
        });
    }
}
