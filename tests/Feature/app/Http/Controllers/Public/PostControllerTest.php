<?php

use App\Http\Controllers\Public\PostController;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Laravel\Head\Facades\Head;

describe('posts index', function () {
    test('it displays published posts in index ordered by order_column', function () {
        User::factory()->create();

        $publishedPost1 = Post::factory()->create([
            'title' => 'First Order Post',
            'published_at' => now()->subDays(2),
        ]);

        $publishedPost2 = Post::factory()->create([
            'title' => 'Second Order Post',
            'published_at' => now()->subDay(),
        ]);

        $futurePost = Post::factory()->create([
            'title' => 'Future Post',
            'published_at' => now()->addDay(),
        ]);

        $unpublishedPost = Post::factory()->create([
            'title' => 'Unpublished Post',
            'published_at' => null,
        ]);

        $publishedPost2->updateQuietly(['order_column' => 1]);
        $publishedPost1->updateQuietly(['order_column' => 2]);

        $response = $this->get(route('posts.index'));

        $response->assertSuccessful()
            ->assertViewIs('public.posts.index')
            ->assertViewHas('posts', function (LengthAwarePaginator $posts) use ($publishedPost1, $publishedPost2, $futurePost, $unpublishedPost) {
                expect($posts->pluck('id')->all())->toBe([$publishedPost2->id, $publishedPost1->id])
                    ->and($posts->contains($futurePost))->toBeFalse()
                    ->and($posts->contains($unpublishedPost))->toBeFalse()
                    ->and($posts->first()->relationLoaded('media'))->toBeTrue()
                    ->and($posts->perPage())->toBe(6);

                return true;
            })
            ->assertSeeText($publishedPost1->title)
            ->assertSeeText($publishedPost2->title)
            ->assertDontSeeText($futurePost->title)
            ->assertDontSeeText($unpublishedPost->title);
    });

    test('it sets head metadata on index', function () {
        $this->get(route('posts.index'));

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.e(__('Blogposts')).'</title>')
            ->and($html)->toContain(e(__('Blogposts')).' '.config('app.name'));
    });
});

describe('posts show', function () {
    test('it displays published post in show route', function () {
        User::factory()->create();

        $post = Post::factory()->create([
            'title' => 'About Us',
            'intro' => 'Welcome to our about post',
            'content' => 'Full content of about post',
            'published_at' => now()->subHour(),
        ]);

        $response = $this->get(route('posts.show', $post));

        $response->assertSuccessful()
            ->assertViewIs('public.posts.show')
            ->assertViewHas('post', function (Post $viewPost) use ($post) {
                return $viewPost->id === $post->id;
            })
            ->assertSeeText($post->title)
            ->assertSeeText($post->intro);

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.$post->title.'</title>')
            ->and($html)->toContain($post->intro);
    });

    test('it returns 404 when showing a post published in the future', function () {
        User::factory()->create();

        $futurePost = Post::factory()->create([
            'title' => 'Coming Soon',
            'published_at' => Carbon::now()->addDays(5),
        ]);

        $response = $this->get(route('posts.show', $futurePost));

        $response->assertNotFound();
    });

    test('it returns 404 when showing an unpublished post with null published_at', function () {
        User::factory()->create();

        $unpublishedPost = Post::factory()->create([
            'title' => 'Draft Post',
            'published_at' => null,
        ]);

        $response = $this->get(route('posts.show', $unpublishedPost));

        $response->assertNotFound();
    });

    test('it returns 404 when showing a nonexistent post', function () {
        $response = $this->get(route('posts.show', 'non-existent-post-slug'));

        $response->assertNotFound();
    });
});

describe('controller methods', function () {
    test('direct controller index method returns correct view and data', function () {
        User::factory()->create();

        $post = Post::factory()->create([
            'published_at' => now()->subDay(),
        ]);

        $controller = new PostController;
        $view = $controller->index();

        expect($view->name())->toBe('public.posts.index')
            ->and($view->getData())->toHaveKey('posts')
            ->and($view->getData()['posts'])->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($view->getData()['posts']->pluck('id')->all())->toContain($post->id);
    });

    test('direct controller show method returns view for published post', function () {
        User::factory()->create();

        $publishedPost = Post::factory()->create([
            'published_at' => now()->subDay(),
        ]);

        $controller = new PostController;
        $view = $controller->show($publishedPost);

        expect($view->name())->toBe('public.posts.show')
            ->and($view->getData()['post']->id)->toBe($publishedPost->id);

    });
});
