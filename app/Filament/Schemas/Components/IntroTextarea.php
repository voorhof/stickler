<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\Textarea;

class IntroTextarea
{
    public static function make(int $maxLength = 512, int $rows = 2): Textarea
    {
        return Textarea::make('intro')
            ->label(__('Summary'))
            ->required()
            ->maxLength($maxLength)
            ->rows($rows)
            ->autosize()
            ->trim()
            ->belowContent(__('These content summaries are used on overview pages, for SEO and to share content.'))
            ->columnSpanFull();
    }
}
