<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\Textarea;

class QuoteTextarea
{
    public static function make(int $maxLength = 172, int $rows = 2): Textarea
    {
        return Textarea::make('quote')
            ->label(__('Quote'))
            ->maxLength($maxLength)
            ->rows($rows)
            ->autosize()
            ->trim()
            ->columnSpanFull();
    }
}
