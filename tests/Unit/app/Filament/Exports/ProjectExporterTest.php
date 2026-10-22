<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Exports\ProjectExporter;
use App\Models\Project;
use App\Models\User;

describe('exporting projects', function () {
    test('project exporter exports project successfully including rich content', function () {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'title' => 'Test Project for Export',
            'content' => '<p>This is test content.</p>',
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]);

        $exportedData = ProjectExporter::test()->export($project);

        expect($exportedData[2])->toBe('Test Project for Export');
        expect($exportedData[4])->toContain('This is test content.');
    });
});
