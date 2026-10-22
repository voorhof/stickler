<?php

namespace App\Traits;

use Spatie\Activitylog\Support\LogOptions;

/**
 * Provides standard Spatie Activitylog options.
 *
 * Use this trait on models that need consistent activity logging configuration.
 * Override {@see activitylogFillable()} to log all attributes instead of fillable only.
 * Override {@see activitylogExcept()} to exclude specific attributes from logging.
 * Override {@see activitylogIgnoreChanges()} to customize which attribute changes should be ignored.
 * Override {@see activitylogName()} to set a custom log name.
 */
trait HasStandardActivitylogOptions
{
    /**
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->dontLogIfAttributesChangedOnly($this->activitylogIgnoreChanges())
            ->setDescriptionForEvent(fn (string $eventName) => __('This item has been :event', ['event' => __($eventName)]));

        if ($this->activitylogFillable()) {
            $options->logFillable();
        } else {
            $options->logAll();
        }

        $except = $this->activitylogExcept();

        if ($except) {
            $options->logExcept($except);
        }

        $logName = $this->activitylogName();

        if ($logName) {
            $options->useLogName($logName);
        }

        return $options;
    }

    /**
     * Determine whether to log only fillable attributes.
     *
     * Return false to log all attributes instead.
     */
    protected function activitylogFillable(): bool
    {
        return true;
    }

    /**
     * Get the attributes that should be excluded from logging.
     *
     * @return array<string>
     */
    protected function activitylogExcept(): array
    {
        return [];
    }

    /**
     * Get the attributes that should be ignored when determining if changes occurred.
     *
     * @return array<string>
     */
    protected function activitylogIgnoreChanges(): array
    {
        return ['updated_at'];
    }

    /**
     * Get the custom log name, or null to use the default.
     */
    protected function activitylogName(): ?string
    {
        return null;
    }
}
