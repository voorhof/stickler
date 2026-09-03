<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection */

use App\Filament\Actions\DearchiveBulkAction;
use App\Filament\Resources\Messages\Pages\ListMessages;
use App\Models\Message;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view messages',
        'update messages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has the correct default name', function () {
    $action = DearchiveBulkAction::make();

    expect($action->getName())->toBe('dearchiveBulk');
});

it('dearchives selected messages by setting archived_at to null', function () {
    $messages = Message::factory()->count(2)->archived()->create();

    livewire(ListMessages::class)
        ->removeTableFilter('isArchived')
        ->callTableBulkAction('dearchiveBulk', $messages->modelKeys())
        ->assertNotified();

    foreach ($messages as $message) {
        $message->refresh();

        expect($message->archived_at)->toBeNull();
    }
});
