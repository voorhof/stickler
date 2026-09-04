<?php

use App\Filament\Actions\Spatie\MediaLibraryRegenerateAction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view media',
        'update media',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has the correct default name', function () {
    $action = MediaLibraryRegenerateAction::make();
    expect($action->getName())->toBe('mediaGenerate');
});

it('can call the mediaGenerate action', function () {
    Artisan::shouldReceive('call')->with('media-library:regenerate --with-responsive-images --force --queue-all')->once();
    Artisan::shouldReceive('output')->once()->andReturn("Line 1\nResult: Success");

    $page = new class extends Page implements HasForms
    {
        use InteractsWithForms;

        public function render(): Illuminate\Contracts\View\View
        {
            return view('filament-panels::components.page.index');
        }

        protected function getHeaderActions(): array
        {
            return [
                MediaLibraryRegenerateAction::make(),
            ];
        }
    };

    livewire($page::class)
        ->callAction('mediaGenerate')
        ->assertNotified();
});
