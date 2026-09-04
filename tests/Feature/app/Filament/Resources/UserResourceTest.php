<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $adminUser = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => 'Admin', 'guard_name' => 'web', 'created_by_user_id' => $adminUser->id, 'updated_by_user_id' => $adminUser->id]);

    $permissions = collect([
        'access admin',
        'create users',
        'view users',
        'update users',
        'delete users',
        'update roles',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole->givePermissionTo($permissions);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

it('can list users', function () {
    $users = User::factory()->count(3)->create();

    livewire(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords($users)
        ->assertCountTableRecords(4); // 3 newly created users + the admin user
});

it('can reorder users', function () {
    $user1 = User::factory()->create(['order_column' => 1]);
    $user2 = User::factory()->create(['order_column' => 2]);

    livewire(ListUsers::class)
        ->call('reorderTable', [$user2->id, $user1->id]);

    expect($user1->fresh()->order_column)->toBe(2)
        ->and($user2->fresh()->order_column)->toBe(1);
});

it('can search users on the list page', function () {
    $matchingUser = User::factory()->create(['name' => 'Wonder']);
    $otherUser = User::factory()->create(['name' => 'Builder']);

    livewire(ListUsers::class)
        ->searchTable('Wonder')
        ->assertCanSeeTableRecords([$matchingUser])
        ->assertCanNotSeeTableRecords([$otherUser]);
});

it('can filter verified users on the list page table', function () {
    $verifiedUser = User::factory()->create(['email_verified_at' => Carbon::now()]);
    $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

    livewire(ListUsers::class)
        ->filterTable('verified users')
        ->assertCanSeeTableRecords([$verifiedUser])
        ->assertCanNotSeeTableRecords([$unverifiedUser]);
});

it('can filter users by trashed state on the list page table', function () {
    $activeUser = User::factory()->create();
    $trashedUser = User::factory()->create();
    $trashedUser->delete();

    livewire(ListUsers::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedUser])
        ->assertCanNotSeeTableRecords([$activeUser])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activeUser, $trashedUser]);
});

it('validates required fields when creating a user', function () {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => null,
            'email' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'required',
        ])
        ->assertNotNotified();
});

it('can create a user', function () {
    // Mail::fake();
    $email = 'new.user@example.com';

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'User',
            'email' => $email,
            'password' => 'password',
            'gender' => 'unknown',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $createdUser = User::query()->where('email', $email)->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser?->name)->toBe('User');

    $this->assertDatabaseHas(User::class, [
        'email' => $createdUser->email,
    ]);

    // Todo
    // Mail::assertQueued(UserCreatedMail::class, function (UserCreatedMail $mail) use ($createdUser) {
    //    return $mail->hasTo($createdUser->email);
    // });
});

it('validates unique email when editing a user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $otherUser->email,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'email' => 'unique',
        ])
        ->assertNotNotified();
});

it('can edit a user', function () {
    $user = User::factory()->create();
    $updatedEmail = 'updated.user@example.com';

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Person',
            'email' => $updatedEmail,
            'slug' => 'updated-slug',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $user->refresh();

    expect($user->name)->toBe('Person')
        ->and($user->email)->toBe($updatedEmail);

    $this->assertDatabaseHas(User::class, [
        'email' => $user->email,
        'slug' => 'updated-slug',
    ]);
});

it('can delete a user', function () {
    $user = User::factory()->create();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(User::class, [
        'id' => $user->id,
    ]);
});

it('can force delete a user', function () {
    $user = User::factory()->create();
    $user->delete();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(User::class, [
        'id' => $user->id,
    ]);
});

it('can restore a user', function () {
    $user = User::factory()->create();
    $user->delete();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'deleted_at' => null,
    ]);
});

it('can delete users in bulk', function () {
    $users = User::factory()->count(2)->create();

    livewire(ListUsers::class)
        ->callTableBulkAction('softDeleteBulk', $users)
        ->assertNotified();

    $users->each(fn (User $user) => $this->assertSoftDeleted(User::class, [
        'id' => $user->id,
    ]));
});

it('can restore users in bulk', function () {
    $users = User::factory()->count(2)->create();
    $users->each->delete();

    livewire(ListUsers::class)
        ->filterTable('trashed', false)
        ->callTableBulkAction('restoreDeletedBulk', $users)
        ->assertNotified();

    $users->each(fn (User $user) => $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'deleted_at' => null,
    ]));
});

it('can force delete users in bulk', function () {
    $users = User::factory()->count(2)->create();
    $users->each->delete();

    livewire(ListUsers::class)
        ->filterTable('trashed', false)
        ->callTableBulkAction('hardDeleteBulk', $users)
        ->assertNotified();

    $users->each(fn (User $user) => $this->assertDatabaseMissing(User::class, [
        'id' => $user->id,
    ]));
});

it('denies listing users when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListUsers::class)
        ->assertForbidden();
});

it('denies loading create user page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateUser::class)
        ->assertForbidden();
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view users']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activeUser = User::factory()->create();
    $trashedUser = User::factory()->create();
    $trashedUser->delete();

    livewire(ListUsers::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListUsers::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(User::class, [
        'id' => $activeUser->id,
        'deleted_at' => null,
    ]);

    $this->assertSoftDeleted(User::class, [
        'id' => $trashedUser->id,
    ]);
});

it('denies loading edit user page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $user = User::factory()->create();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting a user when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view users', 'update users']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $user = User::factory()->create();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'deleted_at' => null,
    ]);
});

it('denies force deleting a user when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view users', 'update users']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $user = User::factory()->create();
    $user->delete();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertActionHidden(HardDeleteAction::class);

    $this->assertSoftDeleted(User::class, [
        'id' => $user->id,
    ]);
});

it('denies restoring a user when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view users', 'update users']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $user = User::factory()->create();
    $user->delete();

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertActionHidden(RestoreDeletedAction::class);

    $this->assertSoftDeleted(User::class, [
        'id' => $user->id,
    ]);
});
