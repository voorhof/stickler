<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\SoftDeleteBulkAction;

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = SoftDeleteBulkAction::make();
        expect($action->getName())->toBe('softDeleteBulk');
    });
});
