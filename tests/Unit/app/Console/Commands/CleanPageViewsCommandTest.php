<?php

use App\Models\PageView;

it('deletes page views older than the given days and keeps recent ones', function () {
    PageView::factory()->create(['path' => '/old', 'viewed_on' => now()->subDays(100)->toDateString()]);
    PageView::factory()->create(['path' => '/recent', 'viewed_on' => now()->toDateString()]);

    $this->artisan('page-views:clean', ['--days' => 60, '--force' => true])
        ->expectsOutput('1 page views deleted.')
        ->assertSuccessful();

    expect(PageView::where('path', '/old')->exists())->toBeFalse()
        ->and(PageView::where('path', '/recent')->exists())->toBeTrue();
});

it('fails when the days option is out of range', function () {
    $this->artisan('page-views:clean', ['--days' => 400, '--force' => true])
        ->assertFailed();
});

it('does not delete anything when confirmation is declined', function () {
    PageView::factory()->create(['viewed_on' => now()->subDays(100)->toDateString()]);

    $this->artisan('page-views:clean', ['--days' => 60])
        ->expectsConfirmation('Delete all page views older than 60 days?', 'no')
        ->assertSuccessful();

    expect(PageView::count())->toBe(1);
});
