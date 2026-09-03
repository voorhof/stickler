<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class IsRepliedFilter extends BaseFilter
{
    protected string $formComponent = Toggle::class;

    public static function getDefaultName(): ?string
    {
        return 'isReplied';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('Replied'));

        $this->query(fn (Builder $query) => $query->where('replied', true));

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

        // Make sure the replied filter is mutually exclusive with the unread filter, and includes the read filter when toggled on
        $this->modifyFormFieldUsing(fn (Toggle $field) => $field
            ->live()
            ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../isUnRead.isActive', false) : null)
            ->afterStateUpdated(fn (Set $set, $state) => $state ? $set('../isRead.isActive', true) : null)
            ->afterStateUpdated(fn (Set $set, $state) => $state ? null : $set('../isReplyRead.isActive', false)),
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
