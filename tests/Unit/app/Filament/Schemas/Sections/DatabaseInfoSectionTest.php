<?php

use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Models\Message;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Section;

use function Pest\Livewire\livewire;

describe('section creation', function () {
    it('creates database info section', function () {
        $section = DatabaseInfoSection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });
});

it('renders on models that do not have every optional database attribute', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view messages',
        'update messages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);
    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);

    $message = Message::factory()->create();

    livewire(EditMessage::class, ['record' => $message->slug])
        ->assertSuccessful();
});
