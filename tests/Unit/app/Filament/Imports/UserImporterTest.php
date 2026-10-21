<?php

use App\Filament\Imports\UserImporter;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;

test('user importer imports valid row', function () {
    UserImporter::test()
        ->import([
            'name' => 'Test Imported User',
            'email' => 'imported-user@example.com',
            'locale' => 'nl_BE',
        ])
        ->assertImported();

    expect(User::where('email', 'imported-user@example.com')->exists())->toBeTrue();
});

test('user importer fails validation with invalid email', function () {
    UserImporter::test()
        ->import([
            'name' => 'Test Imported User',
            'email' => 'not-an-email',
        ])
        ->assertHasErrors(['email' => 'email']);
});

test('user importer sets created by and updated by user id to the user executing the import', function () {
    $user = User::factory()->create();

    $import = new Import([
        'user_id' => $user->id,
    ]);

    UserImporter::test(import: $import)
        ->import([
            'name' => 'Test Imported User ID',
            'email' => 'imported-user-id@example.com',
        ])
        ->assertImported();

    $importedUser = User::where('email', 'imported-user-id@example.com')->first();
    expect($importedUser->created_by_user_id)->toBe($user->id)
        ->and($importedUser->updated_by_user_id)->toBe($user->id);
});
