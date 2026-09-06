<?php

use App\Filament\Actions\Laravel\OptimizeAction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
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
    $action = OptimizeAction::make();
    expect($action->getName())->toBe('optimize');
});

it('can call the optimize action', function () {
    Artisan::shouldReceive('call')->with('optimize')->once();

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
                OptimizeAction::make(),
            ];
        }
    };

    livewire($page::class)
        ->callAction('optimize')
        ->assertNotified();
});
