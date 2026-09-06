<?php

/** @noinspection PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Support\LogOptions;

uses(RefreshDatabase::class);

test('a permission can be created', function () {
    $permission = Permission::create(['name' => 'test permission']);
    expect($permission->name)->toBe('test permission');
});

test('a permission can be mass assigned fillable values', function () {
    $permission = Permission::create([
        'name' => 'test permission',
        'guard_name' => 'web',
    ]);

    expect($permission->name)->toBe('test permission')
        ->and($permission->guard_name)->toBe('web');
});

test('it has a default guard_name of web', function () {
    $permission = new Permission(['name' => 'test permission']);

    expect($permission->guard_name)->toBe('web');
});

test('it returns name as title attribute', function () {
    $permission = Permission::create(['name' => 'edit articles']);

    expect($permission->title)->toBe('edit articles');
});

test('it can be deleted', function () {
    $permission = Permission::create(['name' => 'delete me']);
    $permissionId = $permission->id;
    $permission->delete();

    expect(Permission::find($permissionId))->toBeNull();
});

test('it configures activity log options correctly', function () {
    $permission = new Permission;
    $options = $permission->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});
