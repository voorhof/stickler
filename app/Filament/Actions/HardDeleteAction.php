<?php

namespace App\Filament\Actions;

use Filament\Actions\ForceDeleteAction as BaseForceDeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class HardDeleteAction extends BaseForceDeleteAction
{
    public static function getDefaultName(): ?string
    {
        return 'hardDelete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->outlined();

        $this->size('sm');

        $this->visible(static function (Model $record): bool {
            if (! method_exists($record, 'restore')) {
                return true;
            }

            if (! method_exists($record, 'trashed')) {
                return false;
            }

            return $record->trashed();
        });

        $this->successNotification(function (Notification $notification): Notification {
            $notification
                ->title(__('Ooh! :model was permanently purged!', ['model' => __($this->getTitleCaseModelLabel())]))
                ->danger()
                ->sendToDatabase(auth()->user(), isEventDispatched: true);

            return $notification;
        });
    }
}
