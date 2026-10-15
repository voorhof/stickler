<?php

use App\Filament\Actions\SoftDeleteBulkAction;

it('has the correct default name', function () {
    $action = SoftDeleteBulkAction::make();
    expect($action->getName())->toBe('softDeleteBulk');
});
