<?php

namespace App\Filament\Pages\Spatie;

use App\Filament\Actions\Laravel\OptimizeAction;
use App\Filament\Actions\Laravel\OptimizeClearAction;
use App\Filament\Actions\Spatie\HealthCheckAction;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Health\ResultStores\ResultStore;
use UnitEnum;

class Health extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Heart;

    protected string $view = 'filament.pages.spatie.health';

    protected static ?int $navigationSort = 9600;

    public function getTitle(): string|Htmlable
    {
        return __('Website health status');
    }

    public static function getNavigationLabel(): string
    {
        return __('Website health status');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Settings');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('access health page') ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'checkResults' => app(ResultStore::class)->latestResults(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            OptimizeAction::make(),
            OptimizeClearAction::make(),
            HealthCheckAction::make(),
        ];
    }
}
