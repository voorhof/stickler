<?php

use App\Filament\Actions\RestoreDeletedBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has the correct default name', function () {
    $action = RestoreDeletedBulkAction::make();
    expect($action->getName())->toBe('restoreDeletedBulk');
});
