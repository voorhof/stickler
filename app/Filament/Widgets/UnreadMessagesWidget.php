<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Messages\MessageResource;
use App\Models\Message;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class UnreadMessagesWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = [
        'md' => 3,
        '2xl' => 2,
    ];

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    public static function getStatColor(int $count): string
    {
        return $count > 0 ? 'primary' : 'success';
    }

    protected function getStats(): array
    {
        $unreadCount = Message::where('read', false)->count();

        $color = self::getStatColor($unreadCount);

        return [
            Stat::make(
                mb_ucfirst(mb_strtolower(__('The number of unread :models', ['models' => __('Messages')]))),
                new HtmlString('<span class="fi-link fi-color fi-color-'.$color.' fi-text-color-700 dark:fi-text-color-300">'.$unreadCount.'</span>'),
            )
                ->url(MessageResource::getUrl('index', [
                    'filters' => [
                        'isUnRead' => ['isActive' => true],
                        'isArchived' => ['isArchived' => false],
                        'trashed' => ['value' => 1],
                    ],
                ])),
        ];
    }
}
