<?php

/** @noinspection PhpUnhandledExceptionInspection, PhpRedundantOptionalArgumentInspection */

use App\Actions\RichContent\CopyRichContentMedia;
use App\Actions\RichContent\SyncImageAltToMediaName;
use App\Models\Post;
use App\Models\User;
use App\Observers\PageObserver;
use App\Observers\PostObserver;
use App\Observers\ProjectObserver;

function callSyncImageAltToMediaName($observer, Post $post, array $attributes = ['content']): void
{
    (function () use ($post, $attributes): void {
        $this->syncImageAltToMediaName($post, $attributes);
    })->call($observer);
}

function callCopyRichContentMedia($observer, Post $post, array $attributes = ['content']): void
{
    (function () use ($post, $attributes): void {
        $this->copyRichContentMedia($post, $attributes);
    })->call($observer);
}

describe('SyncsRichContentImageAltText trait', function () {

    test('trait provides syncImageAltToMediaName method on observers', function () {
        $postObserver = new PostObserver;
        $pageObserver = new PageObserver;
        $projectObserver = new ProjectObserver;

        expect(method_exists($postObserver, 'syncImageAltToMediaName'))->toBeTrue()
            ->and(method_exists($pageObserver, 'syncImageAltToMediaName'))->toBeTrue()
            ->and(method_exists($projectObserver, 'syncImageAltToMediaName'))->toBeTrue();
    });

    test('trait provides copyRichContentMedia method on observers', function () {
        $postObserver = new PostObserver;
        $pageObserver = new PageObserver;
        $projectObserver = new ProjectObserver;

        expect(method_exists($postObserver, 'copyRichContentMedia'))->toBeTrue()
            ->and(method_exists($pageObserver, 'copyRichContentMedia'))->toBeTrue()
            ->and(method_exists($projectObserver, 'copyRichContentMedia'))->toBeTrue();
    });

    test('syncImageAltToMediaName delegates to the action', function () {
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

    test('copyRichContentMedia delegates to the action', function () {
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

    test('syncImageAltToMediaName uses content attribute by default', function () {
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

        (function ($post): void {
            $this->syncImageAltToMediaName($post);
        })->call(new PostObserver, $post);
    });
});
