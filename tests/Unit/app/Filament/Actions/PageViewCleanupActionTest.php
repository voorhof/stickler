<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\PageViewCleanupAction;
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

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view pageviews',
        'delete pageviews',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = PageViewCleanupAction::make();

        expect($action->getName())->toBe('pageViewClean');
    });
});

describe('action execution', function () {
    it('deletes page views older than the requested days and keeps recent ones', function () {
        PageView::factory()->create(['path' => '/old', 'viewed_on' => now()->subDays(100)->toDateString()]);
        PageView::factory()->create(['path' => '/recent', 'viewed_on' => now()->toDateString()]);

        livewire(ListPageViews::class)
            ->callAction('pageViewClean', [
                'max_days' => 60,
            ])
            ->assertNotified()
            ->assertHasNoFormErrors();

        expect(PageView::where('path', '/old')->exists())->toBeFalse()
            ->and(PageView::where('path', '/recent')->exists())->toBeTrue();
    });
});

describe('validation', function () {
    it('requires max_days', function () {
        PageView::factory()->create();

        livewire(ListPageViews::class)
            ->callAction('pageViewClean', [
                'max_days' => null,
            ])
            ->assertHasFormErrors(['max_days' => 'required']);
    });

    it('validates max_days range', function () {
        PageView::factory()->create();

        livewire(ListPageViews::class)
            ->callAction('pageViewClean', [
                'max_days' => 400,
            ])
            ->assertHasFormErrors(['max_days' => 'max']);
    });
});

describe('visibility', function () {
    it('hides the action when there are no page views', function () {
        PageView::query()->delete();

        livewire(ListPageViews::class)
            ->assertActionHidden('pageViewClean');
    });

    it('hides the action for users without delete pageviews permission', function () {
        PageView::factory()->create();

        $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
        $restrictedRole->givePermissionTo(['access admin', 'view pageviews']);
        $restrictedUser = User::factory()->create();
        $restrictedUser->assignRole($restrictedRole);
        $this->actingAs($restrictedUser);

        livewire(ListPageViews::class)
            ->assertActionHidden('pageViewClean');
    });
});
