<?php

use App\Filament\Exports\UserExporter;
use App\Models\User;

test('user exporter exports user successfully', function () {
    $user = User::factory()->create([
        'name' => 'Test User For Export',
        'email' => 'export-user@example.com',
    ]);

    $exportedData = UserExporter::test()->export($user);

    expect($exportedData[2])->toBe('Test User For Export')
        ->and($exportedData[3])->toBe('export-user@example.com');
});
