<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class PendingOrdersWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 11;

    protected int|array|null $columns = 1;

    protected int|string|array $columnSpan = [
        'md' => 3,
        '2xl' => 2,
    ];

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    public static function getStatColor(int $count): string
    {
        return $count > 0 ? 'warning' : 'success';
    }

    protected function getStats(): array
    {
        $pendingCount = Order::where('status', OrderStatus::Pending)->count();

        $color = self::getStatColor($pendingCount);

        return [
            Stat::make(
                __('Pending orders'),
                new HtmlString('<span class="fi-link fi-color fi-color-'.$color.' fi-text-color-700 dark:fi-text-color-300">'.$pendingCount.'</span>'),
            )
                ->url(OrderResource::getUrl('index', [
                    'filters' => [
                        'status' => ['value' => OrderStatus::Pending->value],
                    ],
                ])),
        ];
    }
}
