<?php

namespace App\Filament\Resources\Messages\Schemas\Sections;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ReplySection
{
    public static function make(): Section
    {
        return Section::make(__('Reply'))
            ->hiddenOn('create')
            ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
            ->collapsible()
            ->persistCollapsed()
            ->schema([
                Grid::make()->schema([
                    Textarea::make('reply')
                        ->label(__('Reply'))
                        ->maxLength(1000)
                        ->rows(2)
                        ->autosize()
                        ->trim()
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
