<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Support\RichContent\RichContentImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('it returns empty array for non-string or blank input', function () {
    expect(RichContentImages::altByUuid(null))->toBe([])
        ->and(RichContentImages::altByUuid(''))->toBe([])
        ->and(RichContentImages::altByUuid('   '))->toBe([]);
});

test('it returns empty array when html has no images', function () {
    $html = '<p>Hello world without images.</p>';
    expect(RichContentImages::altByUuid($html))->toBe([]);
});

test('it extracts alt text by uuid from img tags', function () {
    $uuid1 = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';
    $uuid2 = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6c';

    $html = '<p><img src="/storage/1.jpg" alt="First Image" data-id="'.$uuid1.'"></p>'
            .'<div><img src="/storage/2.jpg" alt="Second Image" data-id="'.$uuid2.'"></div>';

    $result = RichContentImages::altByUuid($html);

    expect($result)->toBe([
        $uuid1 => 'First Image',
        $uuid2 => 'Second Image',
    ]);
});

test('it skips images with non-uuid data-id', function () {
    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $html = '<p><img src="/storage/1.jpg" alt="Valid Image" data-id="'.$uuid.'"></p>'
            .'<p><img src="/storage/2.jpg" alt="Unsaved File" data-id="attachments/foo.jpg"></p>';

    $result = RichContentImages::altByUuid($html);

    expect($result)->toBe([
        $uuid => 'Valid Image',
    ]);
});

test('it skips images without alt or with blank alt', function () {
    $uuid1 = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';
    $uuid2 = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6c';

    $html = '<p><img src="/storage/test.jpg" data-id="'.$uuid1.'"></p>'
            .'<p><img src="/storage/test.jpg" alt="   " data-id="'.$uuid2.'"></p>';

    $result = RichContentImages::altByUuid($html);

    expect($result)->toBe([]);
});

test('removeImageByUuid returns original input for non-string, blank input or blank uuid', function () {
    expect(RichContentImages::removeImageByUuid(null, 'some-uuid'))->toBeNull()
        ->and(RichContentImages::removeImageByUuid('', 'some-uuid'))->toBe('')
        ->and(RichContentImages::removeImageByUuid('<p>Hello</p>', ''))->toBe('<p>Hello</p>');
});

test('removeImageByUuid returns original html when image with uuid is not found', function () {
    $html = '<p><img src="/storage/test.jpg" alt="Image" data-id="uuid-1"></p>';
    $uuid = 'uuid-2';

    expect(RichContentImages::removeImageByUuid($html, $uuid))->toBe($html);
});

test('removeImageByUuid removes the matching image tag and keeps other content intact', function () {
    $uuid1 = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';
    $uuid2 = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6c';

    $html = '<p><img src="/storage/1.jpg" alt="First" data-id="'.$uuid1.'"></p>'
        .'<p><span style="font-weight: bold;">Some text</span><img src="/storage/2.jpg" alt="Second" data-id="'.$uuid2.'"></p>';

    $result = RichContentImages::removeImageByUuid($html, $uuid1);

    expect($result)->not->toContain($uuid1)
        ->and($result)->toContain($uuid2)
        ->and($result)->toContain('Some text');
});

test('removeImageByUuid removes empty wrapping paragraph when image is removed', function () {
    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $html = '<p>Before</p><p><img src="/storage/1.jpg" alt="Image" data-id="'.$uuid.'"></p><p>After</p>';

    $result = RichContentImages::removeImageByUuid($html, $uuid);

    expect($result)->toBe('<p>Before</p><p>After</p>');
});

test('copyMediaForModel returns original input for non-string, blank input or non-hasmedia model', function () {
    $post = new Post;
    expect(RichContentImages::copyMediaForModel(null, $post))->toBeNull()
        ->and(RichContentImages::copyMediaForModel('', $post))->toBe('')
        ->and(RichContentImages::copyMediaForModel('<p>Hello</p>', new stdClass))->toBe('<p>Hello</p>');
});

test('copyMediaForModel returns original html when media uuid is not found or not a uuid', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $html = '<p><img src="/storage/test.jpg" alt="Image" data-id="attachments/foo.jpg"></p>'
          .'<p><img src="/storage/test.jpg" alt="Image" data-id="0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b"></p>';

    expect(RichContentImages::copyMediaForModel($html, $post))->toBe($html);
});

test('copyMediaForModel skips copying if media already belongs to the given model', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $file = UploadedFile::fake()->image('test.jpg');
    $media = $post->addMedia($file)->toMediaCollection('content');

    $uuid = $media->uuid;
    $html = '<p><img src="'.$media->getUrl().'" alt="Image" data-id="'.$uuid.'"></p>';

    $result = RichContentImages::copyMediaForModel($html, $post);

    expect($result)->toBe($html)
        ->and(Media::count())->toBe(1);
});

test('copyMediaForModel copies media belonging to another model and updates uuid and src', function () {
    $user = User::factory()->create();
    $sourcePost = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $targetPost = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $file = UploadedFile::fake()->image('source.jpg');
    $media = $sourcePost->addMedia($file)->toMediaCollection('content');

    $uuid = $media->uuid;
    $oldSrc = $media->getUrl();
    $html = '<p><img src="'.$oldSrc.'" alt="Copied Image" data-id="'.$uuid.'"></p>';

    $result = RichContentImages::copyMediaForModel($html, $targetPost);

    expect(Media::count())->toBe(2);

    $newMedia = Media::where('model_type', $targetPost->getMorphClass())
        ->where('model_id', $targetPost->getKey())
        ->first();

    expect($newMedia)->not->toBeNull()
        ->and($newMedia->uuid)->not->toBe($uuid)
        ->and($result)->toContain($newMedia->uuid)
        ->and($result)->toContain($newMedia->getUrl());
});
