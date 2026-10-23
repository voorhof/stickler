<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PageViews\PageViewResource;
use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PageViewsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 11;

    protected int|array|null $columns = 1;

    protected int|string|array $columnSpan = [
        'md' => 3,
        '2xl' => 2,
    ];

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $today = now()->toDateString();
        $pageViewsToday = PageView::where('viewed_on', $today)->count();
        $uniqueVisitorsToday = PageView::where('viewed_on', $today)->distinct('visitor_hash')->count('visitor_hash');

        return [
            Stat::make(__('Page views today'), $pageViewsToday)
                ->url(PageViewResource::getUrl('index')),
            Stat::make(__('Unique visitors today'), $uniqueVisitorsToday)
                ->url(PageViewResource::getUrl('index')),
        ];
    }
}
