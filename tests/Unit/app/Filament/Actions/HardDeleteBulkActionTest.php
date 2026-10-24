<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\HardDeleteBulkAction;

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = HardDeleteBulkAction::make();
        expect($action->getName())->toBe('hardDeleteBulk');
    });
});
