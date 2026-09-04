<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class IsArchivedFilter extends BaseFilter
{
    protected string $formComponent = Toggle::class;

    public static function getDefaultName(): ?string
    {
        return 'isArchived';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Hide archived :model', ['model' => $this->getTitleCasePluralModelLabel()]));

        // Use the $state argument to conditionally apply the query constraint
        $this->baseQuery(function (Builder $query, array $state) {
            if ($state['isArchived'] ?? false) {
                $query->whereNull('archived_at');
            }
        });

        $this->default();

        $this->indicateUsing(function (array $state): array {
            if (! ($state['isArchived'] ?? false)) {
                return [];
            }

            $indicator = $this->getIndicator();

            if (! $indicator instanceof Indicator) {
                $indicator = Indicator::make($indicator);
            }

            return [$indicator];
        });
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
        $field = $this->formComponent::make('isArchived')
            ->label($this->getLabel());

        if (filled($defaultState = $this->getDefaultState())) {
            $field->default($defaultState);
        }

        return $field;
    }

    public function getTitleCasePluralModelLabel(): string
    {
        return Str::ucwords($this->getTable()->getPluralModelLabel());
    }

    /**
     * @return array<string, mixed>
     */
    public function getResetState(): array
    {
        if ($this->hasSchema()) {
            return parent::getResetState();
        }

        return ['isArchived' => false];
    }
}
