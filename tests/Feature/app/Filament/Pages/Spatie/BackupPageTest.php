<?php

use App\Filament\Pages\Spatie\Backups;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.locale' => 'en_US']);
    config(['logging.channels.single.path' => storage_path('logs/testing.log')]);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    Cache::flush();

    $this->adminUser = User::factory()->create();

    Role::factory()->create(['name' => 'No Admin']);

    $permissions = collect([
        'access admin',
        'view backups',
        'create backups',
        'download backups',
        'delete backups',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);
});

it('can load the backup page for an authorized user', function () {
    $this->actingAs($this->adminUser)
        ->get('/admin/backups')
        ->assertSuccessful()
        ->assertSeeLivewire(Backups::class);
});

it('denies access to the backup page for users without permission', function () {
    $user = User::factory()->create();
    $user->assignRole('No Admin');

    $this->actingAs($user)
        ->get('/admin/backups')
        ->assertForbidden();
});

it('shows the create backup action for a user with the create backups permission', function () {
    $this->actingAs($this->adminUser);

    livewire(Backups::class)
        ->assertActionVisible('createBackup');
});

it('hides the create backup action for a user without the create backups permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view backups']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(Backups::class)
        ->assertActionHidden('createBackup');
});

it('opens the backup option modal when triggered', function () {
    $this->actingAs($this->adminUser);

    livewire(Backups::class)
        ->call('openOptionModal')
        ->assertDispatched('open-modal', id: 'backup-option');
});

it('creates a full backup and closes the modal', function () {
    $this->actingAs($this->adminUser);

    Artisan::shouldReceive('queue')
        ->once()
        ->with(BackupCommand::class, [
            '--only-db' => false,
            '--only-files' => false,
            '--filename' => null,
        ]);

    livewire(Backups::class)
        ->call('create', 'all')
        ->assertDispatched('close-modal', id: 'backup-option')
        ->assertNotified();
});

it('creates a database only backup with the expected artisan options', function () {
    $this->actingAs($this->adminUser);

    Artisan::shouldReceive('queue')
        ->once()
        ->with(BackupCommand::class, Mockery::on(function ($args) {
            return $args['--only-db'] === true
                && $args['--only-files'] === false
                && str_starts_with($args['--filename'], 'only-db-');
        }));

    livewire(Backups::class)
        ->call('create', 'only-db')
        ->assertNotified();
});

it('creates a files only backup with the expected artisan options', function () {
    $this->actingAs($this->adminUser);

    Artisan::shouldReceive('queue')
        ->once()
        ->with(BackupCommand::class, Mockery::on(function ($args) {
            return $args['--only-db'] === false
                && $args['--only-files'] === true
                && str_starts_with($args['--filename'], 'only-files-');
        }));

    livewire(Backups::class)
        ->call('create', 'only-files')
        ->assertNotified();
});

it('displays the backup destination status', function () {
    $this->actingAs($this->adminUser);

    Storage::fake('backups');

    $backupName = config('backup.backup.name');

    config(['backup.monitor_backups' => [
        [
            'name' => $backupName,
            'disks' => ['backups'],
            'health_checks' => [
                MaximumAgeInDays::class => 1,
                MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ]]);

    livewire(Backups::class)
        ->assertSee($backupName)
        ->assertSee('backups')
        ->assertSee(__('backups.no_backups_info'));
});

it('lists the backup files in the table', function () {
    $this->actingAs($this->adminUser);

    Storage::fake('backups');
    config(['backup.backup.destination.disks' => ['backups']]);

    $backupName = config('backup.backup.name');
    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    livewire(Backups::class)
        ->assertCanSeeTableRecords(['0'])
        ->assertSee("$backupName/backup.zip")
        ->assertSee('backups');
});

it('provides a disk filter with the configured destination disks as options', function () {
    $this->actingAs($this->adminUser);

    config(['backup.backup.destination.disks' => ['backups', 'local']]);

    livewire(Backups::class)
        ->assertTableFilterExists('disk')
        ->assertSee('Backups')
        ->assertSee('Local');
});

it('shows the download action for a user with the download backups permission', function () {
    $adminRole = Role::whereName('Admin')->first();
    $adminRole->givePermissionTo('download backups');

    $this->actingAs($this->adminUser);

    Storage::fake('backups');
    config(['backup.backup.destination.disks' => ['backups']]);

    $backupName = config('backup.backup.name');
    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    livewire(Backups::class)
        ->assertTableActionVisible('download', record: '0');
});

it('hides the download action for a user without the download backups permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view backups']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    Storage::fake('backups');
    config(['backup.backup.destination.disks' => ['backups']]);

    $backupName = config('backup.backup.name');
    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    livewire(Backups::class)
        ->assertTableActionHidden('download', record: '0');
});

it('shows the delete action for a user with the delete backups permission and deletes the backup', function () {
    $this->actingAs($this->adminUser);

    Storage::fake('backups');
    config(['backup.backup.destination.disks' => ['backups']]);

    $backupName = config('backup.backup.name');
    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    livewire(Backups::class)
        ->assertTableActionVisible('delete', record: '0')
        ->callTableAction('delete', record: '0')
        ->assertNotified();

    Storage::disk('backups')->assertMissing("$backupName/backup.zip");
});

it('hides the delete action for a user without the delete backups permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view backups']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    Storage::fake('backups');
    config(['backup.backup.destination.disks' => ['backups']]);

    $backupName = config('backup.backup.name');
    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    livewire(Backups::class)
        ->assertTableActionHidden('delete', record: '0');
});
