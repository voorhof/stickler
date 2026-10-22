<?php

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Models\Message;
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
        'view messages',
        'update messages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = ArchiveAction::make();
        expect($action->getName())->toBe('archive');
    });
});

describe('visibility', function () {
    it('is hidden if the record is already archived', function () {
        $action = ArchiveAction::make();
        $message = Message::factory()->create(['archived_at' => now()]);

        // Set the record on the action
        $action->record($message);

        // The hidden() callback should evaluate to true
        expect($action->isHidden())->toBeTrue();
    });

    it('is not hidden if the record is not archived', function () {
        $action = ArchiveAction::make();
        $message = Message::factory()->create(['archived_at' => null]);

        // Set the record on the action
        $action->record($message);

        // The hidden() callback should evaluate to false
        expect($action->isHidden())->toBeFalse();
    });
});

describe('action execution', function () {
    it('can call the archive action', function () {
        $message = Message::factory()->create(['archived_at' => null]);

        livewire(EditMessage::class, ['record' => $message->slug])
            ->callAction('archive')
            ->assertNotified();

        $message->refresh();
        expect($message->archived_at)->not->toBeNull();
    });
});
