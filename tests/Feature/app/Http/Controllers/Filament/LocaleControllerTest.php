<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Http\Controllers\Filament\LocaleController;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    Permission::firstOrCreate(['name' => 'access admin']);
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $adminRole->givePermissionTo('access admin');
});

test('it updates locale successfully when authenticated and locale is supported', function () {
    $user = User::factory()->create(['locale' => 'en_US']);
    $user->assignRole('Admin');
    $this->actingAs($user);

    $response = $this->get(route('filament.admin.locale.update', ['locale' => 'nl_BE']));

    $response->assertRedirect();
    expect($user->fresh()->locale)->toBe('nl_BE')
        ->and(app()->getLocale())->toBe('nl_BE');
});

test('it returns 404 when locale is unsupported', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin');
    $this->actingAs($user);

    $response = $this->get(route('filament.admin.locale.update', ['locale' => 'fr_FR']));

    $response->assertStatus(404);
});

test('it aborts 403 when unauthenticated in controller', function () {
    $request = Request::create('/admin/locale/nl_BE', 'GET');
    $controller = new LocaleController;

    $this->expectException(HttpException::class);

    $controller->update($request, 'nl_BE');
});
