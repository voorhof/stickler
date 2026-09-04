<?php

use App\Filament\Actions\SoftDeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has the correct default name', function () {
    $action = SoftDeleteBulkAction::make();
    expect($action->getName())->toBe('softDeleteBulk');
});
