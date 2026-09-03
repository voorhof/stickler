<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasOrderTableDefaults
{
    /**
     * Override the `reorderTable` method,
     * so the `updated_at` column is preserved and not changed in the reordering process.
     * By wrapping the call to the parent `reorderTable` method within `Model::withoutTimestamps()`,
     *
     *
     * @param  array<int, int|string>  $order
     */
    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        Model::withoutTimestamps(fn () => parent::reorderTable($order, $draggedRecordKey));
    }
}
