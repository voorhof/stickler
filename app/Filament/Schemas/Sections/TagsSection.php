<?php

namespace App\Filament\Schemas\Sections;

use Filament\Forms\Components\SpatieTagsInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class TagsSection
{
    public static function make(): Section
    {
        return Section::make(__('Tags'))
            ->icon(Heroicon::OutlinedTag)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                SpatieTagsInput::make('tags')
                    ->hiddenLabel()
                    ->columnSpanFull(),
            ]);
    }
}
