<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\Laravel\OptimizeClearAction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;

use function Pest\Livewire\livewire;

use Spatie\Health\ResultStores\ResultStore;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'access health page',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

describe('authorization', function () {
    it('is not authorized for users without the health page permission', function () {
        $restrictedUser = User::factory()->create();
        $restrictedRole = Role::factory()->create(['name' => 'Restricted', 'guard_name' => 'web']);
        $restrictedRole->givePermissionTo('access admin');
        $restrictedUser->assignRole($restrictedRole);

        $this->actingAs($restrictedUser);

        expect(OptimizeClearAction::make()->isAuthorized())->toBeFalse();
    });
});

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = OptimizeClearAction::make();
        expect($action->getName())->toBe('optimizeClear');
    });
});

describe('action execution', function () {
    it('can call the optimizeClear action', function () {
        Artisan::shouldReceive('call')->with('optimize:clear')->once();

        $page = new class extends Page
        {
            public function render(): View
            {
                $checkResults = app(ResultStore::class)->latestResults();

                return view('filament.pages.spatie.health', ['checkResults' => $checkResults]);
            }

            public function getHeaderActions(): array
            {
                return [
                    OptimizeClearAction::make(),
                ];
            }
        };

        livewire($page::class)
            ->callAction('optimizeClear')
            ->assertNotified();
    });
});
