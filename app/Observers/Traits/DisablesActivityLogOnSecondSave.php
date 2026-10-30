<?php

namespace App\Observers\Traits;

use Illuminate\Database\Eloquent\Model;

trait DisablesActivityLogOnSecondSave
{
    /**
     * Handle the second save by Filament's RichEditor.
     *
     * Filament's RichEditor re-saves newly created records to attach media files.
     * This second save triggers an unwanted "updated" activity entry. By disabling
     * logging when wasRecentlyCreated is true, only the original "created" activity
     * is kept.
     *
     * After disabling logging, the "created" activity's attributes are updated to
     * include the content field that was set by the RichEditor's second save.
     *
     * @see \Spatie\Activitylog\Models\Concerns\LogsActivity::disableLogging()
     */
    public function disableActivityLogIfRecentlyCreated(Model $model): void
    {
        if ($model->wasRecentlyCreated && method_exists($model, 'disableLogging')) {
            $model->disableLogging();

            // Update the "created" activity to include the content field set by the RichEditor
            $this->updateCreatedActivityAttributes($model);

            // Re-enable logging so future saves (updates, deletes) are still tracked
            if (method_exists($model, 'enableLogging')) {
                $model->enableLogging();
            }
        }
    }

    /**
     * Update the "created" activity's attributes to include the current model state.
     *
     * The "created" activity was logged before the RichEditor's second save, so it
     * doesn't include the content field. This method updates the activity to reflect
     * the final state of the model after all saves.
     */
    protected function updateCreatedActivityAttributes(Model $model): void
    {
        if (! method_exists($model, 'activitiesAsSubject')) {
            return;
        }

        $activity = $model->activitiesAsSubject()
            ->where('event', 'created')
            ->latest('created_at')
            ->first();

        if ($activity !== null) {
            $activity->properties = $activity->properties->merge([
                'attributes' => $model->getAttributes(),
            ]);
            $activity->save();
        }
    }
}
