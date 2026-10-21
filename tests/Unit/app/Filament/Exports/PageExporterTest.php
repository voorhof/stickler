<?php

use App\Filament\Exports\PageExporter;
use App\Models\Page;
use App\Models\User;

test('page exporter exports page successfully including rich content', function () {
    $user = User::factory()->create();

    $page = Page::factory()->create([
        'title' => 'Test Page for Export',
        'content' => '<p>This is test content.</p>',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $exportedData = PageExporter::test()->export($page);

    expect($exportedData[2])->toBe('Test Page for Export');
    expect($exportedData[4])->toContain('This is test content.');
});
