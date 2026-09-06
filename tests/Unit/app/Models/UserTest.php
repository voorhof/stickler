<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;

uses(RefreshDatabase::class);

test('a user can be created', function () {
    // All required fields are filled
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com');
});

test('a user can be mass assigned fillable values', function () {
    // All fillable fields are filled
    $user = new User([
        'slug' => 'john-doe',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'locale' => 'en_US',
        'password' => 'password',
        'order_column' => 1,
    ]);

    expect($user->slug)->toBe('john-doe')
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and($user->locale)->toBe('en_US')
        ->and($user->password)->not()->toBeNull()
        ->and($user->order_column)->toBe(1);
});

test('it generates a slug from name', function () {
    $user = User::forceCreate([
        'name' => 'John Doe',
        'email' => 'john.slug@example.com',
        'password' => 'password',
        'created_by_user_id' => 1,
        'updated_by_user_id' => 1,
    ]);

    expect($user->slug)->toBe('john-doe');
});

test('it does not regenerate a slug when updating name', function () {
    $user = User::forceCreate([
        'name' => 'John Doe',
        'email' => 'john.slug@example.com',
        'password' => 'password',
        'created_by_user_id' => 1,
        'updated_by_user_id' => 1,
    ]);
    $user->update(['name' => 'Jane']);

    expect($user->slug)->toBe('john-doe');
});

test('it generates unique slugs for users with the same name', function () {
    $firstUser = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john.doe.1@example.com',
        'password' => 'password',
        'created_by_user_id' => 1,
        'updated_by_user_id' => 1,
    ]);

    $secondUser = User::factory()->create([
        'name' => 'John Doe',
        'slug' => 'john-doe-2',
        'email' => 'john.doe.2@example.com',
        'password' => 'password',
        'created_by_user_id' => 2,
        'updated_by_user_id' => 2,
    ]);

    expect($secondUser->slug)
        ->not->toBe($firstUser->slug)
        ->and(Str::startsWith($secondUser->slug, 'john-doe'))->toBeTrue();
});

test('it uses slug as route key name', function () {
    expect((new User)->getRouteKeyName())->toBe('slug');
});

test('it can be reordered', function () {
    $user1 = User::factory()->create(['order_column' => 1]);
    $user2 = User::factory()->create(['order_column' => 2]);

    User::swapOrder($user1, $user2);

    expect($user1->fresh()->order_column)->toBe(2)
        ->and($user2->fresh()->order_column)->toBe(1);
});

test('it defers generating the preview conversion', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sourcePath = storage_path('test.pdf');
    $path = storage_path('app/testing/user-preview-test.pdf');

    copy($sourcePath, $path);

    $user->addMedia($path)
        ->toMediaCollection('documents');

    $media = $user->getFirstMedia('documents');

    expect($media->hasGeneratedConversion('preview'))->toBeFalse();
});

test('it generates a preview conversion for PDF documents when deferred callbacks run', function () {
    $this->withoutDefer(); // This is required to ensure that the deferred callbacks are run.

    $user = User::factory()->create();
    $this->actingAs($user);

    $sourcePath = storage_path('test.pdf');
    $path = storage_path('app/testing/user-preview-test.pdf');

    expect(file_exists($sourcePath))->toBeTrue('The PDF fixture does not exist at storage/test.pdf.');

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    copy($sourcePath, $path);

    $user->addMedia($path)
        ->toMediaCollection('documents');

    $media = $user->getFirstMedia('documents');

    expect($media->hasGeneratedConversion('preview'))->toBeTrue();
});

