<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Widgets\SticklerWidget;
use App\Models\Permission;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();
});

describe('widget visibility', function () {
    it('can be viewed when authenticated', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        expect(SticklerWidget::canView())->toBeTrue();

        livewire(SticklerWidget::class)->assertOk();
    });

    it('cannot be viewed when unauthenticated', function () {
        expect(SticklerWidget::canView())->toBeFalse();
    });
});

describe('sitemap generator', function () {
    it('show the sitemap generator when permitted', function () {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'edit settings', 'guard_name' => 'web']);
        $user->permissions()->attach($permission);
        $this->actingAs($user);

        livewire(SticklerWidget::class)->assertSee(__('Generate sitemap'));
    });

    it('hides the sitemap generator when not permitted', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        livewire(SticklerWidget::class)->assertDontSee(__('Generate sitemap'));
    });
});
