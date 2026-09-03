<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class AdminUserAndRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create admin user
        $admin = User::factory()->create([
            'slug' => Str::slug(config('stickler.admin_users.admin.name')),
            'name' => config('stickler.admin_users.admin.name'),
            'email' => config('stickler.admin_users.admin.email'),
            'password' => Hash::make(config('stickler.admin_users.admin.password')),
            'locale' => config('app.locale'),
        ]);

        // Create ceo user
        $ceo = User::factory()->create([
            'slug' => Str::slug(config('stickler.admin_users.ceo.name')),
            'name' => config('stickler.admin_users.ceo.name'),
            'email' => config('stickler.admin_users.ceo.email'),
            'password' => Hash::make(config('stickler.admin_users.ceo.password')),
            'locale' => config('app.locale'),
        ]);


        // Create permissions
        Permission::create(['name' => 'access admin']);
        Permission::create(['name' => 'access telescope']);
        Permission::create(['name' => 'access health page']);

        Permission::create(['name' => 'access settings']);
        Permission::create(['name' => 'edit settings']);

        Permission::create(['name' => 'access logs']);
        Permission::create(['name' => 'download logs']);
        Permission::create(['name' => 'delete logs']);

        Permission::create(['name' => 'create backups']);
        Permission::create(['name' => 'view backups']);
        Permission::create(['name' => 'download backups']);
        Permission::create(['name' => 'delete backups']);

        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'update users']);
        Permission::create(['name' => 'delete users']);

        Permission::create(['name' => 'create roles']);
        Permission::create(['name' => 'view roles']);
        Permission::create(['name' => 'update roles']);
        Permission::create(['name' => 'delete roles']);

        Permission::create(['name' => 'create media']);
        Permission::create(['name' => 'view media']);
        Permission::create(['name' => 'update media']);
        Permission::create(['name' => 'delete media']);

        Permission::create(['name' => 'create posts']);
        Permission::create(['name' => 'view posts']);
        Permission::create(['name' => 'update posts']);
        Permission::create(['name' => 'delete posts']);

        Permission::create(['name' => 'create projects']);
        Permission::create(['name' => 'view projects']);
        Permission::create(['name' => 'update projects']);
        Permission::create(['name' => 'delete projects']);

        Permission::create(['name' => 'create tags']);
        Permission::create(['name' => 'view tags']);
        Permission::create(['name' => 'update tags']);
        Permission::create(['name' => 'delete tags']);

        Permission::create(['name' => 'view messages']);
        Permission::create(['name' => 'update messages']);
        Permission::create(['name' => 'delete messages']);

        Permission::create(['name' => 'view activities']);
        Permission::create(['name' => 'delete activities']);

        // Update the cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign created permissions
        Role::create([
            'name' => 'Admin',
            'order_column' => 1,
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ])->givePermissionTo(Permission::all()); // All permissions for the Admin role

        // Assign the Admin role to the admin users
        $admin->assignRole('Admin');
        $ceo->assignRole('Admin');
    }
}
