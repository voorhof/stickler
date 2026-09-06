<?php

use App\Filament\Actions\Laravel\OptimizeClearAction;
use Illuminate\Contracts\View\View;
use Filament\Pages\Page;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

use function Pest\Livewire\livewire;

use Spatie\Health\ResultStores\ResultStore;

uses(RefreshDatabase::class);

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

it('has the correct default name', function () {
    $action = OptimizeClearAction::make();
    expect($action->getName())->toBe('optimizeClear');
});

it('can call the optimizeClear action', function () {
    Artisan::shouldReceive('call')->with('optimize:clear')->once();

    $page = new class extends Page
    {
        function render(): Illuminate\Contracts\View\View
        {
            $checkResults = app(ResultStore::class)->latestResults();

            return view('filament.pages.spatie.health', ['checkResults' => $checkResults]);
        }

        function getHeaderActions(): array
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
