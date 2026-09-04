<?php

namespace App\Filament\Resources\Messages\Schemas\Sections;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class MessageSection
{
    public static function make(): Section
    {
        return Section::make(__('Message'))
            ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('email')
                    ->label(__('Email address'))
                    ->columnSpanFull(),
                TextEntry::make('phone')
                    ->label(__('Phone'))
                    ->columnSpanFull(),
                TextEntry::make('subject')
                    ->label(__('Subject'))
                    ->columnSpanFull(),
                TextEntry::make('message')
                    ->label(__('Message'))
                    ->columnSpanFull(),
            ]);
    }
}
