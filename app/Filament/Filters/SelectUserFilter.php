<?php

namespace App\Filament\Filters;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Concerns\HasOptions;
use Filament\Tables\Filters\Concerns\HasPlaceholder;
use Filament\Tables\Filters\Concerns\HasRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SelectUserFilter extends BaseFilter
{
    use HasOptions;
    use HasPlaceholder;
    use HasRelationship;

    public static function getDefaultName(): ?string
    {
        return 'user';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('User'));

        $this->relationship('user', 'name');

        $this->placeholder(
            fn (SelectUserFilter $filter): string => __('filament-tables::table.filters.select.placeholder'),
        );

        $this->indicateUsing(function (array $data): ?string {
            if (blank($data['value'] ?? null)) {
                return null;
            }

            // Fetch the user record to get the full name for the badge
            $user = User::find($data['value']);

            return $user ? __('User').": $user->name" : null;
        });

        $this->resetState(['value' => null]);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $data
     * @return Builder<TModel>
     */
    public function apply(Builder $query, array $data = []): Builder
    {
        $userId = $data['value'] ?? null;

        if (blank($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }

    public function getFormField(): Select
    {
        return Select::make('value')
            ->label($this->getLabel())
            ->placeholder($this->getPlaceholder())
            ->searchable(['name'])
            ->preload($this->isPreloaded())
            ->optionsLimit(50)
            ->relationship(
                $this->getRelationshipName(),
                $this->getRelationshipTitleAttribute(),
            )
            ->getSearchResultsUsing(fn (Select $component, ?string $search): array => $this->getSearchResultsFromRelationship($component, $search))
            ->options(fn (Select $component): ?array => $this->getOptionsFromRelationship($component))
            ->getOptionLabelUsing(fn (Select $component) => $this->getOptionLabelFromRelationship($component))
            ->getOptionLabelFromRecordUsing(fn ($record) => "$record->name");
    }

    /**
     * @return array<string | int, string>
     */
    public function getSearchResultsFromRelationship(Select $select, ?string $search): array
    {
        return $select->getSearchResultsFromRelationship($search);
    }

    /**
     * @return array<string | int, string> | null
     */
    public function getOptionsFromRelationship(Select $select): ?array
    {
        $options = $select->getOptionsFromRelationship();

        if ($options === null) {
            return null;
        }

        return $options;
    }

    public function getOptionLabelFromRelationship(Select $select): ?string
    {
        $record = $select->getSelectedRecord();

        if (! $record) {
            return null;
        }

        return $select->getOptionLabelFromRecord($record);
    }
}
