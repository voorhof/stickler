<?php

use App\Filament\Actions\Spatie\MediaLibraryRegenerateAction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view media',
        'update media',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has the correct default name', function () {
    $action = MediaLibraryRegenerateAction::make();
    expect($action->getName())->toBe('mediaGenerate');
});

it('can call the mediaGenerate action', function () {
    Artisan::shouldReceive('call')->with('media-library:regenerate --with-responsive-images --force --queue-all')->once();
    Artisan::shouldReceive('output')->once()->andReturn('Result: Success');

    MediaLibraryRegenerateAction::make()->call();

    $notification = DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $this->adminUser->id)
        ->latest()
        ->first();

    expect($notification)->not->toBeNull();

    $data = json_decode($notification->data, true);

    expect($data['title'])->toBe(__('Regenerating media queued'))
        ->and($data['body'])->toBe(__('Result: Success'));
});
