<?php

use App\Filament\Imports\PageImporter;
use App\Models\Page;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;

test('page importer imports valid row', function () {
    PageImporter::test()
        ->import([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'intro' => 'This is a test page intro.',
            'published_at' => '2026-10-21 12:00:00',
        ])
        ->assertImported();

    expect(Page::where('slug', 'test-page')->exists())->toBeTrue();
});

test('page importer fails validation with invalid date', function () {
    PageImporter::test()
        ->import([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'intro' => 'This is a test page intro.',
            'published_at' => 'not-a-date',
        ])
        ->assertHasErrors(['published_at' => 'date']);
});

test('page importer sets created by and updated by user id to the user executing the import', function () {
    $user = User::factory()->create();

    $import = new Import([
        'user_id' => $user->id,
    ]);

    PageImporter::test(import: $import)
        ->import([
            'slug' => 'test-page-user-id',
            'title' => 'Test Page User ID',
            'intro' => 'This is a test page intro.',
        ])
        ->assertImported();

    $page = Page::where('slug', 'test-page-user-id')->first();
    expect($page->created_by_user_id)->toBe($user->id)
        ->and($page->updated_by_user_id)->toBe($user->id);
});
