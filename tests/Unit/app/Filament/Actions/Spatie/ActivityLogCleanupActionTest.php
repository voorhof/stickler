<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\Spatie\ActivityLogCleanupAction;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view activities',
        'delete activities',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = ActivityLogCleanupAction::make();
        expect($action->getName())->toBe('activityClean');
    });
});

describe('action execution', function () {
    it('can execute activity log cleanup action', function () {
        Activity::create([
            'log_name' => 'default',
            'description' => 'Old activity',
            'created_at' => now()->subDays(100),
        ]);
        Activity::create([
            'log_name' => 'default',
            'description' => 'New activity',
            'created_at' => now(),
        ]);

        livewire(ListActivities::class)
            ->callAction('activityClean', [
                'max_days' => 60,
                'log_name' => null,
            ])
            ->assertNotified()
            ->assertHasNoFormErrors();

        // Check that old activity was cleaned up
        expect(Activity::where('description', 'Old activity')->exists())->toBeFalse()
            ->and(Activity::where('description', 'New activity')->exists())->toBeTrue();
    });
});

describe('validation', function () {
    it('requires max_days', function () {
        livewire(ListActivities::class)
            ->callAction('activityClean', [
                'max_days' => null,
                'log_name' => null,
            ])
            ->assertHasFormErrors(['max_days' => 'required']);
    });

    it('validates max_days range', function () {
        livewire(ListActivities::class)
            ->callAction('activityClean', [
                'max_days' => 400,
                'log_name' => null,
            ])
            ->assertHasFormErrors(['max_days' => 'max']);
    });
});

describe('visibility', function () {
    it('hides the action for users without delete activities permission', function () {
        $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
        $restrictedRole->givePermissionTo(['access admin', 'view activities']);
        $restrictedUser = User::factory()->create();
        $restrictedUser->assignRole($restrictedRole);
        $this->actingAs($restrictedUser);

        livewire(ListActivities::class)
            ->assertActionHidden('activityClean');
    });
});
