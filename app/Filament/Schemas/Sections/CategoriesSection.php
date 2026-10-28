<?php

namespace App\Filament\Schemas\Sections;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

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
                    ->relationship(titleAttribute: 'title')
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
