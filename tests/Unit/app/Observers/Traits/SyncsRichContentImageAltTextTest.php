<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\RichContent\CopyRichContentMedia;
use App\Actions\RichContent\SyncImageAltToMediaName;
use App\Models\Post;
use App\Models\User;
use App\Observers\Traits\SyncsRichContentImageAltText;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calls sync image alt to media name action when trait method is invoked', function () {
    $observer = new class
    {
        use SyncsRichContentImageAltText;

        public function publicSync($model, $attributes = ['content']): void
        {
            $this->syncImageAltToMediaName($model, $attributes);
        }
    };

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

    $observer->publicSync($post, ['content']);
});

test('it calls copy rich content media action when trait method is invoked', function () {
    $observer = new class
    {
        use SyncsRichContentImageAltText;

        public function publicCopy($model, $attributes = ['content']): void
        {
            $this->copyRichContentMedia($model, $attributes);
        }
    };

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

    $observer->publicCopy($post, ['content']);
});
