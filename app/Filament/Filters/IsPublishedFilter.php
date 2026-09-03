<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class IsPublishedFilter extends BaseFilter
{
    protected string $formComponent = Toggle::class;

    public static function getDefaultName(): ?string
    {
        return 'isPublished';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Published'));

        $this->baseQuery(fn (Builder $query) => $query->whereNotNull('published_at'));

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

        // Make sure the isPublished filter is mutually exclusive with the isUnpublished filter
        $this->modifyFormFieldUsing(fn (Toggle $field) => $field
            ->live()
            ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../isUnpublished.isActive', false) : null),
        );
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
