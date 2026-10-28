<?php

namespace App\Filament\Resources\Categories\Schemas\Sections;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class ForModelToggleSection
{
    public static function make(): Section
    {
        return Section::make(__('Information'))
            ->icon(Heroicon::OutlinedInformationCircle)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                Grid::make()->schema([
                    Toggle::make('for_pages')
                        ->label(__('For :model', ['model' => __('Pages')]))
                        ->columnSpanFull(),
                    Toggle::make('for_posts')
                        ->label(__('For :model', ['model' => __('Blogposts')]))
                        ->columnSpanFull(),
                    Toggle::make('for_products')
                        ->label(__('For :model', ['model' => __('Products')]))
                        ->columnSpanFull(),
                    Toggle::make('for_projects')
                        ->label(__('For :model', ['model' => __('Projects')]))
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
