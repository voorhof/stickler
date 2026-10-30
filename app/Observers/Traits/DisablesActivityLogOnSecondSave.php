<?php

namespace App\Observers\Traits;

use Illuminate\Database\Eloquent\Model;

trait DisablesActivityLogOnSecondSave
{
    /**
     * Disable activity logging if the model was just created in this request.
     *
     * Filament's RichEditor re-saves newly created records to attach media files.
     * This second save triggers an unwanted "updated" activity entry. By disabling
     * logging when wasRecentlyCreated is true, only the original "created" activity
     * is kept.
     *
     * @see \Spatie\Activitylog\Models\Concerns\LogsActivity::disableLogging()
     */
    public function disableActivityLogIfRecentlyCreated(Model $model): void
    {
        if ($model->wasRecentlyCreated && method_exists($model, 'disableLogging')) {
            $model->disableLogging();
        }
    }
}
