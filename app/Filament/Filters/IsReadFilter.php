<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class IsReadFilter extends BaseFilter
{
    protected string $formComponent = Toggle::class;

    public static function getDefaultName(): ?string
    {
        return 'isRead';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Read'));

        $this->query(fn (Builder $query) => $query->where('read', true));

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

        // Make sure the read filter is mutually exclusive with the unread filter, and exclude the replied filter when toggled off
        $this->modifyFormFieldUsing(fn (Toggle $field) => $field
            ->live()
            ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../isUnRead.isActive', false) : null)
            ->afterStateUpdated(fn (Set $set, $state) => $state ? null : $set('../isReplied.isActive', false))
            ->afterStateUpdated(fn (Set $set, $state) => $state === false ? $set('../isReplyRead.isActive', false) : null),
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
