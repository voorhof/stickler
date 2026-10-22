<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\RestoreDeletedBulkAction;

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = RestoreDeletedBulkAction::make();
        expect($action->getName())->toBe('restoreDeletedBulk');
    });
});
