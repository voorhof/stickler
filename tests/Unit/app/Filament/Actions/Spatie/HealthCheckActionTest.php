<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\Spatie\HealthCheckAction;
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

        expect(HealthCheckAction::make()->isAuthorized())->toBeFalse();
    });
});

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = HealthCheckAction::make();
        expect($action->getName())->toBe('healthCheck');
    });
});

describe('action execution', function () {
    it('can call the healthCheck action', function () {
        Artisan::shouldReceive('call')->with('health:queue-check-heartbeat')->once();
        Artisan::shouldReceive('call')->with('health:check')->once();
        Artisan::shouldReceive('output')->once()->andReturn("Line 1\nResult: Success");

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
                    HealthCheckAction::make(),
                ];
            }
        };

        livewire($page::class)
            ->callAction('healthCheck')
            ->assertNotified();
    });
});
