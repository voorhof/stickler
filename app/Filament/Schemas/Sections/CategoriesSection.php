<?php

namespace App\Filament\Schemas\Sections;

use App\Models\Category;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CategoriesSection
{
    public static function make(string $forModel = ''): Section
    {
        return Section::make(__('Categories'))
            ->icon(Heroicon::OutlinedListBullet)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                Select::make('categories')
                    ->label(__('Categories'))
                    ->hiddenLabel()
                    ->multiple()
                    ->relationship(
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query): Builder => $forModel !== ''
                            ? $query->where($forModel, true)
                            : $query,
                    )
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail) use ($forModel): void {
                            if ($forModel === '' || blank($value)) {
                                return;
                            }

                            $hasUnmatchedCategory = Category::query()
                                ->whereIn('id', (array) $value)
                                ->where($forModel, false)
                                ->exists();

                            if ($hasUnmatchedCategory) {
                                $fail(__('The selected categories are not available for this item.'));
                            }
                        },
                    ])
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->createOptionModalHeading(__('Create a new :model', ['model' => mb_strtolower(__('Category'))]))
                    ->createOptionForm([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->unique()
                            ->required(),
                        Toggle::make('for_pages')
                            ->label(__('For :model', ['model' => __('Pages')]))
                            ->default($forModel === 'for_pages')
                            ->columnSpanFull(),
                        Toggle::make('for_posts')
                            ->label(__('For :model', ['model' => __('Blogposts')]))
                            ->default($forModel === 'for_posts')
                            ->columnSpanFull(),
                        Toggle::make('for_products')
                            ->label(__('For :model', ['model' => __('Products')]))
                            ->default($forModel === 'for_products')
                            ->columnSpanFull(),
                        Toggle::make('for_projects')
                            ->label(__('For :model', ['model' => __('Projects')]))
                            ->default($forModel === 'for_projects')
                            ->columnSpanFull(),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        return Category::create([
                            'title' => $data['title'],
                            'for_pages' => $data['for_pages'],
                            'for_posts' => $data['for_posts'],
                            'for_products' => $data['for_products'],
                            'for_projects' => $data['for_projects'],
                        ])->getKey();
                    }),
            ]);
    }
}
