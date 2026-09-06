<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection, PhpUndefinedFieldInspection */

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Exceptions\RoleAlreadyExists;

uses(RefreshDatabase::class);

test('a role can be created', function () {
    $user = User::factory()->create();
    $role = Role::forceCreate([
        'name' => 'admin',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($role->name)->toBe('admin')
        ->and($role->guard_name)->toBe('web');
});

test('a role can be mass assigned name and guard_name', function () {
    $role = new Role([
        'name' => 'editor',
        'guard_name' => 'api',
        'order_column' => 5,
    ]);

    expect($role->name)->toBe('editor')
        ->and($role->guard_name)->toBe('api')
        ->and($role->order_column)->toBe(5);
});

test('a permission can be created', function () {
    $permission = Permission::create(['name' => 'edit articles']);

    expect($permission->name)->toBe('edit articles')
        ->and($permission->guard_name)->toBe('web');
});

test('a permission can be assigned to a role', function () {
    $user = User::factory()->create();
    $role = Role::forceCreate([
        'name' => 'admin',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $permission = Permission::create(['name' => 'edit articles']);

    $role->givePermissionTo($permission);

    expect($role->hasPermissionTo('edit articles'))->toBeTrue();
});

test('a role can have multiple permissions', function () {
    $user = User::factory()->create();
    $role = Role::forceCreate([
        'name' => 'admin',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $permission1 = Permission::create(['name' => 'edit articles']);
    $permission2 = Permission::create(['name' => 'delete articles']);

    $role->givePermissionTo($permission1, $permission2);

    expect($role->permissions)->toHaveCount(2)
        ->and($role->hasPermissionTo('edit articles'))->toBeTrue()
        ->and($role->hasPermissionTo('delete articles'))->toBeTrue();
});

test('a permission can belong to multiple roles', function () {
    $user = User::factory()->create();
    $role1 = Role::forceCreate([
        'name' => 'admin',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $role2 = Role::forceCreate([
        'name' => 'editor',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $permission = Permission::create(['name' => 'edit articles']);

    $role1->givePermissionTo($permission);
    $role2->givePermissionTo($permission);

    expect($permission->roles)->toHaveCount(2)
        ->and($permission->roles->contains($role1))->toBeTrue()
        ->and($permission->roles->contains($role2))->toBeTrue();
});

test('a role name must be unique for a given guard', function () {
    $user = User::factory()->create();
    Role::forceCreate([
        'name' => 'admin',
        'guard_name' => 'web',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect(fn () => Role::forceCreate([
        'name' => 'admin',
        'guard_name' => 'web',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]))
        ->toThrow(RoleAlreadyExists::class);
});

test('a role can be created with a different guard and same name', function () {
    $user = User::factory()->create();
    $role1 = Role::forceCreate([
        'name' => 'admin',
        'guard_name' => 'web',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $role2 = Role::forceCreate([
        'name' => 'admin',
        'guard_name' => 'api',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($role1->name)->toBe('admin')
        ->and($role1->guard_name)->toBe('web')
        ->and($role2->name)->toBe('admin')
        ->and($role2->guard_name)->toBe('api');
});

test('a permission can be revoked from a role', function () {
    $user = User::factory()->create();
    $role = Role::forceCreate([
        'name' => 'admin',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $permission = Permission::create(['name' => 'edit articles']);

    $role->givePermissionTo($permission);
    expect($role->hasPermissionTo('edit articles'))->toBeTrue();

    $role->revokePermissionTo($permission);
    expect($role->hasPermissionTo('edit articles'))->toBeFalse();
});

test('role and permission factories work', function () {
    User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();

    $role->givePermissionTo($permission);

    expect($role->name)->not->toBeEmpty()
        ->and($permission->name)->not->toBeEmpty()
        ->and($role->hasPermissionTo($permission->name))->toBeTrue();
});

test('a role can be assigned to a user', function () {
    $user = User::factory()->create();
    $role = Role::forceCreate([
        'name' => 'admin',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $user = User::factory()->create();

    $user->assignRole($role);

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($role->users)->toHaveCount(1)
        ->and($role->users->first()->id)->toBe($user->id);
});

test('it has a default order_column of one', function () {
    User::factory()->create();
    $role = Role::factory()->create();

    expect($role->order_column)->toBe(1);
});

test('it can be reordered', function () {
    User::factory()->create();
    $role1 = Role::factory()->create(['order_column' => 1]);
    $role2 = Role::factory()->create(['order_column' => 2]);

    Role::swapOrder($role1, $role2);

    expect($role1->fresh()->order_column)->toBe(2)
        ->and($role2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    User::factory()->create();
    $role1 = Role::factory()->create(['order_column' => 1]);
    $role2 = Role::factory()->create(['order_column' => 2]);
    $role3 = Role::factory()->create(['order_column' => 3]);

    $role1->update(['order_column' => 3]);

    expect($role1->fresh()->order_column)->toBe(3)
        ->and($role2->fresh()->order_column)->toBe(1)
        ->and($role3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $user = User::factory()->create();
    $role1 = Role::factory()->create(['order_column' => 1]);
    $role2 = Role::factory()->create(['order_column' => 2]);

    $newRole = Role::forceCreate([
        'name' => 'admin',
        'order_column' => 1,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newRole->fresh()->order_column)->toBe(1)
        ->and($role1->fresh()->order_column)->toBe(2)
        ->and($role2->fresh()->order_column)->toBe(3);
});

test('it does reorder when creating a new model without specific order_column', function () {
    $user = User::factory()->create();
    $role1 = Role::factory()->create(['order_column' => 1]);
    $role2 = Role::factory()->create(['order_column' => 2]);

    $newRole = Role::forceCreate([
        'name' => 'admin',
        // order_column will be 1 (because of SortableOnUpdate)
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newRole->fresh()->order_column)->toBe(1)
        ->and($role1->fresh()->order_column)->toBe(2)
        ->and($role2->fresh()->order_column)->toBe(3);
});

test('it returns name as title attribute', function () {
    User::factory()->create();
    $role = Role::factory()->create(['name' => 'super-admin']);

    expect($role->title)->toBe('super-admin');
});

test('it can be soft deleted', function () {
    User::factory()->create();
    $role = Role::factory()->create();

    $role->delete();

    $this->assertSoftDeleted(Role::class, ['id' => $role->id]);
});

test('it can be restored after soft delete', function () {
    User::factory()->create();
    $role = Role::factory()->softDeleted()->create();

    $role->restore();

    $this->assertNotSoftDeleted(Role::class, ['id' => $role->id]);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['created_by_user_id' => $user->id]);

    expect($role->creator)->toBeInstanceOf(User::class)
        ->and($role->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['updated_by_user_id' => $user->id]);

    expect($role->updater)->toBeInstanceOf(User::class)
        ->and($role->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $role = new Role;

    expect($role->creator)->toBeInstanceOf(User::class)
        ->and($role->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $role = new Role;

    expect($role->updater)->toBeInstanceOf(User::class)
        ->and($role->updater->name)->toBe('Guest User');
});

test('it configures activity log options correctly', function () {
    $role = new Role;
    $options = $role->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});