test('it automatically reorders others when order_column is manually updated', function () {
    $user1 = User::factory()->create(['order_column' => 1]);
    $user2 = User::factory()->create(['order_column' => 2]);
    $user3 = User::factory()->create(['order_column' => 3]);

    $user1->update(['order_column' => 3]);

    expect($user1->fresh()->order_column)->toBe(3)
        ->and($user2->fresh()->order_column)->toBe(1)
        ->and($user3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $user1 = User::factory()->create(['order_column' => 1]);
    $user2 = User::factory()->create(['order_column' => 2]);

    $newUser = User::forceCreate([
        'name' => 'John Doe',
        'email' => 'john.slug@example.com',
        'password' => 'password',
        'order_column' => 1,
        'created_by_user_id' => 1,
        'updated_by_user_id' => 1,
    ]);

    expect($newUser->fresh()->order_column)->toBe(1)
        ->and($user1->fresh()->order_column)->toBe(2)
        ->and($user2->fresh()->order_column)->toBe(3);
});

test('it does reorder when creating a new model without specific order_column', function () {
    $user1 = User::factory()->create(['order_column' => 1]);
    $user2 = User::factory()->create(['order_column' => 2]);

    $newUser = User::forceCreate([
        'name' => 'John Doe',
        'email' => 'john.slug@example.com',
        'password' => 'password',
        // order_column will be 1 (because of SortableOnUpdate)
        'created_by_user_id' => 1,
        'updated_by_user_id' => 1,
    ]);

    expect($newUser->fresh()->order_column)->toBe(1)
        ->and($user1->fresh()->order_column)->toBe(2)
        ->and($user2->fresh()->order_column)->toBe(3);
});

test('it applies name mutators and accessors', function () {
    $user = User::factory()->create([
        'name' => 'JOHN DOE',
        'email' => 'john.mutator@example.com',
        'password' => 'password',
        'created_by_user_id' => 1,
        'updated_by_user_id' => 1,
    ]);

    expect($user->getRawOriginal('name'))->toBe('john doe')
        ->and($user->name)->toBe('John Doe')
        ->and($user->getFilamentName())->toBe('John Doe');
});

test('it has default locale when none is provided', function () {
    $user = User::factory()->create([
        'name' => 'Locale',
        'email' => 'default.locale@example.com',
        'password' => 'password',
        'locale' => app()->getLocale(),
    ]);

    expect($user->locale)->toBe('nl_BE');
});

test('it hashes the password through casts', function () {
    $user = new User([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'my-secret-password',
    ]);

    expect($user->password)->not->toBe('my-secret-password')
        ->and(Hash::check('my-secret-password', $user->password))->toBeTrue();
});

test('it hides sensitive attributes from serialization', function () {
    $user = User::factory()->create([
        'password' => 'my-secret-password',
        'remember_token' => 'remember-token',
    ]);

    $array = $user->toArray();

    expect($array)
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

test('it can be soft deleted', function () {
    $user = User::factory()->create();

    $user->delete();

    $this->assertSoftDeleted(User::class, ['id' => $user->id]);
});

test('it can be restored after soft delete', function () {
    $user = User::factory()->create(['deleted_at' => now()]);

    $user->restore();

    $this->assertNotSoftDeleted(User::class, ['id' => $user->id]);
});

test('email_verified_at is cast to a datetime instance', function () {
    $user = User::factory()->create(['email_verified_at' => '2024-06-01 10:00:00']);

    expect($user->email_verified_at)->toBeInstanceOf(Carbon::class);
});

test('it can access the admin panel when it has the access admin permission', function () {
    $panel = Filament::getPanel('admin'); // @phpstan-ignore-line

    $user = User::factory()->create();
    expect($user->canAccessPanel($panel))->toBeFalse();

    Permission::create(['name' => 'access admin']);
    $user->givePermissionTo('access admin');
    expect($user->canAccessPanel($panel))->toBeTrue();
});

test('it has many created posts', function () {
    $user = User::factory()->create();
    Post::factory()->count(2)->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $posts = Post::where('created_by_user_id', $user->id)->get();

    expect($posts)->toHaveCount(2)
        ->and($posts->first())->toBeInstanceOf(Post::class);
});

test('it has many updated posts', function () {
    $user = User::factory()->create();
    Post::factory()->count(3)->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $posts = Post::where('updated_by_user_id', $user->id)->get();

    expect($posts)->toHaveCount(3)
        ->and($posts->first())->toBeInstanceOf(Post::class);
});

test('it returns name as title attribute', function () {
    $user = User::factory()->create(['name' => 'John Title']);

    expect($user->title)->toBe('John Title');
});

test('it returns preferred locale matching user locale', function () {
    $user = User::factory()->create(['locale' => 'en_US']);

    expect($user->preferredLocale())->toBe('en_US');
});

test('it retrieves role names for the user', function () {
    $user = User::factory()->create();
    $role1 = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $user->assignRole($role1, $role2);

    expect($user->getRoleNames())->toContain('admin', 'editor');
});

test('it belongs to creator and updater users', function () {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $user = User::factory()->create([
        'created_by_user_id' => $creator->id,
        'updated_by_user_id' => $updater->id,
    ]);

    expect($user->creator->id)->toBe($creator->id)
        ->and($user->updater->id)->toBe($updater->id);
});

test('it returns default user instance when creator or updater is not loaded', function () {
    $user = User::factory()->create([
        'created_by_user_id' => null,
        'updated_by_user_id' => null,
    ]);

    expect($user->creator)->toBeInstanceOf(User::class)
        ->and($user->creator->name)->toBe('Guest User')
        ->and($user->updater)->toBeInstanceOf(User::class)
        ->and($user->updater->name)->toBe('Guest User');
});

test('it has relations for created and updated media', function () {
    $user = User::factory()->create();

    expect($user->createdMedia())->toBeInstanceOf(HasMany::class)
        ->and($user->updatedMedia())->toBeInstanceOf(HasMany::class);
});

test('it has relations for created and updated messages', function () {
    $user = User::factory()->create();

    expect($user->createdMessages())->toBeInstanceOf(HasMany::class)
        ->and($user->updatedMessages())->toBeInstanceOf(HasMany::class);
});

test('it has relations for created and updated projects', function () {
    $user = User::factory()->create();

    expect($user->createdProjects())->toBeInstanceOf(HasMany::class)
        ->and($user->updatedProjects())->toBeInstanceOf(HasMany::class);
});

test('it has relations for created and updated roles', function () {
    $user = User::factory()->create();

    expect($user->createdRoles())->toBeInstanceOf(HasMany::class)
        ->and($user->updatedRoles())->toBeInstanceOf(HasMany::class);
});

test('it has relations for created and updated tags', function () {
    $user = User::factory()->create();

    expect($user->createdTags())->toBeInstanceOf(HasMany::class)
        ->and($user->updatedTags())->toBeInstanceOf(HasMany::class);
});

test('it has relations for created and updated users', function () {
    $user = User::factory()->create();

    expect($user->createdUsers())->toBeInstanceOf(HasMany::class)
        ->and($user->updatedUsers())->toBeInstanceOf(HasMany::class);
});

test('it configures activity log options correctly', function () {
    $user = new User;
    $options = $user->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});
