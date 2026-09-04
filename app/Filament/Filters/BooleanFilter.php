<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class BooleanFilter extends BaseFilter
{
    protected string $formComponent = Toggle::class;

    protected bool $operator = true {
        get {
            return $this->operator;
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Use query instead of baseQuery to be able to change the default query when using the filter
        // This is useful for when the name of the column is different from the name of the filter
        $this->query(fn (Builder $query) => $query->where($this->getName(), $this->operator));

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
    }

    public function operator(bool $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function toggle(): static
    {
        $this->formComponent(Toggle::class);

        return $this;
    }

    public function checkbox(): static
    {
        $this->formComponent(Checkbox::class);

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
