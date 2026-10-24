<?php

namespace App\Filament\Resources\PageViews;

use App\Filament\Resources\PageViews\Pages\ListPageViews;
use App\Filament\Resources\PageViews\Tables\PageViewsTable;
use App\Models\PageView;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PageViewResource extends Resource
{
    protected static ?string $model = PageView::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChartBar;

    protected static bool $isGloballySearchable = false;

    protected static ?int $navigationSort = 9400; // Specify the order in which navigation items are listed.

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_bot', false)
            ->selectRaw('MIN(id) as id, path, route_name, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as unique_visitors')
            ->groupBy('path', 'route_name');
    }

    public static function table(Table $table): Table
    {
        return PageViewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPageViews::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Page view');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Page views');
    }
}
