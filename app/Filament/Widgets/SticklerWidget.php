<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class SticklerWidget extends Widget
{
    protected static ?int $sort = -20;

    protected int|string|array $columnSpan = [
        'md' => 3,
        '2xl' => 2,
    ];

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.stickler-widget';

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }
}
