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

        // Create ceo user
        $ceo = User::factory()->create([
            'slug' => Str::slug(config('stickler.admin_users.ceo.name')),
            'name' => config('stickler.admin_users.ceo.name'),
            'email' => config('stickler.admin_users.ceo.email'),
            'password' => Hash::make(config('stickler.admin_users.ceo.password')),
            'password_needs_update' => true,
            'terms_accepted' => true,
            'locale' => config('app.faker_locale'),
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'slug' => Str::slug(config('stickler.admin_users.admin.name')),
            'name' => config('stickler.admin_users.admin.name'),
            'email' => config('stickler.admin_users.admin.email'),
            'password' => Hash::make(config('stickler.admin_users.admin.password')),
            'password_needs_update' => true,
            'terms_accepted' => true,
            'locale' => config('app.faker_locale'),
        ]);

        // Create permissions
        Permission::create(['name' => 'access admin', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'access telescope', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'access health page', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'access settings', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'edit settings', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'access logs', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'download logs', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete logs', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create backups', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view backups', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'download backups', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete backups', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create users', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view users', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update users', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete users', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create roles', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view roles', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update roles', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete roles', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create media', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view media', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update media', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete media', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create pages', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view pages', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update pages', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete pages', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create posts', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view posts', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update posts', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete posts', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create products', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view products', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update products', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete products', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create projects', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view projects', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update projects', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete projects', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create categories', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view categories', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update categories', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete categories', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create orders', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view orders', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update orders', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete orders', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create carts', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view carts', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update carts', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete carts', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'create tags', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'view tags', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update tags', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete tags', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'view messages', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'update messages', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete messages', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'view activities', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete activities', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        Permission::create(['name' => 'view pageviews', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);
        Permission::create(['name' => 'delete pageviews', 'created_by_user_id' => $ceo->id, 'updated_by_user_id' => $ceo->id]);

        // Update the cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign created permissions
        Role::create([
            'name' => 'Admin',
            'order_column' => 1,
            'created_by_user_id' => $ceo->id,
            'updated_by_user_id' => $ceo->id,
        ])->givePermissionTo(Permission::all()); // All permissions for the Admin role

        // Assign the Admin role to the admin users
        $admin->assignRole('Admin');
        $ceo->assignRole('Admin');
    }
}
