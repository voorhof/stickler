<?php

/** @noinspection PhpUndefinedFieldInspection */

use App\Models\Media;
use Spatie\Activitylog\Support\LogOptions;
use App\Models\User;
use App\Policies\MediaPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

uses(RefreshDatabase::class);

test('media model extends spatie base media model', function () {
    expect(new Media)->toBeInstanceOf(BaseMedia::class);
});

test('media model uses the media table', function () {
    expect((new Media)->getTable())->toBe('media');
});

test('media model is configured as the media library media model', function () {
    expect(config('media-library.media_model'))->toBe(Media::class);
});

test('media model has media policy attribute configured', function () {
    $attributes = new ReflectionClass(Media::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(MediaPolicy::class);
});

test('a media record can be created', function () {
    $user = User::factory()->create();

    actingAs($user);

    $media = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Profile Avatar',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->exists)->toBeTrue()
        ->and($media->collection_name)->toBe('avatar')
        ->and($media->file_name)->toBe('avatar.jpg');
});

test('media model belongs to an owning model through morph relation', function () {
    $user = User::factory()->create();

    actingAs($user);

    $media = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->model())->toBeInstanceOf(MorphTo::class)
        ->and($media->model)->toBeInstanceOf(User::class)
        ->and($media->model->is($user))->toBeTrue();
});

test('it can be reordered', function () {
    $user = User::factory()->create();

    actingAs($user);

    $media1 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 1',
        'file_name' => 'avatar1.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media2 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 2',
        'file_name' => 'avatar2.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 2,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    Media::swapOrder($media1, $media2);

    expect($media1->fresh()->order_column)->toBe(2)
        ->and($media2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    $user = User::factory()->create();

    actingAs($user);

    $media1 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 1',
        'file_name' => 'avatar1.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media2 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 2',
        'file_name' => 'avatar2.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 2,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media3 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 3',
        'file_name' => 'avatar3.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 3,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media1->update(['order_column' => 3]);

    expect($media1->fresh()->order_column)->toBe(3)
        ->and($media2->fresh()->order_column)->toBe(1)
        ->and($media3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $user = User::factory()->create();

    actingAs($user);

    $media1 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 1',
        'file_name' => 'avatar1.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media2 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 2',
        'file_name' => 'avatar2.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 2,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $newMedia = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 3',
        'file_name' => 'avatar3.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($newMedia->fresh()->order_column)->toBe(1)
        ->and($media1->fresh()->order_column)->toBe(2)
        ->and($media2->fresh()->order_column)->toBe(3);
});

test('it does reorder when creating a new model without specific order_column', function () {
    $user = User::factory()->create();

    actingAs($user);

    $media1 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 1',
        'file_name' => 'avatar1.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media2 = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 2',
        'file_name' => 'avatar2.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'order_column' => 2,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $newMedia = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar 3',
        'file_name' => 'avatar3.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        // 'order_column' => 3,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($newMedia->fresh()->order_column)->toBe(1)
        ->and($media1->fresh()->order_column)->toBe(2)
        ->and($media2->fresh()->order_column)->toBe(3);
});

test('it returns name as title attribute', function () {
    $user = User::factory()->create();
    actingAs($user);

    $media = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar Title Test',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->title)->toBe('Avatar Title Test');
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    actingAs($user);

    $media = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'created_by_user_id' => $user->id,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->creator)->toBeInstanceOf(User::class)
        ->and($media->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    actingAs($user);

    $media = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Avatar',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'updated_by_user_id' => $user->id,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->updater)->toBeInstanceOf(User::class)
        ->and($media->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $media = new Media;

    expect($media->creator)->toBeInstanceOf(User::class)
        ->and($media->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $media = new Media;

    expect($media->updater)->toBeInstanceOf(User::class)
        ->and($media->updater->name)->toBe('Guest User');
});

test('it can be deleted', function () {
    $user = User::factory()->create();
    actingAs($user);

    $media = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'To Delete',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $mediaId = $media->id;
    $media->delete();

    expect(Media::find($mediaId))->toBeNull();
});

test('it configures activity log options correctly', function () {
    $media = new Media;
    $options = $media->getActivitylogOptions();

    expect($options)->toBeInstanceOf(Spatie\Activitylog\Support\LogOptions::class);
});
