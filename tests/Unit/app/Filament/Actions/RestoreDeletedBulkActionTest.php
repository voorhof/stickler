<?php

use App\Filament\Actions\RestoreDeletedBulkAction;

it('has the correct default name', function () {
    $action = RestoreDeletedBulkAction::make();
    expect($action->getName())->toBe('restoreDeletedBulk');
});
