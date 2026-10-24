<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Resources\PageViews\PageViewResource;
use App\Filament\Widgets\PageViewsWidget;
use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget\Stat;

use function Pest\Livewire\livewire;

/**
 * @return array<Stat>
 */
function pageViewsWidgetStats(): array
{
    $widget = new PageViewsWidget;

    return (fn (): array => $this->getStats())->call($widget);
}

describe('widget statistics', function () {
    it('counts only the page views of today', function () {
        PageView::factory()->count(2)->create(['viewed_on' => now()->toDateString()]);
        PageView::factory()->create(['viewed_on' => now()->subDay()->toDateString()]);

        expect(pageViewsWidgetStats()[0]->getValue())->toBe(2);
    });

    it('counts unique visitors of today by their hash', function () {
        $visitorHash = hash('sha256', 'visitor');

        PageView::factory()->count(3)->create([
            'viewed_on' => now()->toDateString(),
            'visitor_hash' => $visitorHash,
        ]);
        PageView::factory()->create([
            'viewed_on' => now()->toDateString(),
            'visitor_hash' => hash('sha256', 'another-visitor'),
        ]);
        PageView::factory()->create([
            'viewed_on' => now()->subDay()->toDateString(),
            'visitor_hash' => hash('sha256', 'yesterday-visitor'),
        ]);

        expect(pageViewsWidgetStats()[1]->getValue())->toBe(2);
    });

    it('excludes bot page views from both statistics', function () {
        PageView::factory()->create([
            'viewed_on' => now()->toDateString(),
            'visitor_hash' => hash('sha256', 'human'),
        ]);
        PageView::factory()->bot()->count(2)->create([
            'viewed_on' => now()->toDateString(),
            'visitor_hash' => hash('sha256', 'bot'),
        ]);

        $stats = pageViewsWidgetStats();

        expect($stats[0]->getValue())->toBe(1)
            ->and($stats[1]->getValue())->toBe(1);
    });

    it('returns zero for both statistics when there are no page views', function () {
        $stats = pageViewsWidgetStats();

        expect($stats[0]->getValue())->toBe(0)
            ->and($stats[1]->getValue())->toBe(0);
    });
});

describe('widget links', function () {
    it('links both statistics to the page views index', function () {
        $url = PageViewResource::getUrl('index');

        $stats = pageViewsWidgetStats();

        expect($stats[0]->getUrl())->toBe($url)
            ->and($stats[1]->getUrl())->toBe($url);
    });
});

describe('widget display', function () {
    it('renders the page views and unique visitors labels', function () {
        livewire(PageViewsWidget::class)
            ->assertOk()
            ->assertSee(__('Page views today'))
            ->assertSee(__('Unique visitors today'));
    });
});
