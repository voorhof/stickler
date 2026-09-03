<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Pages\Auth\EditProfile;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create([
        'password' => 'password',
        'locale' => 'nl_BE',
    ]);

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo(Permission::create(['name' => 'access admin']));

    $this->adminUser->assignRole($adminRole);

    app()->setLocale('en_US');

    $this->actingAs($this->adminUser);
});

it('can load the profile page', function () {
    get('/admin/profile')
        ->assertSuccessful()
        ->assertSeeLivewire(EditProfile::class);
});

it('shows the dutch avatar upload placeholder on the profile page', function () {
    app()->setLocale('nl_BE');

    get('/admin/profile')
        ->assertSuccessful()
        ->assertSee('Sleep je afbeelding hierheen of', false);
});

it('aliases nl_BE to nl for file upload localization in filament', function () {
    app()->setLocale('nl_BE');

    get('/admin/profile')
        ->assertSuccessful()
        ->assertSee("nl_be: 'nl'", false)
        ->assertSee("locale.replace('-', '_').toLowerCase()", false)
        ->assertSee("name === 'fileUploadFormComponent'", false)
        ->assertSee('window.Alpine', false)
        ->assertSee('window.Alpine.data', false);
});

it('can update profile names', function () {
    livewire(EditProfile::class)
        ->fillForm([
            'name' => 'UpdatedLast',
            'email' => $this->adminUser->email,
            'password' => null,
            'passwordConfirmation' => null,
            'currentPassword' => 'password',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(User::class, [
        'id' => $this->adminUser->id,
        'name' => 'updatedlast',
    ]);
});

it('validates name requirements on profile update', function () {
    livewire(EditProfile::class)
        ->fillForm([
            'name' => null,
            'email' => $this->adminUser->email,
            'password' => null,
            'passwordConfirmation' => null,
            'currentPassword' => 'password',
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
        ]);
});

it('can update password from the profile page', function () {
    livewire(EditProfile::class)
        ->fillForm([
            'name' => $this->adminUser->name,
            'email' => $this->adminUser->email,
            'password' => 'new-password',
            'passwordConfirmation' => 'new-password',
            'currentPassword' => 'password',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(Hash::check('new-password', $this->adminUser->fresh()->password))->toBeTrue();
});

it('can upload an avatar from the profile page', function () {
    Storage::fake(config('media-library.disk_name'));

    $avatar = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(300);

    livewire(EditProfile::class)
        ->fillForm([
            'name' => $this->adminUser->name,
            'email' => $this->adminUser->email,
            'avatar' => $avatar,
            'password' => null,
            'passwordConfirmation' => null,
            'currentPassword' => 'password',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect($this->adminUser->fresh()->getFirstMedia('avatar'))->not()->toBeNull();
});

it('rejects non-image avatar uploads on the profile page', function () {
    Storage::fake(config('media-library.disk_name'));

    $invalidAvatar = UploadedFile::fake()->create('avatar.pdf', 200, 'application/pdf');

    livewire(EditProfile::class)
        ->fillForm([
            'name' => $this->adminUser->name,
            'email' => $this->adminUser->email,
            'avatar' => $invalidAvatar,
            'password' => null,
            'passwordConfirmation' => null,
            'currentPassword' => 'password',
        ])
        ->call('save')
        ->assertHasFormErrors(['avatar']);
});

it('logs activity when profile is updated', function () {
    livewire(EditProfile::class)
        ->fillForm([
            'name' => 'New Name',
            'email' => $this->adminUser->email,
            'password' => null,
            'passwordConfirmation' => null,
            'currentPassword' => 'password',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->adminUser->refresh();

    expect($this->adminUser->name)->toBe('New Name');

    // Check if an activity log was created for the user
    $activities = Activity::where('subject_type', User::class)
        ->where('subject_id', $this->adminUser->id)
        ->get();

    // dump($activities->toArray());

    $updateActivity = $activities->where('event', 'updated')->last();

    expect($updateActivity)->not->toBeNull()
        ->and($updateActivity->event)->toBe('updated')
        ->and($updateActivity->description)->toContain('updated');
});
