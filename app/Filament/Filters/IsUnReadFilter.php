<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class IsUnReadFilter extends BaseFilter
{
    protected string $formComponent = Toggle::class;

    public static function getDefaultName(): ?string
    {
        return 'isUnRead';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Unread'));

        $this->query(fn (Builder $query) => $query->where('read', false));

        $this->indicateUsing(function (array $state): array {
            if (! ($state['isActive'] ?? false)) {
                return [];
            }

            $indicator = $this->getIndicator();

            if (! $indicator instanceof Indicator) {
                $indicator = Indicator::make($indicator);
            }

            return [$indicator];
        });

        // Make sure the unread filter is mutually exclusive with the read and replied filters
        $this->modifyFormFieldUsing(fn (Toggle $field) => $field
            ->live()
            ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../isRead.isActive', false) : null)
            ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../isReplied.isActive', false) : null)
            ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../isReplyRead.isActive', false) : null),
        );
    }

    public function toggle(): static
    {
        $this->formComponent(Toggle::class);

        return $this;
    }

    public function formComponent(string $component): static
    {
        $this->formComponent = $component;

        return $this;
    }

    public function getFormField(): Field
    {
        $field = $this->formComponent::make('isActive')
            ->label($this->getLabel());

        if (filled($defaultState = $this->getDefaultState())) {
            $field->default($defaultState);
        }

        return $field;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResetState(): array
    {
        if ($this->hasSchema()) {
            return parent::getResetState();
        }

        return ['isActive' => false];
    }
}
