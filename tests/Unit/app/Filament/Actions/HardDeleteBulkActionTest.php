<?php

use App\Filament\Actions\HardDeleteBulkAction;

it('has the correct default name', function () {
    $action = HardDeleteBulkAction::make();
    expect($action->getName())->toBe('hardDeleteBulk');
});
