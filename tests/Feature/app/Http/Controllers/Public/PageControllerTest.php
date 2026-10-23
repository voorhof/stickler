<?php

use App\Http\Controllers\Public\PageController;
use App\Models\Page;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Laravel\Head\Facades\Head;

describe('pages index', function () {
    test('it displays published pages in index ordered by order_column', function () {
        User::factory()->create();

        $publishedPage1 = Page::factory()->create([
            'title' => 'First Order Page',
            'published_at' => now()->subDays(2),
        ]);

        $publishedPage2 = Page::factory()->create([
            'title' => 'Second Order Page',
            'published_at' => now()->subDay(),
        ]);

        $futurePage = Page::factory()->create([
            'title' => 'Future Page',
            'published_at' => now()->addDay(),
        ]);

        $unpublishedPage = Page::factory()->create([
            'title' => 'Unpublished Page',
            'published_at' => null,
        ]);

        $publishedPage2->updateQuietly(['order_column' => 1]);
        $publishedPage1->updateQuietly(['order_column' => 2]);

        $response = $this->get(route('pages.index'));

        $response->assertSuccessful()
            ->assertViewIs('public.pages.index')
            ->assertViewHas('pages', function (LengthAwarePaginator $pages) use ($publishedPage1, $publishedPage2, $futurePage, $unpublishedPage) {
                expect($pages->pluck('id')->all())->toBe([$publishedPage2->id, $publishedPage1->id])
                    ->and($pages->contains($futurePage))->toBeFalse()
                    ->and($pages->contains($unpublishedPage))->toBeFalse()
                    ->and($pages->first()->relationLoaded('media'))->toBeTrue()
                    ->and($pages->perPage())->toBe(6);

                return true;
            })
            ->assertSeeText($publishedPage1->title)
            ->assertSeeText($publishedPage2->title)
            ->assertDontSeeText($futurePage->title)
            ->assertDontSeeText($unpublishedPage->title);
    });

    test('it sets head metadata on index', function () {
        $this->get(route('pages.index'));

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.e(__('Pages')).'</title>')
            ->and($html)->toContain(e(__('Pages')).' '.config('app.name'));
    });
});

describe('pages show', function () {
    test('it displays published page in show route', function () {
        User::factory()->create();

        $page = Page::factory()->create([
            'title' => 'About Us',
            'intro' => 'Welcome to our about page',
            'content' => 'Full content of about page',
            'published_at' => now()->subHour(),
        ]);

        $response = $this->get(route('pages.show', $page));

        $response->assertSuccessful()
            ->assertViewIs('public.pages.show')
            ->assertViewHas('page', function (Page $viewPage) use ($page) {
                return $viewPage->id === $page->id;
            })
            ->assertSeeText($page->title)
            ->assertSeeText($page->intro);

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.$page->title.'</title>')
            ->and($html)->toContain($page->intro);
    });

    test('it redirects to welcome index when showing a page published in the future', function () {
        User::factory()->create();

        $futurePage = Page::factory()->create([
            'title' => 'Coming Soon',
            'published_at' => Carbon::now()->addDays(5),
        ]);

        $response = $this->get(route('pages.show', $futurePage));

        $response->assertRedirect(route('welcome.index'));
    });

    test('it redirects to welcome index when showing an unpublished page with null published_at', function () {
        User::factory()->create();

        $unpublishedPage = Page::factory()->create([
            'title' => 'Draft Page',
            'published_at' => null,
        ]);

        $response = $this->get(route('pages.show', $unpublishedPage));

        $response->assertRedirect(route('welcome.index'));
    });

    test('it returns 404 when showing a nonexistent page', function () {
        $response = $this->get(route('pages.show', 'non-existent-page-slug'));

        $response->assertNotFound();
    });
});

describe('controller methods', function () {
    test('direct controller index method returns correct view and data', function () {
        User::factory()->create();

        $page = Page::factory()->create([
            'published_at' => now()->subDay(),
        ]);

        $controller = new PageController;
        $view = $controller->index();

        expect($view->name())->toBe('public.pages.index')
            ->and($view->getData())->toHaveKey('pages')
            ->and($view->getData()['pages'])->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($view->getData()['pages']->pluck('id')->all())->toContain($page->id);
    });

    test('direct controller show method returns view for published page or redirects for unpublished page', function () {
        User::factory()->create();

        $publishedPage = Page::factory()->create([
            'published_at' => now()->subDay(),
        ]);

        $controller = new PageController;
        $view = $controller->show($publishedPage);

        expect($view->name())->toBe('public.pages.show')
            ->and($view->getData()['page']->id)->toBe($publishedPage->id);

        $unpublishedPage = Page::factory()->create([
            'published_at' => null,
        ]);

        $redirectResponse = $controller->show($unpublishedPage);

        expect($redirectResponse->isRedirect(route('welcome.index')))->toBeTrue();
    });
});
