<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Exports\PostExporter;
use App\Models\Post;
use App\Models\User;

describe('exporting posts', function () {
    test('post exporter exports post successfully including rich content', function () {
        $user = User::factory()->create();

        $post = Post::factory()->create([
            'title' => 'Test Post for Export',
            'content' => '<p>This is test content.</p>',
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]);

        $exportedData = PostExporter::test()->export($post);

        expect($exportedData[2])->toBe('Test Post for Export')
            ->and($exportedData[4])->toContain('This is test content.');
    });
});
