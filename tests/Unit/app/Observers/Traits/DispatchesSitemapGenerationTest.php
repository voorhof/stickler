<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Jobs\GenerateSitemapJob;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

function createSitemapGenerationTestModel(string $modelClass, User $user): Model
{
    return $modelClass::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
}

it('queues sitemap generation when public content is saved', function (string $modelClass): void {
    Queue::fake([GenerateSitemapJob::class]);
    $user = User::factory()->create();

    createSitemapGenerationTestModel($modelClass, $user);

    Queue::assertPushed(GenerateSitemapJob::class);
})->with([
    'post' => [Post::class],
    'page' => [Page::class],
    'project' => [Project::class],
    'tag' => [Tag::class],
]);

it('queues sitemap generation when public content is deleted', function (string $modelClass): void {
    $user = User::factory()->create();
    $model = Model::withoutEvents(fn (): Model => createSitemapGenerationTestModel($modelClass, $user));
    Queue::fake([GenerateSitemapJob::class]);

    $model->delete();

    Queue::assertPushed(GenerateSitemapJob::class);
})->with([
    'post' => [Post::class],
    'page' => [Page::class],
    'project' => [Project::class],
    'tag' => [Tag::class],
]);
