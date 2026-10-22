<?php

/** @noinspection PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection */

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;

describe('sitemap generation', function () {
    test('it generates sitemap successfully when authorized', function () {
        $sitemapGenerator = Mockery::mock(SitemapGenerator::class);
        $sitemapGenerator->shouldReceive('setUrl')->andReturnSelf();
        $sitemapGenerator->shouldReceive('getSitemap')->andReturn(new Sitemap);
        $this->app->instance(SitemapGenerator::class, $sitemapGenerator);

        Permission::firstOrCreate(['name' => 'edit settings']);
        Permission::firstOrCreate(['name' => 'access admin']);

        $adminUser = User::factory()->create();

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(['edit settings', 'access admin']);

        $adminUser->assignRole($adminRole);

        $this->actingAs($adminUser);

        $response = $this->get(route('filament.admin.sitemap.generate'));
        $response->assertStatus(302);
    });

    test('it returns 403 when lacking permission to generate sitemap', function () {
        Permission::firstOrCreate(['name' => 'access admin']);

        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo('access admin');
        $user->assignRole($adminRole);

        $this->actingAs($user);

        $response = $this->get(route('filament.admin.sitemap.generate'));
        $response->assertStatus(403);
    });
});
