<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection */

use App\Filament\Actions\ArchiveBulkAction;
use App\Filament\Resources\Messages\Pages\ListMessages;
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
        $action = ArchiveBulkAction::make();

        expect($action->getName())->toBe('archiveBulk');
    });
});

describe('action execution', function () {
    it('archives selected messages by setting archived_at', function () {
        $messages = Message::factory()->count(2)->create(['archived_at' => null]);

        livewire(ListMessages::class)
            ->callTableBulkAction('archiveBulk', $messages->modelKeys())
            ->assertNotified();

        foreach ($messages as $message) {
            $message->refresh();

            expect($message->archived_at)->not->toBeNull();
        }
    });
});
