<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection, PhpMultipleClassDeclarationsInspection */

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Observers\PermissionObserver;
use App\Policies\PermissionPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;

test('a permission can be created', function () {
    $permission = Permission::create(['name' => 'test permission']);

    expect($permission->name)->toBe('test permission')
        ->and($permission->guard_name)->toBe('web');
});

test('a permission can be mass assigned fillable values', function () {
    $permission = new Permission([
        'name' => 'test permission',
        'guard_name' => 'api',
    ]);

    expect($permission->name)->toBe('test permission')
        ->and($permission->guard_name)->toBe('api');
});

test('it has a default guard_name of web', function () {
    $permission = new Permission(['name' => 'test permission']);

    expect($permission->guard_name)->toBe('web');
});

test('a permission name must be unique for a given guard', function () {
    Permission::create(['name' => 'edit articles']);

    expect(fn () => Permission::create(['name' => 'edit articles']))
        ->toThrow(PermissionAlreadyExists::class);
});

test('a permission can be created with a different guard and same name', function () {
    $permission1 = Permission::create(['name' => 'edit articles', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'edit articles', 'guard_name' => 'api']);

    expect($permission1->name)->toBe('edit articles')
        ->and($permission1->guard_name)->toBe('web')
        ->and($permission2->name)->toBe('edit articles')
        ->and($permission2->guard_name)->toBe('api');
});

test('it can be found by name', function () {
    Permission::create(['name' => 'view dashboard']);

    $permission = Permission::findByName('view dashboard');

    expect($permission->name)->toBe('view dashboard');
});

test('it can be found by id', function () {
    $permission = Permission::create(['name' => 'manage users']);

    $found = Permission::findById($permission->id);

    expect($found->id)->toBe($permission->id)
        ->and($found->name)->toBe('manage users');
});

test('it can be found or created', function () {
    $permission1 = Permission::findOrCreate('create reports');
    $permission2 = Permission::findOrCreate('create reports');

    expect($permission1->id)->toBe($permission2->id)
        ->and($permission1->name)->toBe('create reports');
});

test('it returns name as title attribute', function () {
    $permission = Permission::create(['name' => 'edit articles']);

    expect($permission->title)->toBe('edit articles');
});

test('it belongs to many roles', function () {
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

    $permission->syncRoles([$role1, $role2]);

    expect($permission->roles)->toHaveCount(2)
        ->and($permission->roles->contains($role1))->toBeTrue()
        ->and($permission->roles->contains($role2))->toBeTrue();
});

test('it has roles relationship', function () {
    $permission = new Permission;

    expect($permission->roles())->toBeInstanceOf(BelongsToMany::class);
});

test('it has users relationship', function () {
    $permission = new Permission;

    expect($permission->users())->toBeInstanceOf(BelongsToMany::class);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $permission = Permission::factory()->create(['created_by_user_id' => $user->id]);

    expect($permission->creator)->toBeInstanceOf(User::class)
        ->and($permission->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $permission = Permission::factory()->create(['updated_by_user_id' => $user->id]);

    expect($permission->updater)->toBeInstanceOf(User::class)
        ->and($permission->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $permission = new Permission;

    expect($permission->creator)->toBeInstanceOf(User::class)
        ->and($permission->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $permission = new Permission;

    expect($permission->updater)->toBeInstanceOf(User::class)
        ->and($permission->updater->name)->toBe('Guest User');
});

test('it can be deleted', function () {
    $permission = Permission::create(['name' => 'delete me']);
    $permissionId = $permission->id;
    $permission->delete();

    expect(Permission::find($permissionId))->toBeNull();
});

test('permission factory works', function () {
    User::factory()->create();
    $permission = Permission::factory()->create();

    expect($permission->name)->not->toBeEmpty()
        ->and($permission->guard_name)->toBe('web');
});

test('it configures activity log options correctly', function () {
    $permission = new Permission;
    $options = $permission->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});

test('permission model has permission policy attribute configured', function () {
    $attributes = new ReflectionClass(Permission::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(PermissionPolicy::class);
});

test('permission model has observer attribute configured', function () {
    $attributes = new ReflectionClass(Permission::class)->getAttributes(ObservedBy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(PermissionObserver::class);
});
