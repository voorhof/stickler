<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;

trait SortableOnUpdate
{
    public static function bootSortableOnUpdate(): void
    {
        static::saved(function (Model $model) {
            /** @var Sortable $model */
            if ($model->wasChanged('order_column') || $model->wasRecentlyCreated) {
                $newOrder = (int) $model->order_column;

                // Fetch all records ordered by the current order_column.
                $models = $model->buildSortQuery()->orderBy('order_column')->get();

                // Remove the updated model from the list.
                $models = $models->reject(fn ($m) => $m->getKey() === $model->getKey());

                // Re-insert at the new position (clamp to valid indices).
                $insertPosition = max(0, $newOrder - 1);
                $models->splice($insertPosition, 0, [$model]);

                $ids = $models->pluck($model->getKeyName())->toArray();

                $model->setNewOrder($ids);
            }
        });
    }
}
