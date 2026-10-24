<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection */

use App\Filament\Actions\VerifyEmailBulkAction;
use App\Filament\Resources\Users\Pages\ListUsers;
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
        'view users',
        'update users',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = VerifyEmailBulkAction::make();

        expect($action->getName())->toBe('verifyEmailBulk');
    });
});

describe('action execution', function () {
    it('verifies selected users by setting email_verified_at', function () {
        $users = User::factory()->count(2)->create(['email_verified_at' => null]);

        livewire(ListUsers::class)
            ->callTableBulkAction('verifyEmailBulk', $users)
            ->assertNotified();

        foreach ($users as $user) {
            $user->refresh();

            expect($user->email_verified_at)->not->toBeNull();
        }
    });
});
