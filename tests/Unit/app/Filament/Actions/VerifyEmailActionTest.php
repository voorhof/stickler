<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\VerifyEmailAction;
use App\Filament\Resources\Users\Pages\EditUser;
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
        $action = VerifyEmailAction::make();
        expect($action->getName())->toBe('verifyEmail');
    });
});

describe('visibility', function () {
    it('is hidden if the email is already verified', function () {
        $action = VerifyEmailAction::make();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $action->record($user);

        expect($action->isHidden())->toBeTrue();
    });

    it('is not hidden if the email is not verified', function () {
        $action = VerifyEmailAction::make();
        $user = User::factory()->create(['email_verified_at' => null]);

        $action->record($user);

        expect($action->isHidden())->toBeFalse();
    });
});

describe('action execution', function () {
    it('can verify a user email', function () {
        $user = User::factory()->create(['email_verified_at' => null]);

        livewire(EditUser::class, ['record' => $user->getRouteKey()])
            ->callAction('verifyEmail')
            ->assertNotified();

        $user->refresh();
        expect($user->email_verified_at)->not->toBeNull();
    });
});
