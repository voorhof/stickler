<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class AccountWidget extends Widget
{
    protected static ?int $sort = -30;

    protected int|string|array $columnSpan = [
        'md' => 3,
        '2xl' => 2,
    ];

    protected static bool $isLazy = false;

    protected string $view = 'filament-panels::widgets.account-widget';

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }
}
