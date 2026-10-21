<?php

use App\Filament\Actions\Spatie\ClearResponseCacheAction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;
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

it('has the correct default name', function () {
    $action = ClearResponseCacheAction::make();
    expect($action->getName())->toBe('clearResponseCache');
});

it('can call the clearResponseCache action', function () {
    Artisan::shouldReceive('call')->with('responsecache:clear')->once();
    Artisan::shouldReceive('output')->once()->andReturn('Cache cleared successfully!');

    $page = new class extends Page
    {
        public function render(): Illuminate\Contracts\View\View
        {
            $checkResults = app(ResultStore::class)->latestResults();

            return view('filament.pages.spatie.health', ['checkResults' => $checkResults]);
        }

        protected function getHeaderActions(): array
        {
            return [
                ClearResponseCacheAction::make(),
            ];
        }
    };

    livewire($page::class)
        ->callAction('clearResponseCache')
        ->assertNotified();
});
