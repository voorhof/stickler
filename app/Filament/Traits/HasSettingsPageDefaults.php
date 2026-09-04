<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Log;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Throwable;

trait HasSettingsPageDefaults
{
    protected string $logname = 'settings';

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('access settings');
    }

    public function canEdit(): bool
    {
        return auth()->user()->can('edit settings');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
        ];
    }

    public function getSavedNotificationTitle(): ?string
    {
        return __('Ok! Settings have been saved.');
    }

    public array $oldSettings = [];

    protected function beforeSave(): void
    {
        $this->oldSettings = app(static::getSettings())->toArray();
    }

    protected function afterSave(): void
    {
        $newSettings = app(static::getSettings())->toArray();

        // Log settings changes
        if (array_diff($this->oldSettings, $newSettings)) {

            $changedSettings = array_diff_assoc($newSettings, $this->oldSettings);
            $originalSettings = array_intersect_key($this->oldSettings, $changedSettings);

            activity()
                ->inLog($this->getLogname())
                ->performedOn(SettingsProperty::getModel())
                ->event('updated')
                ->withChanges([
                    'settings' => $changedSettings, 'old_settings' => $originalSettings,
                ])
                ->log(__('This item has been :event', ['event' => __('updated')]));

        }

        // Persist the standard Filament save notification to the database for the current user
        $notification = $this->getSavedNotification();

        if ($notification !== null && auth()->check()) {
            $notification->sendToDatabase(auth()->user(), isEventDispatched: true);
        }
    }

    /**
     * Get the log name for activity logging.
     */
    protected function getLogname(): string
    {
        if (method_exists($this, 'logname')) {
            return $this->logname();
        }

        if (property_exists($this, 'logname') && $this->logname !== 'settings') {
            return $this->logname;
        }

        try {
            $settingsClass = static::getSettings();
            if (method_exists($settingsClass, 'group')) {
                return $settingsClass::group().'_settings';
            }
        } catch (Throwable) {
            Log::alert('Failed to determine log name for activity logging.', ['settings_class' => get_class(parent::$settings)]);
        }

        return $this->logname;
    }
}
