<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Resources\PageViews\Pages\ListPageViews;
use App\Models\PageView;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view pageviews',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

describe('listing page views', function () {
    test('it groups page views by path with their total and unique visitor counts', function () {
        PageView::factory()->create(['path' => '/contact', 'route_name' => 'contact.index', 'visitor_hash' => hash('sha256', 'a'), 'viewed_on' => now()->toDateString()]);
        PageView::factory()->create(['path' => '/contact', 'route_name' => 'contact.index', 'visitor_hash' => hash('sha256', 'a'), 'viewed_on' => now()->toDateString()]);
        PageView::factory()->create(['path' => '/contact', 'route_name' => 'contact.index', 'visitor_hash' => hash('sha256', 'b'), 'viewed_on' => now()->toDateString()]);
        PageView::factory()->create(['path' => '/terms', 'route_name' => 'terms.index', 'visitor_hash' => hash('sha256', 'a'), 'viewed_on' => now()->toDateString()]);

        $records = livewire(ListPageViews::class)
            ->assertCountTableRecords(2)
            ->instance()
            ->getTableRecords();

        expect($records->pluck('views', 'path')->all())->toBe(['/contact' => 3, '/terms' => 1])
            ->and($records->pluck('unique_visitors', 'path')->all())->toBe(['/contact' => 2, '/terms' => 1]);
    });

    test('it excludes bot page views from the grouped counts', function () {
        PageView::factory()->create(['path' => '/contact', 'route_name' => 'contact.index', 'visitor_hash' => hash('sha256', 'a'), 'viewed_on' => now()->toDateString()]);
        PageView::factory()->bot()->create(['path' => '/contact', 'route_name' => 'contact.index', 'visitor_hash' => hash('sha256', 'bot'), 'viewed_on' => now()->toDateString()]);
        PageView::factory()->bot()->create(['path' => '/bot-only', 'route_name' => 'contact.index', 'visitor_hash' => hash('sha256', 'bot'), 'viewed_on' => now()->toDateString()]);

        $records = livewire(ListPageViews::class)
            ->assertCountTableRecords(1)
            ->instance()
            ->getTableRecords();

        expect($records->pluck('views', 'path')->all())->toBe(['/contact' => 1])
            ->and($records->pluck('unique_visitors', 'path')->all())->toBe(['/contact' => 1]);
    });

    test('it shows the last visit time per page', function () {
        PageView::factory()->create(['path' => '/contact', 'visitor_hash' => hash('sha256', 'a'), 'viewed_on' => now()->subDays(2)->toDateString(), 'created_at' => now()->subDays(2)]);
        PageView::factory()->create(['path' => '/contact', 'visitor_hash' => hash('sha256', 'b'), 'viewed_on' => now()->toDateString(), 'created_at' => now()->subHour()]);

        $records = livewire(ListPageViews::class)
            ->assertCountTableRecords(1)
            ->instance()
            ->getTableRecords();

        expect(Illuminate\Support\Carbon::parse($records->first()->last_visited_at)->toDateTimeString())
            ->toBe(now()->subHour()->startOfSecond()->toDateTimeString());
    });

    test('it searches the grouped page views without aggregate errors', function () {
        PageView::factory()->create(['path' => '/temporis', 'route_name' => 'pages.show', 'visitor_hash' => hash('sha256', 'a'), 'viewed_on' => now()->toDateString()]);
        PageView::factory()->create(['path' => '/contact', 'route_name' => 'contact.index', 'visitor_hash' => hash('sha256', 'b'), 'viewed_on' => now()->toDateString()]);

        $records = livewire(ListPageViews::class)
            ->searchTable('temporis')
            ->assertCountTableRecords(1)
            ->instance()
            ->getTableRecords();

        expect($records->pluck('path')->all())->toBe(['/temporis']);
    });

    test('it filters page views by the viewed on date range', function () {
        PageView::factory()->create(['path' => '/old', 'viewed_on' => now()->subDays(10)->toDateString()]);
        PageView::factory()->create(['path' => '/recent', 'viewed_on' => now()->subDay()->toDateString()]);

        livewire(ListPageViews::class)
            ->filterTable('viewed_on', [
                'viewed_from' => now()->subDays(3)->toDateString(),
            ])
            ->assertCountTableRecords(1)
            ->assertSeeText('/recent')
            ->assertDontSeeText('/old');
    });
});

describe('access', function () {
    test('only users with the view pageviews permission can view pageviews', function () {
        $userWithoutPermission = User::factory()->create();

        expect($userWithoutPermission->can('viewAny', PageView::class))->toBeFalse()
            ->and(User::query()->first()->can('viewAny', PageView::class))->toBeTrue();
    });

    test('nobody can create or update page views', function () {
        $adminUser = User::query()->first();

        expect($adminUser->can('create', PageView::class))->toBeFalse()
            ->and($adminUser->can('update', PageView::class))->toBeFalse();
    });
});
