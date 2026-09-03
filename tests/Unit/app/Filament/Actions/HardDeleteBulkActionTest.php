<?php

use App\Filament\Actions\HardDeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has the correct default name', function () {
    $action = HardDeleteBulkAction::make();
    expect($action->getName())->toBe('hardDeleteBulk');
});
