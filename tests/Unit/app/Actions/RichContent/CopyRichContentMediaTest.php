<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection, PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection */

use App\Actions\RichContent\CopyRichContentMedia;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('it copies media referenced in rich content attributes and updates model attribute', function () {
    $user = User::factory()->create();
    $sourcePost = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $targetPost = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $file = UploadedFile::fake()->image('test.jpg');
    $media = $sourcePost->addMedia($file)->toMediaCollection('content');

    $uuid = $media->uuid;
    $targetPost->content = '<p><img src="'.$media->getUrl().'" alt="Test Image" data-id="'.$uuid.'"></p>';
    $targetPost->saveQuietly();

    (new CopyRichContentMedia)->handle($targetPost);

    expect(Media::count())->toBe(2);

    $newMedia = Media::where('model_type', $targetPost->getMorphClass())
        ->where('model_id', $targetPost->getKey())
        ->first();

    expect($newMedia)->not->toBeNull()
        ->and($newMedia->uuid)->not->toBe($uuid)
        ->and($targetPost->fresh()->content)->toContain($newMedia->uuid)
        ->and($targetPost->fresh()->content)->toContain($newMedia->getUrl());
});

test('it handles multiple attributes when copying rich content media', function () {
    $user = User::factory()->create();
    $sourcePost = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $targetPost = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $file1 = UploadedFile::fake()->image('intro.jpg');
    $media1 = $sourcePost->addMedia($file1)->toMediaCollection('content');

    $file2 = UploadedFile::fake()->image('content.jpg');
    $media2 = $sourcePost->addMedia($file2)->toMediaCollection('content');

    $targetPost->intro = '<p><img src="'.$media1->getUrl().'" alt="Intro Image" data-id="'.$media1->uuid.'"></p>';
    $targetPost->content = '<p><img src="'.$media2->getUrl().'" alt="Content Image" data-id="'.$media2->uuid.'"></p>';
    $targetPost->saveQuietly();

    (new CopyRichContentMedia)->handle($targetPost, ['intro', 'content']);

    expect(Media::count())->toBe(4); // 2 source + 2 target copies

    $newMediaIntro = Media::where('model_type', $targetPost->getMorphClass())
        ->where('model_id', $targetPost->getKey())
        ->where('file_name', 'intro.jpg')
        ->first();

    $newMediaContent = Media::where('model_type', $targetPost->getMorphClass())
        ->where('model_id', $targetPost->getKey())
        ->where('file_name', 'content.jpg')
        ->first();

    expect($newMediaIntro)->not->toBeNull()
        ->and($newMediaContent)->not->toBeNull()
        ->and($targetPost->fresh()->intro)->toContain($newMediaIntro->uuid)
        ->and($targetPost->fresh()->content)->toContain($newMediaContent->uuid);
});

test('it returns early if model does not implement HasMedia', function () {
    $nonMediaModel = new class extends Illuminate\Database\Eloquent\Model
    {
        protected $table = 'users';
    };

    $nonMediaModel->content = '<p>Some content</p>';

    // Should not throw or fail
    (new CopyRichContentMedia)->handle($nonMediaModel);

    expect($nonMediaModel->content)->toBe('<p>Some content</p>');
});

test('it does nothing if attributes are blank or non-string', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'content' => null,
        'intro' => '',
    ]);

    (new CopyRichContentMedia)->handle($post, ['content', 'intro']);

    expect($post->content)->toBeNull()
        ->and($post->intro)->toBe('');
});
