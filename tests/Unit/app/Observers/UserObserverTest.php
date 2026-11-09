<?php

use App\Exceptions\AdministrativeLockoutException;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on user create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $user = User::create([
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
    ]);

    expect($user->created_by_user_id)->toBe($creator->id)
        ->and($user->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $user->update(['name' => 'Updated User']);

    expect($user->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($user->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('throws administrative lockout exception when deleting the sole admin', function (): void {
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    expect(fn () => $admin->delete())
        ->toThrow(AdministrativeLockoutException::class);
});

it('throws administrative lockout exception when force deleting the sole admin', function (): void {
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    expect(fn () => $admin->forceDelete())
        ->toThrow(AdministrativeLockoutException::class);
});

it('allows deleting an admin when another active admin exists', function (): void {
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $admin1 = User::factory()->create();
    $admin1->assignRole($adminRole);

    $admin2 = User::factory()->create();
    $admin2->assignRole($adminRole);

    expect($admin1->delete())->toBeTrue()
        ->and(User::find($admin1->id))->toBeNull()
        ->and(User::withTrashed()->find($admin1->id))->not->toBeNull();
});

it('allows deleting non-admin users freely', function (): void {
    $user = User::factory()->create();

    expect($user->delete())->toBeTrue()
        ->and(User::find($user->id))->toBeNull();
});
