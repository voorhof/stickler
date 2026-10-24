<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Imports\ProjectImporter;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;

describe('importing projects', function () {
    test('project importer imports valid row with website fields', function () {
        ProjectImporter::test()
            ->import([
                'slug' => 'test-project',
                'title' => 'Test Project',
                'intro' => 'This is a test project intro.',
                'website_title' => 'Test Website',
                'website_url' => 'https://example.com',
            ])
            ->assertImported();

        $project = Project::where('slug', 'test-project')->first();
        expect($project->website_title)->toBe('Test Website')
            ->and($project->website_url)->toBe('https://example.com');
    });

    test('project importer fails validation with invalid website url', function () {
        ProjectImporter::test()
            ->import([
                'slug' => 'test-project',
                'title' => 'Test Project',
                'intro' => 'This is a test project intro.',
                'website_url' => 'not-a-url',
            ])
            ->assertHasErrors(['website_url' => 'url']);
    });
});

describe('user tracking', function () {
    test('project importer sets created by and updated by user id to the user executing the import', function () {
        $user = User::factory()->create();

        $import = new Import([
            'user_id' => $user->id,
        ]);

        ProjectImporter::test(import: $import)
            ->import([
                'slug' => 'test-project-user-id',
                'title' => 'Test Project User ID',
                'intro' => 'This is a test project intro.',
            ])
            ->assertImported();

        $project = Project::where('slug', 'test-project-user-id')->first();
        expect($project->created_by_user_id)->toBe($user->id)
            ->and($project->updated_by_user_id)->toBe($user->id);
    });
});
