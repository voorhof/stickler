<?php

/** @noinspection PhpUnhandledExceptionInspection, PhpRedundantOptionalArgumentInspection */

use App\Actions\RichContent\CopyRichContentMedia;
use App\Actions\RichContent\SyncImageAltToMediaName;
use App\Models\Post;
use App\Models\User;
use App\Observers\PostObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function callSyncImageAltToMediaName(PostObserver $observer, Post $post, array $attributes = ['content']): void
{
    (function () use ($post, $attributes): void {
        $this->syncImageAltToMediaName($post, $attributes);
    })->call($observer);
}

function callCopyRichContentMedia(PostObserver $observer, Post $post, array $attributes = ['content']): void
{
    (function () use ($post, $attributes): void {
        $this->copyRichContentMedia($post, $attributes);
    })->call($observer);
}

test('it calls sync image alt to media name action when trait method is invoked', function () {
    User::factory()->create();

    $post = Post::factory()->create([
        'content' => '<p><img src="/storage/test.jpg" alt="Test Alt" data-id="0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b"></p>',
    ]);

    $mock = Mockery::mock(SyncImageAltToMediaName::class);
    $mock->shouldReceive('handle')
        ->once()
        ->withArgs(function ($model, $attributes) use ($post) {
            return $model->is($post) && $attributes === ['content'];
        });

    app()->instance(SyncImageAltToMediaName::class, $mock);

    callSyncImageAltToMediaName(new PostObserver, $post, ['content']);
});

test('it calls copy rich content media action when trait method is invoked', function () {
    User::factory()->create();

    $post = Post::factory()->create([
        'content' => '<p><img src="/storage/test.jpg" alt="Test Alt" data-id="0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b"></p>',
    ]);

    $mock = Mockery::mock(CopyRichContentMedia::class);
    $mock->shouldReceive('handle')
        ->once()
        ->withArgs(function ($model, $attributes) use ($post) {
            return $model->is($post) && $attributes === ['content'];
        });

    app()->instance(CopyRichContentMedia::class, $mock);

    callCopyRichContentMedia(new PostObserver, $post, ['content']);
});
