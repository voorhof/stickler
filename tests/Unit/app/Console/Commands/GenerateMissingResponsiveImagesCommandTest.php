<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob;

describe('command execution', function () {
    test('it outputs info message when no media items require responsive image generation', function () {
        $this->artisan('media:generate-missing-responsive-images')
            ->expectsOutput('No media records with missing responsive images found.')
            ->assertSuccessful();
    });

    test('it dispatches generate responsive images jobs synchronously by default', function () {
        $user = User::factory()->create();

        actingAs($user);

        $post = Post::factory()->create();

        $media = $post->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('image');

        // Simulate media item uploaded without responsive images
        $media->update(['responsive_images' => []]);

        $this->artisan('media:generate-missing-responsive-images')
            ->expectsOutput('Found 1 media items to process.')
            ->expectsOutput('Done!')
            ->assertSuccessful();

        $media->refresh();
        expect($media->responsive_images)->not->toBeEmpty();
    });

    test('it queues generate responsive images jobs when queue option is provided', function () {
        Queue::fake();

        $user = User::factory()->create();

        actingAs($user);

        $post = Post::factory()->create();

        $media = $post->addMedia(UploadedFile::fake()->image('test-queue.jpg'))
            ->toMediaCollection('image');

        $media->update(['responsive_images' => []]);

        $this->artisan('media:generate-missing-responsive-images --queue')
            ->expectsOutput('Found 1 media items to process.')
            ->expectsOutput('Done!')
            ->assertSuccessful();

        Queue::assertPushed(GenerateResponsiveImagesJob::class);
    });
});
