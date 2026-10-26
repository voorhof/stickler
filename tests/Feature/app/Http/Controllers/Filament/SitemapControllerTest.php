<?php

/** @noinspection PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection */

use App\Jobs\GenerateSitemapJob;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;

describe('sitemap generation', function () {
    test('it queues sitemap generation when authorized', function () {
        Queue::fake([GenerateSitemapJob::class]);
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

        $response = $this->post(route('filament.admin.sitemap.generate'));
        $response->assertStatus(302);

        Queue::assertPushed(GenerateSitemapJob::class);
    });

    test('it returns 403 when lacking permission to generate sitemap', function () {
        Queue::fake([GenerateSitemapJob::class]);

        Permission::firstOrCreate(['name' => 'access admin']);

        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo('access admin');
        $user->assignRole($adminRole);

        $this->actingAs($user);

        $response = $this->post(route('filament.admin.sitemap.generate'));
        $response->assertStatus(403);

        Queue::assertNotPushed(GenerateSitemapJob::class);
    });

    test('it rejects GET requests without queuing sitemap generation', function () {
        Queue::fake([GenerateSitemapJob::class]);

        $this->get(route('filament.admin.sitemap.generate'))
            ->assertStatus(405);

        Queue::assertNotPushed(GenerateSitemapJob::class);
    });

    test('it redirects back, queues generation, and sends a database notification', function () {
        Queue::fake([GenerateSitemapJob::class]);
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

        $response = $this->from(route('filament.admin.pages.dashboard'))
            ->post(route('filament.admin.sitemap.generate'));

        $response->assertRedirect(route('filament.admin.pages.dashboard'));
        Queue::assertPushed(GenerateSitemapJob::class);

        $notification = $adminUser->notifications()->first();

        expect(Activity::where('description', 'Sitemap generated')->where('event', 'updated')->exists())->toBeFalse()
            ->and($notification->data['title'])->toBe(__('Ok! Sitemap generation queued!'));
    });
});
