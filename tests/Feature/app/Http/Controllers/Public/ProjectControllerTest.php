<?php

use App\Http\Controllers\Public\ProjectController;
use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Laravel\Head\Facades\Head;

describe('projects index', function () {
    test('it displays published projects in index ordered by order_column', function () {
        User::factory()->create();

        $publishedProject1 = Project::factory()->create([
            'title' => 'First Order Project',
            'published_at' => now()->subDays(2),
        ]);

        $publishedProject2 = Project::factory()->create([
            'title' => 'Second Order Project',
            'published_at' => now()->subDay(),
        ]);

        $futureProject = Project::factory()->create([
            'title' => 'Future Project',
            'published_at' => now()->addDay(),
        ]);

        $unpublishedProject = Project::factory()->create([
            'title' => 'Unpublished Project',
            'published_at' => null,
        ]);

        $publishedProject2->updateQuietly(['order_column' => 1]);
        $publishedProject1->updateQuietly(['order_column' => 2]);

        $response = $this->get(route('projects.index'));

        $response->assertSuccessful()
            ->assertViewIs('public.projects.index')
            ->assertViewHas('projects', function (LengthAwarePaginator $projects) use ($publishedProject1, $publishedProject2, $futureProject, $unpublishedProject) {
                expect($projects->pluck('id')->all())->toBe([$publishedProject2->id, $publishedProject1->id])
                    ->and($projects->contains($futureProject))->toBeFalse()
                    ->and($projects->contains($unpublishedProject))->toBeFalse()
                    ->and($projects->first()->relationLoaded('media'))->toBeTrue()
                    ->and($projects->perPage())->toBe(6);

                return true;
            })
            ->assertSeeText($publishedProject1->title)
            ->assertSeeText($publishedProject2->title)
            ->assertDontSeeText($futureProject->title)
            ->assertDontSeeText($unpublishedProject->title);
    });

    test('it sets head metadata on index', function () {
        $this->get(route('projects.index'));

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.e(__('Projects')).'</title>')
            ->and($html)->toContain(e(__('Projects')).' '.config('app.name'));
    });
});

describe('projects show', function () {
    test('it displays published project in show route', function () {
        User::factory()->create();

        $project = Project::factory()->create([
            'title' => 'About Us',
            'intro' => 'Welcome to our about project',
            'content' => 'Full content of about project',
            'published_at' => now()->subHour(),
        ]);

        $response = $this->get(route('projects.show', $project));

        $response->assertSuccessful()
            ->assertViewIs('public.projects.show')
            ->assertViewHas('project', function (Project $viewProject) use ($project) {
                return $viewProject->id === $project->id;
            })
            ->assertSeeText($project->title)
            ->assertSeeText($project->intro);

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.$project->title.'</title>')
            ->and($html)->toContain($project->intro);
    });

    test('it returns 404 when showing a project published in the future', function () {
        User::factory()->create();

        $futureProject = Project::factory()->create([
            'title' => 'Coming Soon',
            'published_at' => Carbon::now()->addDays(5),
        ]);

        $response = $this->get(route('projects.show', $futureProject));

        $response->assertNotFound();
    });

    test('it returns 404 when showing an unpublished project with null published_at', function () {
        User::factory()->create();

        $unpublishedProject = Project::factory()->create([
            'title' => 'Draft Project',
            'published_at' => null,
        ]);

        $response = $this->get(route('projects.show', $unpublishedProject));

        $response->assertNotFound();
    });

    test('it returns 404 when showing a nonexistent project', function () {
        $response = $this->get(route('projects.show', 'non-existent-project-slug'));

        $response->assertNotFound();
    });
});

describe('controller methods', function () {
    test('direct controller index method returns correct view and data', function () {
        User::factory()->create();

        $project = Project::factory()->create([
            'published_at' => now()->subDay(),
        ]);

        $controller = new ProjectController;
        $view = $controller->index();

        expect($view->name())->toBe('public.projects.index')
            ->and($view->getData())->toHaveKey('projects')
            ->and($view->getData()['projects'])->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($view->getData()['projects']->pluck('id')->all())->toContain($project->id);
    });

    test('direct controller show method returns view for published project', function () {
        User::factory()->create();

        $publishedProject = Project::factory()->create([
            'published_at' => now()->subDay(),
        ]);

        $controller = new ProjectController;
        $view = $controller->show($publishedProject);

        expect($view->name())->toBe('public.projects.show')
            ->and($view->getData()['project']->id)->toBe($publishedProject->id);

    });
});
